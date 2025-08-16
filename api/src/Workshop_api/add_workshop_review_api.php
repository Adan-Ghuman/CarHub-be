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
    $userId = isset($data['user_id']) ? mysqli_real_escape_string($conn, $data['user_id']) : '';
    $bookingId = isset($data['booking_id']) ? mysqli_real_escape_string($conn, $data['booking_id']) : '';
    $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
    $reviewText = isset($data['review_text']) ? mysqli_real_escape_string($conn, trim($data['review_text'])) : '';

    // Validation - booking_id is optional for general reviews
    if (empty($workshopId) || empty($userId) || $rating < 1 || $rating > 5 || empty($reviewText)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Required fields: workshopId, user_id, rating (1-5), review_text'
        ]);
        exit;
    }

    try {
        // Debug: Check table structure
        $debugQuery = "DESCRIBE workshop_reviews";
        $debugResult = mysqli_query($conn, $debugQuery);
        $tableStructure = [];
        if ($debugResult) {
            while ($row = mysqli_fetch_assoc($debugResult)) {
                $tableStructure[] = $row['Field'];
            }
        }
        
        // If booking_id is provided, verify the booking exists and belongs to the user
        if (!empty($bookingId)) {
            $bookingQuery = "SELECT wb.id, wb.status, w.name as workshop_name, ws.service_name 
                            FROM workshop_bookings wb
                            LEFT JOIN workshops w ON wb.workshop_id = w.id
                            LEFT JOIN workshop_services ws ON wb.service_id = ws.id
                            WHERE wb.id = '$bookingId' 
                            AND wb.user_id = '$userId' 
                            AND wb.workshop_id = '$workshopId'
                            AND wb.status = 'completed'";
            
            $bookingResult = mysqli_query($conn, $bookingQuery);

            if (!$bookingResult || mysqli_num_rows($bookingResult) === 0) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Booking not found, not yours, or not completed yet. Only completed bookings can be reviewed.'
                ]);
                exit;
            }
        }
        // Verify workshop exists
        $workshopQuery = "SELECT name FROM workshops WHERE id = '$workshopId' AND status = 'active'";
        $workshopResult = mysqli_query($conn, $workshopQuery);

        if (!$workshopResult || mysqli_num_rows($workshopResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Workshop not found or not active'
            ]);
            exit;
        }

        // Check if user already reviewed this booking (if booking_id provided) or workshop
        if (!empty($bookingId)) {
            $existingReviewQuery = "SELECT id FROM workshop_reviews WHERE booking_id = '$bookingId' AND user_id = '$userId'";
        } else {
            $existingReviewQuery = "SELECT id FROM workshop_reviews WHERE workshop_id = '$workshopId' AND user_id = '$userId' AND booking_id IS NULL";
        }
        
        $existingResult = mysqli_query($conn, $existingReviewQuery);

        if (mysqli_num_rows($existingResult) > 0) {
            // Update existing review
            if (!empty($bookingId)) {
                $updateQuery = "UPDATE workshop_reviews 
                               SET rating = '$rating', review_text = '$reviewText'
                               WHERE booking_id = '$bookingId' AND user_id = '$userId'";
            } else {
                $updateQuery = "UPDATE workshop_reviews 
                               SET rating = '$rating', review_text = '$reviewText'
                               WHERE workshop_id = '$workshopId' AND user_id = '$userId' AND booking_id IS NULL";
            }
            
            if (mysqli_query($conn, $updateQuery)) {
                $message = !empty($bookingId) ? 'Your review for this booking has been updated successfully!' : 'Your review for this workshop has been updated successfully!';
                $isUpdate = true;
            } else {
                throw new Exception('Failed to update review: ' . mysqli_error($conn) . ' | Query: ' . $updateQuery);
            }
        } else {
            // Insert new review
            $insertQuery = "INSERT INTO workshop_reviews 
                           (workshop_id, user_id, booking_id, rating, review_text, created_at) 
                           VALUES 
                           ('$workshopId', '$userId', " . (empty($bookingId) ? "NULL" : "'$bookingId'") . ", '$rating', '$reviewText', NOW())";

            if (mysqli_query($conn, $insertQuery)) {
                $message = !empty($bookingId) ? 'Thank you! Your review for this booking has been submitted successfully!' : 'Thank you! Your review has been submitted successfully!';
                $isUpdate = false;
            } else {
                throw new Exception('Failed to add review: ' . mysqli_error($conn) . ' | Query: ' . $insertQuery);
            }
        }

        // Get updated average rating
        $avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                    FROM workshop_reviews 
                    WHERE workshop_id = '$workshopId'";
        $avgResult = mysqli_query($conn, $avgQuery);
        $avgData = mysqli_fetch_assoc($avgResult);

        // Update workshop rating
        $updateWorkshopQuery = "UPDATE workshops 
                               SET rating = '" . number_format($avgData['avg_rating'], 1) . "' 
                               WHERE id = '$workshopId'";
        mysqli_query($conn, $updateWorkshopQuery);

        echo json_encode([
            'success' => true,
            'message' => $message,
            'is_update' => $isUpdate,
            'data' => [
                'workshop_id' => $workshopId,
                'rating' => $rating,
                'review_text' => $reviewText,
                'average_rating' => number_format($avgData['avg_rating'], 1),
                'total_reviews' => $avgData['total_reviews']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'debug_table_structure' => isset($tableStructure) ? $tableStructure : []
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed. Use POST method.']);
}

mysqli_close($conn);
?>
