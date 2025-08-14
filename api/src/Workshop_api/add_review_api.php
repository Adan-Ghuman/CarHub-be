<?php
include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Required fields
    $workshopId = isset($data['workshopId']) ? mysqli_real_escape_string($conn, $data['workshopId']) : '';
    $userId = isset($data['userId']) ? mysqli_real_escape_string($conn, $data['userId']) : '';
    $bookingId = isset($data['bookingId']) ? mysqli_real_escape_string($conn, $data['bookingId']) : '';
    $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
    $reviewText = isset($data['reviewText']) ? mysqli_real_escape_string($conn, trim($data['reviewText'])) : '';

    // Validation
    if (empty($workshopId) || empty($userId) || empty($bookingId) || $rating < 1 || $rating > 5 || empty($reviewText)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Required fields: workshopId, userId, bookingId, rating (1-5), reviewText'
        ]);
        exit;
    }

    try {
        // Verify booking exists, belongs to user, and is completed
        $bookingQuery = "SELECT wb.id, wb.status, w.name as workshop_name 
                        FROM workshop_bookings wb
                        LEFT JOIN workshops w ON wb.workshop_id = w.id
                        WHERE wb.id = '$bookingId' 
                        AND wb.user_id = '$userId' 
                        AND wb.workshop_id = '$workshopId'
                        AND wb.status = 'completed'";
        
        $bookingResult = mysqli_query($conn, $bookingQuery);

        if (!$bookingResult || mysqli_num_rows($bookingResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Booking not found, not yours, or not completed yet'
            ]);
            exit;
        }

        // Check if review already exists
        $existingReviewQuery = "SELECT id FROM workshop_reviews WHERE booking_id = '$bookingId' AND user_id = '$userId'";
        $existingResult = mysqli_query($conn, $existingReviewQuery);

        if (mysqli_num_rows($existingResult) > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'You have already reviewed this booking'
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

        // Insert review
        $insertQuery = "INSERT INTO workshop_reviews 
                       (workshop_id, user_id, booking_id, rating, review_text, created_at) 
                       VALUES 
                       ('$workshopId', '$userId', '$bookingId', '$rating', '$reviewText', NOW())";

        if (mysqli_query($conn, $insertQuery)) {
            $reviewId = mysqli_insert_id($conn);
            
            // Fetch the created review with user info
            $fetchQuery = "SELECT wr.*, u.name as user_name, u.email as user_email,
                                 w.name as workshop_name
                          FROM workshop_reviews wr
                          LEFT JOIN users u ON wr.user_id = u.id
                          LEFT JOIN workshops w ON wr.workshop_id = w.id
                          WHERE wr.id = '$reviewId'";
            
            $fetchResult = mysqli_query($conn, $fetchQuery);
            $review = mysqli_fetch_assoc($fetchResult);

            // Update workshop average rating (optional - can be calculated on the fly)
            $avgQuery = "UPDATE workshops 
                        SET rating = (
                            SELECT AVG(rating) 
                            FROM workshop_reviews 
                            WHERE workshop_id = '$workshopId'
                        ) 
                        WHERE id = '$workshopId'";
            mysqli_query($conn, $avgQuery);

            echo json_encode([
                'success' => true,
                'message' => 'Review added successfully',
                'data' => $review
            ]);
        } else {
            throw new Exception('Failed to add review: ' . mysqli_error($conn));
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
