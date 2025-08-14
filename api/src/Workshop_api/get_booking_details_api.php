<?php
include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $bookingId = isset($data['bookingId']) ? mysqli_real_escape_string($conn, $data['bookingId']) : '';

    if (empty($bookingId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Booking ID is required'
        ]);
        exit;
    }

    try {
        // Get detailed booking information
        $query = "SELECT wb.*, 
                         w.name as workshop_name, w.address as workshop_address, 
                         w.city as workshop_city, w.phone as workshop_phone,
                         w.email as workshop_email, w.description as workshop_description,
                         ws.service_name, ws.service_category, ws.price, ws.duration,
                         ws.description as service_description,
                         u.Name as user_name, u.Email as user_email, u.PhoneNumber as user_phone,
                         wr.id as review_id, wr.rating as review_rating, wr.review_text
                  FROM workshop_bookings wb
                  LEFT JOIN workshops w ON wb.workshop_id = w.id
                  LEFT JOIN workshop_services ws ON wb.service_id = ws.id
                  LEFT JOIN users u ON wb.user_id = u.id
                  LEFT JOIN workshop_reviews wr ON wb.id = wr.booking_id AND wb.user_id = wr.user_id
                  WHERE wb.id = '$bookingId'";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $booking = mysqli_fetch_assoc($result);

            // Format booking data
            $booking['price'] = number_format((float)$booking['price'], 2);
            $booking['booking_datetime'] = $booking['booking_date'] . ' ' . $booking['booking_time'];
            
            // Add review information
            $booking['has_review'] = !empty($booking['review_id']);
            if ($booking['has_review']) {
                $booking['my_rating'] = (int)$booking['review_rating'];
                $booking['my_review_text'] = $booking['review_text'];
            } else {
                $booking['my_rating'] = null;
                $booking['my_review_text'] = null;
            }
            
            // Remove raw review fields to keep response clean
            unset($booking['review_id'], $booking['review_rating'], $booking['review_text']);
            
            // Add status badge color for frontend
            switch($booking['status']) {
                case 'pending':
                    $booking['status_color'] = '#FFA500';
                    break;
                case 'confirmed':
                    $booking['status_color'] = '#28A745';
                    break;
                case 'completed':
                    $booking['status_color'] = '#007BFF';
                    break;
                case 'cancelled':
                    $booking['status_color'] = '#DC3545';
                    break;
                default:
                    $booking['status_color'] = '#6C757D';
            }

            echo json_encode([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking details retrieved successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Booking not found',
                'data' => null
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'data' => null
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
}

mysqli_close($conn);
?>
