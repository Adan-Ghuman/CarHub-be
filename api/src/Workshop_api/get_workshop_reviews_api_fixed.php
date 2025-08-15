<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/config.php';

try {
    // Get workshop_id from query parameter or POST data
    $workshop_id = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $workshop_id = $_GET['workshop_id'] ?? null;
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $workshop_id = $input['workshop_id'] ?? $_POST['workshop_id'] ?? null;
    }
    
    if (!$workshop_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Workshop ID is required'
        ]);
        exit;
    }
    
    // Verify workshop exists
    $workshop_check = $conn->prepare("SELECT id FROM workshops WHERE id = ?");
    $workshop_check->bind_param("i", $workshop_id);
    $workshop_check->execute();
    $workshop_result = $workshop_check->get_result();
    
    if ($workshop_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Workshop not found'
        ]);
        exit;
    }
    
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Query to get workshop reviews with customer details
    $sql = "SELECT 
                r.id,
                r.rating,
                r.comment,
                r.created_at,
                u.name as customer_name,
                u.email as customer_email,
                b.id as booking_id,
                s.service_name
            FROM reviews r
            JOIN bookings b ON r.booking_id = b.id
            JOIN users u ON b.user_id = u.id
            JOIN services s ON b.service_id = s.id
            WHERE b.workshop_id = ?
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $workshop_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total 
                  FROM reviews r
                  JOIN bookings b ON r.booking_id = b.id
                  WHERE b.workshop_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $workshop_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result()->fetch_assoc();
    $total_count = $count_result['total'];
    
    // Calculate average rating
    $avg_sql = "SELECT AVG(r.rating) as average_rating
                FROM reviews r
                JOIN bookings b ON r.booking_id = b.id
                WHERE b.workshop_id = ?";
    $avg_stmt = $conn->prepare($avg_sql);
    $avg_stmt->bind_param("i", $workshop_id);
    $avg_stmt->execute();
    $avg_result = $avg_stmt->get_result()->fetch_assoc();
    $average_rating = $avg_result['average_rating'] ? round($avg_result['average_rating'], 1) : 0;
    
    // Get rating distribution
    $dist_sql = "SELECT 
                    rating,
                    COUNT(*) as count
                 FROM reviews r
                 JOIN bookings b ON r.booking_id = b.id
                 WHERE b.workshop_id = ?
                 GROUP BY rating
                 ORDER BY rating DESC";
    $dist_stmt = $conn->prepare($dist_sql);
    $dist_stmt->bind_param("i", $workshop_id);
    $dist_stmt->execute();
    $dist_result = $dist_stmt->get_result();
    
    // Format the rating distribution
    $distribution = [
        '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0
    ];
    
    while ($rating = $dist_result->fetch_assoc()) {
        $distribution[$rating['rating']] = (int)$rating['count'];
    }
    
    // Format reviews data
    $formatted_reviews = array_map(function($review) {
        return [
            'id' => $review['id'],
            'rating' => (int)$review['rating'],
            'comment' => $review['comment'],
            'customer_name' => $review['customer_name'],
            'customer_email' => $review['customer_email'],
            'service_name' => $review['service_name'],
            'booking_id' => $review['booking_id'],
            'created_at' => $review['created_at'],
            'formatted_date' => date('M j, Y', strtotime($review['created_at']))
        ];
    }, $reviews);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'reviews' => $formatted_reviews,
            'total_count' => (int)$total_count,
            'average_rating' => $average_rating,
            'rating_distribution' => $distribution,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total_count
            ]
        ],
        'message' => 'Reviews retrieved successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching reviews: ' . $e->getMessage()
    ]);
}
?>
