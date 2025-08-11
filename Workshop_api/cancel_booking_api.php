<?php
include "../config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $bookingId = isset($data['booking_id']) ? (int)$data['booking_id'] : 0;
    
    if ($bookingId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid booking ID'
        ]);
        exit;
    }
    
    try {
        // Check if booking exists
        $checkQuery = "SELECT * FROM workshop_bookings WHERE id = $bookingId";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if (!$checkResult || mysqli_num_rows($checkResult) === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Booking not found'
            ]);
            exit;
        }
        
        $booking = mysqli_fetch_assoc($checkResult);
        
        // Check if booking can be cancelled (not already completed)
        if ($booking['status'] === 'completed') {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot cancel completed booking'
            ]);
            exit;
        }
        
        // Update booking status to cancelled
        $updateQuery = "UPDATE workshop_bookings SET status = 'cancelled', updated_at = NOW() WHERE id = $bookingId";
        $updateResult = mysqli_query($conn, $updateQuery);
        
        if ($updateResult) {
            echo json_encode([
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to cancel booking'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

mysqli_close($conn);
?>
