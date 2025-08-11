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
    $bookingId = isset($data['bookingId']) ? mysqli_real_escape_string($conn, $data['bookingId']) : '';
    $workshopId = isset($data['workshopId']) ? mysqli_real_escape_string($conn, $data['workshopId']) : '';
    $newStatus = isset($data['status']) ? mysqli_real_escape_string($conn, $data['status']) : '';
    $notes = isset($data['notes']) ? mysqli_real_escape_string($conn, trim($data['notes'])) : '';

    // Valid status values
    $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

    // Validation
    if (empty($bookingId) || empty($workshopId) || empty($newStatus)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Required fields: bookingId, workshopId, status'
        ]);
        exit;
    }

    if (!in_array($newStatus, $validStatuses)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid status. Valid values: ' . implode(', ', $validStatuses)
        ]);
        exit;
    }

    try {
        // Verify booking exists and belongs to the workshop
        $bookingQuery = "SELECT wb.*, u.Name as user_name, u.Email as user_email, u.PhoneNumber as user_phone,
                               ws.service_name, ws.price
                        FROM workshop_bookings wb
                        LEFT JOIN users u ON wb.user_id = u.id
                        LEFT JOIN workshop_services ws ON wb.service_id = ws.id
                        WHERE wb.id = '$bookingId' AND wb.workshop_id = '$workshopId'";
        
        $bookingResult = mysqli_query($conn, $bookingQuery);

        if (!$bookingResult || mysqli_num_rows($bookingResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Booking not found or does not belong to this workshop'
            ]);
            exit;
        }

        $booking = mysqli_fetch_assoc($bookingResult);
        $currentStatus = $booking['status'];

        // Check if status transition is valid
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [], // No transitions from completed
            'cancelled' => [] // No transitions from cancelled
        ];

        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => "Cannot change status from '$currentStatus' to '$newStatus'"
            ]);
            exit;
        }

        // Update booking status
        $updateQuery = "UPDATE workshop_bookings 
                       SET status = '$newStatus', 
                           updated_at = NOW()";
        
        if (!empty($notes)) {
            $updateQuery .= ", notes = CONCAT(COALESCE(notes, ''), '\n[" . date('Y-m-d H:i:s') . "] Status changed to $newStatus: $notes')";
        }
        
        $updateQuery .= " WHERE id = '$bookingId' AND workshop_id = '$workshopId'";

        if (mysqli_query($conn, $updateQuery)) {
            
            // Fetch updated booking details
            $fetchQuery = "SELECT wb.*, 
                                 w.name as workshop_name, w.address as workshop_address,
                                 ws.service_name, ws.price, ws.duration,
                                 u.Name as user_name, u.Email as user_email, u.PhoneNumber as user_phone
                          FROM workshop_bookings wb
                          LEFT JOIN workshops w ON wb.workshop_id = w.id
                          LEFT JOIN workshop_services ws ON wb.service_id = ws.id
                          LEFT JOIN users u ON wb.user_id = u.id
                          WHERE wb.id = '$bookingId'";
            
            $fetchResult = mysqli_query($conn, $fetchQuery);
            $updatedBooking = mysqli_fetch_assoc($fetchResult);

            // Log the status change (optional - you could create a booking_logs table)
            $logQuery = "INSERT INTO booking_status_logs (booking_id, old_status, new_status, changed_at, notes) 
                        VALUES ('$bookingId', '$currentStatus', '$newStatus', NOW(), '$notes')";
            // Note: This requires a booking_status_logs table - uncomment if you create it
            // mysqli_query($conn, $logQuery);

            echo json_encode([
                'success' => true,
                'message' => "Booking status updated to '$newStatus'",
                'data' => $updatedBooking,
                'previous_status' => $currentStatus
            ]);
        } else {
            throw new Exception('Failed to update booking status: ' . mysqli_error($conn));
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
