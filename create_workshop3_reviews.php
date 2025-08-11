<?php
include 'config.php';

echo "=== ADDING REVIEWS FOR WORKSHOP 3 BOOKINGS ===\n\n";

// Get completed bookings for workshop 3
$stmt = $conn->prepare("SELECT id, customer_name, customer_email, service_id FROM workshop_bookings WHERE workshop_id = 3 AND status = 'completed' LIMIT 3");
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

echo "Found " . count($bookings) . " completed bookings for workshop 3\n\n";

if (count($bookings) > 0) {
    $reviews = [
        [
            'rating' => 4,
            'comment' => 'Great oil changed service',
            'service_name' => 'Oil Change'
        ],
        [
            'rating' => 5,
            'comment' => 'Excellent brake service, very professional staff',
            'service_name' => 'Brake Service'
        ],
        [
            'rating' => 4,
            'comment' => 'Good engine repair work, satisfied with the results',
            'service_name' => 'Engine Repair'
        ]
    ];
    
    for ($i = 0; $i < min(count($bookings), count($reviews)); $i++) {
        $booking = $bookings[$i];
        $reviewData = $reviews[$i];
        
        $stmt = $conn->prepare('INSERT INTO reviews (booking_id, workshop_id, customer_name, customer_email, rating, comment, service_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        
        $workshopId = 3;
        $stmt->bind_param('iississ', 
            $booking['id'],
            $workshopId,
            $booking['customer_name'],
            $booking['customer_email'],
            $reviewData['rating'],
            $reviewData['comment'],
            $reviewData['service_name']
        );
        
        if ($stmt->execute()) {
            echo "✓ Added review from " . $booking['customer_name'] . " (Rating: " . $reviewData['rating'] . ")\n";
        } else {
            echo "✗ Failed to add review from " . $booking['customer_name'] . ": " . $conn->error . "\n";
        }
    }
    
    // Check final count
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM reviews WHERE workshop_id = 3');
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    echo "\nTotal reviews for Workshop ID 3: $count\n";
    
    // Display all reviews for workshop 3
    echo "\n=== ALL REVIEWS FOR WORKSHOP 3 ===\n\n";
    $stmt = $conn->prepare('SELECT * FROM reviews WHERE workshop_id = 3 ORDER BY created_at DESC');
    $stmt->execute();
    $result = $stmt->get_result();
    $allReviews = $result->fetch_all(MYSQLI_ASSOC);
    
    foreach($allReviews as $index => $review) {
        echo ($index + 1) . ". " . $review['customer_name'] . " - " . $review['rating'] . "/5 stars\n";
        echo "   Comment: " . $review['comment'] . "\n";
        echo "   Service: " . $review['service_name'] . "\n";
        echo "   Date: " . $review['created_at'] . "\n\n";
    }
    
} else {
    echo "No completed bookings found for workshop 3\n";
    
    // Check all bookings for workshop 3
    $stmt = $conn->prepare("SELECT id, customer_name, status FROM workshop_bookings WHERE workshop_id = 3");
    $stmt->execute();
    $result = $stmt->get_result();
    $allBookings = $result->fetch_all(MYSQLI_ASSOC);
    
    echo "All bookings for workshop 3:\n";
    foreach($allBookings as $booking) {
        echo "ID: " . $booking['id'] . ", Customer: " . $booking['customer_name'] . ", Status: " . $booking['status'] . "\n";
    }
}
?>
