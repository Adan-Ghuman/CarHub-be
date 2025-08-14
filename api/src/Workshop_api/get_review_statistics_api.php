<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get raw POST data
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    // Log the received data
    error_log("Review Statistics API - Received data: " . print_r($data, true));
    
    // Validate required fields
    if (!isset($data['workshop_id'])) {
        echo json_encode([
            "success" => false,
            "error" => "Missing required field: workshop_id"
        ]);
        exit;
    }
    
    $workshop_id = (int)$data['workshop_id'];
    
    // Create database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get overall statistics
    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star_count,
            COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star_count,
            COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star_count,
            COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star_count,
            COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star_count,
            COUNT(CASE WHEN workshop_response IS NOT NULL THEN 1 END) as responded_count
        FROM reviews 
        WHERE workshop_id = ?
    ");
    
    $statsStmt->execute([$workshop_id]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate percentages
    $total = (int)$stats['total_reviews'];
    $responseRate = $total > 0 ? round(($stats['responded_count'] / $total) * 100, 1) : 0;
    
    // Get monthly review trends (last 6 months)
    $trendsStmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as review_count,
            AVG(rating) as avg_rating
        FROM reviews 
        WHERE workshop_id = ? 
        AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    
    $trendsStmt->execute([$workshop_id]);
    $trends = $trendsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent reviews (last 5)
    $recentStmt = $pdo->prepare("
        SELECT 
            id,
            customer_name,
            rating,
            comment as review_text,
            workshop_response,
            created_at,
            response_date
        FROM reviews 
        WHERE workshop_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    
    $recentStmt->execute([$workshop_id]);
    $recentReviews = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $statistics = [
        'total_reviews' => (int)$stats['total_reviews'],
        'average_rating' => $stats['average_rating'] ? round((float)$stats['average_rating'], 1) : 0,
        'response_rate' => $responseRate,
        'responded_count' => (int)$stats['responded_count'],
        'rating_distribution' => [
            '5' => (int)$stats['five_star_count'],
            '4' => (int)$stats['four_star_count'],
            '3' => (int)$stats['three_star_count'],
            '2' => (int)$stats['two_star_count'],
            '1' => (int)$stats['one_star_count']
        ],
        'monthly_trends' => $trends,
        'recent_reviews' => $recentReviews
    ];
    
    echo json_encode([
        "success" => true,
        "message" => "Review statistics retrieved successfully",
        "data" => $statistics
    ]);
    
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "An error occurred: " . $e->getMessage()
    ]);
}
?>
