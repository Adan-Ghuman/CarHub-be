<?php
include 'config.php';

echo "=== CHECKING BOOKINGS FOR WORKSHOP 3 ===\n\n";

$result = $conn->query('SELECT id, customer_name, service_id, status FROM bookings WHERE workshop_id = 3 LIMIT 5');
if ($result) {
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    echo "Available bookings for workshop 3: " . count($bookings) . "\n";
    foreach($bookings as $booking) {
        echo "Booking ID: " . $booking['id'] . ", Customer: " . $booking['customer_name'] . ", Service ID: " . $booking['service_id'] . ", Status: " . $booking['status'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== ADDING REVIEWS WITH VALID BOOKING IDS ===\n\n";

// Get some booking IDs for workshop 3
$result = $conn->query('SELECT id FROM bookings WHERE workshop_id = 3 LIMIT 3');
if ($result) {
    $bookingIds = [];
    while ($row = $result->fetch_assoc()) {
        $bookingIds[] = $row['id'];
    }
    
    if (count($bookingIds) >= 3) {
        $reviews = [
            [
                'booking_id' => $bookingIds[0],
                'workshop_id' => 3,
                'customer_name' => 'SYED MUHAMMAD JARRAR HAIDER',
                'customer_email' => 'jarrar@example.com',
                'rating' => 4,
                'comment' => 'Great oil changed service',
                'service_name' => 'Oil Change'
            ],
            [
                'booking_id' => $bookingIds[1],
                'workshop_id' => 3,
                'customer_name' => 'Ahmed Ali',
                'customer_email' => 'ahmed@example.com',
                'rating' => 5,
                'comment' => 'Excellent brake service, very professional staff',
                'service_name' => 'Brake Service'
            ],
            [
                'booking_id' => $bookingIds[2],
                'workshop_id' => 3,
                'customer_name' => 'Hassan Khan',
                'customer_email' => 'hassan@example.com',
                'rating' => 4,
                'comment' => 'Good engine repair work, satisfied with the results',
                'service_name' => 'Engine Repair'
            ]
        ];
        
        foreach($reviews as $review) {
            $stmt = $conn->prepare('INSERT INTO reviews (booking_id, workshop_id, customer_name, customer_email, rating, comment, service_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->bind_param('iississ', 
                $review['booking_id'],
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
    } else {
        echo "Not enough bookings found for workshop 3. Found: " . count($bookingIds) . "\n";
    }
} else {
    echo "Error fetching bookings: " . $conn->error . "\n";
}

// Final count check
echo "\n=== FINAL COUNT CHECK ===\n\n";
$result = $conn->query('SELECT COUNT(*) as count FROM reviews WHERE workshop_id = 3');
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "Total reviews for Workshop ID 3: $count\n";
}
?>
