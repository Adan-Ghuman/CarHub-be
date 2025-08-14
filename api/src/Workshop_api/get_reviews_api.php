<?php
include "../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $workshopId = isset($data['workshopId']) ? mysqli_real_escape_string($conn, $data['workshopId']) : '';
    $userId = isset($data['userId']) ? mysqli_real_escape_string($conn, $data['userId']) : '';
    $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
    $limit = isset($data['limit']) ? (int)$data['limit'] : 20;
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

    try {
        // Build query based on parameters
        $query = "SELECT wr.*, 
                         u.name as user_name, u.email as user_email,
                         w.name as workshop_name,
                         wb.booking_date, wb.service_id,
                         ws.service_name
                  FROM workshop_reviews wr
                  LEFT JOIN users u ON wr.user_id = u.id
                  LEFT JOIN workshops w ON wr.workshop_id = w.id
                  LEFT JOIN workshop_bookings wb ON wr.booking_id = wb.id
                  LEFT JOIN workshop_services ws ON wb.service_id = ws.id
                  WHERE 1=1";
        
        // Add filters
        if (!empty($workshopId)) {
            $query .= " AND wr.workshop_id = '$workshopId'";
        }
        
        if (!empty($userId)) {
            $query .= " AND wr.user_id = '$userId'";
        }
        
        if ($rating > 0 && $rating <= 5) {
            $query .= " AND wr.rating = $rating";
        }
        
        $query .= " ORDER BY wr.created_at DESC LIMIT $limit OFFSET $offset";

        $result = mysqli_query($conn, $query);

        if ($result) {
            $reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);

            // Format review data and add helpful info
            foreach ($reviews as &$review) {
                // Format date
                $review['formatted_date'] = date('M j, Y', strtotime($review['created_at']));
                $review['time_ago'] = time_ago($review['created_at']);
                
                // Add star display
                $review['stars'] = str_repeat('⭐', (int)$review['rating']);
                
                // Truncate review text for list view (full text available in review_text)
                $review['short_review'] = strlen($review['review_text']) > 150 
                    ? substr($review['review_text'], 0, 150) . '...' 
                    : $review['review_text'];
            }

            // Get total count and rating summary if workshop specified
            $summary = null;
            if (!empty($workshopId)) {
                $summaryQuery = "SELECT 
                                COUNT(*) as total_reviews,
                                AVG(rating) as average_rating,
                                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
                               FROM workshop_reviews 
                               WHERE workshop_id = '$workshopId'";
                
                $summaryResult = mysqli_query($conn, $summaryQuery);
                if ($summaryResult) {
                    $summary = mysqli_fetch_assoc($summaryResult);
                    $summary['average_rating'] = number_format((float)$summary['average_rating'], 1);
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $reviews,
                'count' => count($reviews),
                'summary' => $summary,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'hasMore' => count($reviews) === $limit
                ]
            ]);
        } else {
            throw new Exception('Failed to fetch reviews: ' . mysqli_error($conn));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'data' => []
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
}

// Helper function for time ago display
function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}

mysqli_close($conn);
?>
