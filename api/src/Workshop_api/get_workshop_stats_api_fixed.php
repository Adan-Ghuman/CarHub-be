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
    
    // Initialize stats array
    $stats = [
        'totalBookings' => 0,
        'todayBookings' => 0,
        'weeklyBookings' => 0,
        'monthlyBookings' => 0,
        'totalRevenue' => 0,
        'monthlyRevenue' => 0,
        'pendingRevenue' => 0,
        'completedBookings' => 0,
        'pendingBookings' => 0,
        'confirmedBookings' => 0,
        'cancelledBookings' => 0,
        'totalServices' => 0,
        'totalCustomers' => 0,
        'averageRating' => 0,
        'totalReviews' => 0,
        'completionRate' => 0,
        'popularServices' => []
    ];
    
    // Get total bookings and revenue stats
    $booking_sql = "SELECT 
                        COUNT(*) as total_bookings,
                        SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END) as total_revenue,
                        SUM(CASE WHEN status IN ('pending', 'confirmed') THEN price ELSE 0 END) as pending_revenue,
                        SUM(CASE WHEN DATE(booking_date) = CURDATE() THEN 1 ELSE 0 END) as today_bookings,
                        SUM(CASE WHEN WEEK(booking_date) = WEEK(NOW()) AND YEAR(booking_date) = YEAR(NOW()) THEN 1 ELSE 0 END) as weekly_bookings,
                        SUM(CASE WHEN MONTH(booking_date) = MONTH(NOW()) AND YEAR(booking_date) = YEAR(NOW()) THEN 1 ELSE 0 END) as monthly_bookings,
                        SUM(CASE WHEN MONTH(booking_date) = MONTH(NOW()) AND YEAR(booking_date) = YEAR(NOW()) AND status = 'completed' THEN price ELSE 0 END) as monthly_revenue,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                        COUNT(DISTINCT user_id) as total_customers
                    FROM bookings 
                    WHERE workshop_id = ?";
    
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param("i", $workshop_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result()->fetch_assoc();
    
    if ($booking_result) {
        $stats['totalBookings'] = (int)$booking_result['total_bookings'];
        $stats['todayBookings'] = (int)$booking_result['today_bookings'];
        $stats['weeklyBookings'] = (int)$booking_result['weekly_bookings'];
        $stats['monthlyBookings'] = (int)$booking_result['monthly_bookings'];
        $stats['totalRevenue'] = (float)$booking_result['total_revenue'];
        $stats['monthlyRevenue'] = (float)$booking_result['monthly_revenue'];
        $stats['pendingRevenue'] = (float)$booking_result['pending_revenue'];
        $stats['completedBookings'] = (int)$booking_result['completed_bookings'];
        $stats['pendingBookings'] = (int)$booking_result['pending_bookings'];
        $stats['confirmedBookings'] = (int)$booking_result['confirmed_bookings'];
        $stats['cancelledBookings'] = (int)$booking_result['cancelled_bookings'];
        $stats['totalCustomers'] = (int)$booking_result['total_customers'];
        
        // Calculate completion rate
        if ($stats['totalBookings'] > 0) {
            $stats['completionRate'] = round(($stats['completedBookings'] / $stats['totalBookings']) * 100, 1);
        }
    }
    
    // Get services count
    $services_sql = "SELECT COUNT(*) as total_services FROM services WHERE workshop_id = ? AND is_active = 1";
    $services_stmt = $conn->prepare($services_sql);
    $services_stmt->bind_param("i", $workshop_id);
    $services_stmt->execute();
    $services_result = $services_stmt->get_result()->fetch_assoc();
    $stats['totalServices'] = (int)$services_result['total_services'];
    
    // Get reviews and rating stats
    $review_sql = "SELECT 
                        COUNT(*) as total_reviews,
                        AVG(r.rating) as average_rating
                   FROM reviews r
                   JOIN bookings b ON r.booking_id = b.id
                   WHERE b.workshop_id = ?";
    
    $review_stmt = $conn->prepare($review_sql);
    $review_stmt->bind_param("i", $workshop_id);
    $review_stmt->execute();
    $review_result = $review_stmt->get_result()->fetch_assoc();
    
    if ($review_result) {
        $stats['totalReviews'] = (int)$review_result['total_reviews'];
        $stats['averageRating'] = $review_result['average_rating'] ? round((float)$review_result['average_rating'], 1) : 0;
    }
    
    // Get popular services
    $popular_sql = "SELECT 
                        s.service_name,
                        COUNT(b.id) as booking_count,
                        SUM(b.price) as total_revenue
                    FROM services s
                    LEFT JOIN bookings b ON s.id = b.service_id
                    WHERE s.workshop_id = ? AND s.is_active = 1
                    GROUP BY s.id, s.service_name
                    ORDER BY booking_count DESC, total_revenue DESC
                    LIMIT 5";
    
    $popular_stmt = $conn->prepare($popular_sql);
    $popular_stmt->bind_param("i", $workshop_id);
    $popular_stmt->execute();
    $popular_result = $popular_stmt->get_result();
    
    while ($service = $popular_result->fetch_assoc()) {
        $stats['popularServices'][] = [
            'service_name' => $service['service_name'],
            'booking_count' => (int)$service['booking_count'],
            'total_revenue' => (float)$service['total_revenue']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'message' => 'Workshop statistics retrieved successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching workshop statistics: ' . $e->getMessage()
    ]);
}
?>
