<?php
// Direct test of get_workshop_stats_api.php
include_once 'config.php';

$workshop_id = 1; // Test with workshop ID 1

echo "Direct Stats API Test\n";
echo "====================\n";

try {
    // Test the logic directly
    $booking_sql = "SELECT 
                        COUNT(*) as total_bookings,
                        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue,
                        SUM(CASE WHEN status IN ('pending', 'confirmed') THEN total_amount ELSE 0 END) as pending_revenue,
                        SUM(CASE WHEN DATE(booking_date) = CURDATE() THEN 1 ELSE 0 END) as today_bookings,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                        COUNT(DISTINCT user_id) as total_customers
                    FROM workshop_bookings 
                    WHERE workshop_id = ?";
    
    $stmt = $conn->prepare($booking_sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $workshop_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    echo "✓ Booking stats query successful\n";
    echo "  Total Bookings: " . $result['total_bookings'] . "\n";
    echo "  Total Revenue: " . $result['total_revenue'] . "\n";
    echo "  Completed: " . $result['completed_bookings'] . "\n";
    echo "  Pending: " . $result['pending_bookings'] . "\n";
    echo "  Customers: " . $result['total_customers'] . "\n";
    
    // Test services query
    $services_sql = "SELECT COUNT(*) as total_services FROM workshop_services WHERE workshop_id = ? AND is_active = 1";
    $services_stmt = $conn->prepare($services_sql);
    if (!$services_stmt) {
        throw new Exception("Services prepare failed: " . $conn->error);
    }
    
    $services_stmt->bind_param("i", $workshop_id);
    $services_stmt->execute();
    $services_result = $services_stmt->get_result()->fetch_assoc();
    
    echo "✓ Services query successful\n";
    echo "  Total Services: " . $services_result['total_services'] . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
