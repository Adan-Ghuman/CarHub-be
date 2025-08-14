<?php
include 'config.php';

$workshopId = 3;

try {
    echo "Testing reviews for workshop ID: $workshopId\n";
    
    // Check total reviews count
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM reviews WHERE workshop_id = ?');
    $stmt->bind_param('i', $workshopId);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalCount = $result->fetch_assoc()['total'];
    echo "Total reviews in database: $totalCount\n\n";
    
    // Get all reviews
    $stmt = $conn->prepare('
        SELECT r.*, 
               COALESCE(r.customer_name, u.name, "Anonymous") as customer_display_name,
               s.name as service_name
        FROM reviews r
        LEFT JOIN users u ON r.customer_id = u.id
        LEFT JOIN services s ON r.service_id = s.id
        WHERE r.workshop_id = ? 
        ORDER BY r.created_at DESC
    ');
    $stmt->bind_param('i', $workshopId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
    
    echo "Reviews fetched by API query: " . count($reviews) . "\n\n";
    
    foreach($reviews as $index => $review) {
        echo "Review " . ($index + 1) . ":\n";
        echo "  ID: " . $review['id'] . "\n";
        echo "  Customer: " . $review['customer_display_name'] . "\n";
        echo "  Rating: " . $review['rating'] . "\n";
        echo "  Comment: " . substr($review['comment'], 0, 50) . "...\n";
        echo "  Service: " . ($review['service_name'] ?? 'N/A') . "\n";
        echo "  Created: " . $review['created_at'] . "\n";
        echo "  Workshop Response: " . ($review['workshop_response'] ?? 'None') . "\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
