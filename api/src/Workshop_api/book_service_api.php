<?php
include "../config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Required fields
    $workshopId = isset($data['workshopId']) ? mysqli_real_escape_string($conn, $data['workshopId']) : '';
    $serviceId = isset($data['serviceId']) ? mysqli_real_escape_string($conn, $data['serviceId']) : '';
    $userId = isset($data['userId']) ? mysqli_real_escape_string($conn, $data['userId']) : '';
    $bookingDate = isset($data['bookingDate']) ? mysqli_real_escape_string($conn, $data['bookingDate']) : '';
    $bookingTime = isset($data['bookingTime']) ? mysqli_real_escape_string($conn, $data['bookingTime']) : '';
    $customerName = isset($data['customerName']) ? mysqli_real_escape_string($conn, trim($data['customerName'])) : '';
    $customerPhone = isset($data['customerPhone']) ? mysqli_real_escape_string($conn, trim($data['customerPhone'])) : '';
    $notes = isset($data['notes']) ? mysqli_real_escape_string($conn, trim($data['notes'])) : '';

    // Validation
    if (empty($workshopId) || empty($serviceId) || empty($userId) || empty($bookingDate) || empty($bookingTime) || empty($customerName) || empty($customerPhone)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Required fields: workshopId, serviceId, userId, bookingDate, bookingTime, customerName, customerPhone'
        ]);
        exit;
    }

    // Validate date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingDate)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid date format. Use YYYY-MM-DD'
        ]);
        exit;
    }

    // Validate time format (HH:MM)
    if (!preg_match('/^\d{2}:\d{2}$/', $bookingTime)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid time format. Use HH:MM'
        ]);
        exit;
    }

    try {
        // Verify workshop exists and is active
        $workshopQuery = "SELECT name FROM workshops WHERE id = '$workshopId' AND status = 'active' AND is_verified = TRUE";
        $workshopResult = mysqli_query($conn, $workshopQuery);

        if (!$workshopResult || mysqli_num_rows($workshopResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Workshop not found or not available'
            ]);
            exit;
        }

        // Verify service exists and is active
        $serviceQuery = "SELECT service_name, price FROM workshop_services WHERE id = '$serviceId' AND workshop_id = '$workshopId' AND is_active = TRUE";
        $serviceResult = mysqli_query($conn, $serviceQuery);

        if (!$serviceResult || mysqli_num_rows($serviceResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Service not found or not available'
            ]);
            exit;
        }

        // Verify user exists
        $userQuery = "SELECT name FROM users WHERE id = '$userId'";
        $userResult = mysqli_query($conn, $userQuery);

        if (!$userResult || mysqli_num_rows($userResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'User not found'
            ]);
            exit;
        }

        // Check for existing booking at the same time
        $conflictQuery = "SELECT id FROM workshop_bookings 
                         WHERE workshop_id = '$workshopId' 
                         AND booking_date = '$bookingDate' 
                         AND booking_time = '$bookingTime' 
                         AND status IN ('pending', 'confirmed')";
        $conflictResult = mysqli_query($conn, $conflictQuery);

        if (mysqli_num_rows($conflictResult) > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'This time slot is already booked. Please choose another time.'
            ]);
            exit;
        }

        // Create booking
        $insertQuery = "INSERT INTO workshop_bookings 
                       (workshop_id, service_id, user_id, booking_date, booking_time, customer_name, customer_phone, notes, status, created_at) 
                       VALUES 
                       ('$workshopId', '$serviceId', '$userId', '$bookingDate', '$bookingTime', '$customerName', '$customerPhone', '$notes', 'pending', NOW())";

        if (mysqli_query($conn, $insertQuery)) {
            $bookingId = mysqli_insert_id($conn);
            
            // Fetch complete booking details
            $fetchQuery = "SELECT wb.*, 
                                 w.name as workshop_name, w.address as workshop_address,
                                 ws.service_name, ws.price, ws.estimated_time,
                                 u.name as user_name, u.email as user_email
                          FROM workshop_bookings wb
                          LEFT JOIN workshops w ON wb.workshop_id = w.id
                          LEFT JOIN workshop_services ws ON wb.service_id = ws.id
                          LEFT JOIN users u ON wb.user_id = u.id
                          WHERE wb.id = '$bookingId'";
            
            $fetchResult = mysqli_query($conn, $fetchQuery);
            
            if ($fetchResult && mysqli_num_rows($fetchResult) > 0) {
                $booking = mysqli_fetch_assoc($fetchResult);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'data' => $booking
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'data' => [
                        'booking_id' => $bookingId,
                        'status' => 'pending'
                    ]
                ]);
            }
        } else {
            throw new Exception('Failed to create booking: ' . mysqli_error($conn));
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed. Use POST method.']);
}

mysqli_close($conn);
?>
