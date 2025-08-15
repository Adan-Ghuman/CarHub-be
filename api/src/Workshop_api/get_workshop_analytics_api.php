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

require_once __DIR__ . '/../../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get raw POST data
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    // Log the received data
    error_log("Analytics API - Received data: " . print_r($data, true));
    
    // Validate required fields
    if (!isset($data['workshop_id'])) {
        echo json_encode([
            "success" => false,
            "error" => "Missing required field: workshop_id"
        ]);
        exit;
    }
    
    $workshop_id = (int)$data['workshop_id'];
    $period = $data['period'] ?? 'month';
    
    // Create database connection using MySQLi
    $pdo = new PDO("mysql:host=" . $servername . ";dbname=" . $database, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get basic workshop statistics
    $statsQuery = "
        SELECT 
            COUNT(CASE WHEN b.status = 'completed' THEN 1 END) as total_bookings,
            SUM(CASE WHEN b.status = 'completed' THEN b.price ELSE 0 END) as total_revenue,
            COUNT(CASE WHEN b.status = 'pending' THEN 1 END) as pending_bookings,
            COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) as confirmed_bookings,
            COUNT(CASE WHEN b.status = 'cancelled' THEN 1 END) as cancelled_bookings,
            COUNT(DISTINCT b.user_id) as total_customers
        FROM bookings b
        WHERE b.workshop_id = ?
    ";
    
    if ($period === 'week') {
        $statsQuery .= " AND b.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    } elseif ($period === 'month') {
        $statsQuery .= " AND b.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    } elseif ($period === 'quarter') {
        $statsQuery .= " AND b.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
    } elseif ($period === 'year') {
        $statsQuery .= " AND b.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    }
    
    $stmt = $pdo->prepare($statsQuery);
    $stmt->execute([$workshop_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get average rating
    $ratingQuery = "
        SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews
        FROM reviews 
        WHERE workshop_id = ?
    ";
    $ratingStmt = $pdo->prepare($ratingQuery);
    $ratingStmt->execute([$workshop_id]);
    $ratingData = $ratingStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get total services count
    $servicesQuery = "
        SELECT COUNT(*) as total_services
        FROM services 
        WHERE workshop_id = ? AND is_active = 1
    ";
    $servicesStmt = $pdo->prepare($servicesQuery);
    $servicesStmt->execute([$workshop_id]);
    $servicesData = $servicesStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get monthly revenue for current month
    $monthlyRevenueQuery = "
        SELECT SUM(price) as monthly_revenue
        FROM bookings 
        WHERE workshop_id = ? 
        AND status = 'completed'
        AND MONTH(created_at) = MONTH(CURRENT_DATE())
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ";
    $monthlyStmt = $pdo->prepare($monthlyRevenueQuery);
    $monthlyStmt->execute([$workshop_id]);
    $monthlyData = $monthlyStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get today's bookings
    $todayQuery = "
        SELECT COUNT(*) as today_bookings
        FROM bookings 
        WHERE workshop_id = ? 
        AND DATE(created_at) = CURRENT_DATE()
    ";
    $todayStmt = $pdo->prepare($todayQuery);
    $todayStmt->execute([$workshop_id]);
    $todayData = $todayStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get weekly bookings
    $weeklyQuery = "
        SELECT COUNT(*) as weekly_bookings
        FROM bookings 
        WHERE workshop_id = ? 
        AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK)
    ";
    $weeklyStmt = $pdo->prepare($weeklyQuery);
    $weeklyStmt->execute([$workshop_id]);
    $weeklyData = $weeklyStmt->fetch(PDO::FETCH_ASSOC);
    
    $analytics = [
        'totalRevenue' => (float)($stats['total_revenue'] ?: 0),
        'totalBookings' => (int)($stats['total_bookings'] ?: 0),
        'pendingBookings' => (int)($stats['pending_bookings'] ?: 0),
        'confirmedBookings' => (int)($stats['confirmed_bookings'] ?: 0),
        'cancelledBookings' => (int)($stats['cancelled_bookings'] ?: 0),
        'totalCustomers' => (int)($stats['total_customers'] ?: 0),
        'averageRating' => (float)($ratingData['average_rating'] ?: 0),
        'totalReviews' => (int)($ratingData['total_reviews'] ?: 0),
        'totalServices' => (int)($servicesData['total_services'] ?: 0),
        'monthlyRevenue' => (float)($monthlyData['monthly_revenue'] ?: 0),
        'todayBookings' => (int)($todayData['today_bookings'] ?: 0),
        'weeklyBookings' => (int)($weeklyData['weekly_bookings'] ?: 0),
        'period' => $period
    ];
    
    echo json_encode([
        "success" => true,
        "message" => "Analytics data retrieved successfully",
        "data" => $analytics
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
