<?php
include 'config.php';

echo "Adding test reviews for Workshop ID 3 (ZAWAR HAIDER)...\n\n";

try {
    // Add test reviews for workshop ID 3
    $reviews = [
        [
            'workshop_id' => 3,
            'customer_name' => 'SYED MUHAMMAD JARRAR HAIDER',
            'customer_email' => 'jarrar@example.com',
            'rating' => 4,
            'comment' => 'Great oil changed service',
            'service_name' => 'Oil Change'
        ],
        [
            'workshop_id' => 3,
            'customer_name' => 'Ahmed Ali',
            'customer_email' => 'ahmed@example.com',
            'rating' => 5,
            'comment' => 'Excellent brake service, very professional staff',
            'service_name' => 'Brake Service'
        ],
        [
            'workshop_id' => 3,
            'customer_name' => 'Hassan Khan',
            'customer_email' => 'hassan@example.com',
            'rating' => 4,
            'comment' => 'Good engine repair work, satisfied with the results',
            'service_name' => 'Engine Repair'
        ]
    ];
    
    foreach($reviews as $review) {
        $stmt = $conn->prepare('INSERT INTO reviews (workshop_id, customer_name, customer_email, rating, comment, service_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->bind_param('ississ', 
            $review['workshop_id'],
            $review['customer_name'],
            $review['customer_email'],
            $review['rating'],
            $review['comment'],
            $review['service_name']
        );
        
        if ($stmt->execute()) {
            echo "✓ Added review from " . $review['customer_name'] . " (Rating: " . $review['rating'] . ")\n";
        } else {
            echo "✗ Failed to add review from " . $review['customer_name'] . ": " . $conn->error . "\n";
        }
    }
    
    // Check total reviews for workshop 3
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM reviews WHERE workshop_id = 3');
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    echo "\nTotal reviews for Workshop ID 3: $count\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
