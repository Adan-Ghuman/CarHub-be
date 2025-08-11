<?php
include 'config.php';

echo "=== ADDING BOOKINGS FOR WORKSHOP 3 ===\n\n";

// First add some bookings to the bookings table for workshop 3
$bookingsToAdd = [
    [
        'user_id' => 3,
        'workshop_id' => 3,
        'service_id' => 1,
        'booking_date' => '2025-07-28',
        'booking_time' => '10:00:00',
        'status' => 'completed',
        'price' => 3000.00,
        'notes' => 'Oil change service'
    ],
    [
        'user_id' => 4,
        'workshop_id' => 3,
        'service_id' => 2,
        'booking_date' => '2025-07-29',
        'booking_time' => '14:00:00',
        'status' => 'completed',
        'price' => 5000.00,
        'notes' => 'Brake service'
    ],
    [
        'user_id' => 5,
        'workshop_id' => 3,
        'service_id' => 3,
        'booking_date' => '2025-07-30',
        'booking_time' => '11:00:00',
        'status' => 'completed',
        'price' => 8000.00,
        'notes' => 'Engine repair'
    ]
];

$bookingIds = [];

foreach($bookingsToAdd as $booking) {
    $stmt = $conn->prepare('INSERT INTO bookings (user_id, workshop_id, service_id, booking_date, booking_time, status, price, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->bind_param('iiisssds',
        $booking['user_id'],
        $booking['workshop_id'],
        $booking['service_id'],
        $booking['booking_date'],
        $booking['booking_time'],
        $booking['status'],
        $booking['price'],
        $booking['notes']
    );
    
    if ($stmt->execute()) {
        $bookingId = $conn->insert_id;
        $bookingIds[] = $bookingId;
        echo "✓ Added booking ID: $bookingId for user " . $booking['user_id'] . "\n";
    } else {
        echo "✗ Failed to add booking: " . $conn->error . "\n";
    }
}

echo "\n=== ADDING REVIEWS FOR THESE BOOKINGS ===\n\n";

if (count($bookingIds) >= 3) {
    $reviews = [
        [
            'booking_id' => $bookingIds[0],
            'customer_name' => 'SYED MUHAMMAD JARRAR HAIDER',
            'customer_email' => 'jarrar@example.com',
            'rating' => 4,
            'comment' => 'Great oil changed service',
            'service_name' => 'Oil Change'
        ],
        [
            'booking_id' => $bookingIds[1],
            'customer_name' => 'Ahmed Ali',
            'customer_email' => 'ahmed@example.com',
            'rating' => 5,
            'comment' => 'Excellent brake service, very professional staff',
            'service_name' => 'Brake Service'
        ],
        [
            'booking_id' => $bookingIds[2],
            'customer_name' => 'Hassan Khan',
            'customer_email' => 'hassan@example.com',
            'rating' => 4,
            'comment' => 'Good engine repair work, satisfied with the results',
            'service_name' => 'Engine Repair'
        ]
    ];
    
    foreach($reviews as $reviewData) {
        $stmt = $conn->prepare('INSERT INTO reviews (booking_id, workshop_id, customer_name, customer_email, rating, comment, service_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        
        $workshopId = 3;
        $stmt->bind_param('iississ',
            $reviewData['booking_id'],
            $workshopId,
            $reviewData['customer_name'],
            $reviewData['customer_email'],
            $reviewData['rating'],
            $reviewData['comment'],
            $reviewData['service_name']
        );
        
        if ($stmt->execute()) {
            echo "✓ Added review from " . $reviewData['customer_name'] . " (Rating: " . $reviewData['rating'] . "/5)\n";
        } else {
            echo "✗ Failed to add review from " . $reviewData['customer_name'] . ": " . $conn->error . "\n";
        }
    }
}

echo "\n=== FINAL VERIFICATION ===\n\n";

// Check final count
$result = $conn->query('SELECT COUNT(*) as count FROM reviews WHERE workshop_id = 3');
$count = $result->fetch_assoc()['count'];
echo "Total reviews for Workshop ID 3: $count\n";

// Display all reviews
$result = $conn->query('SELECT * FROM reviews WHERE workshop_id = 3 ORDER BY created_at DESC');
$allReviews = $result->fetch_all(MYSQLI_ASSOC);

echo "\nAll reviews for Workshop 3:\n";
foreach($allReviews as $index => $review) {
    echo ($index + 1) . ". " . $review['customer_name'] . " - " . $review['rating'] . "/5 stars\n";
    echo "   Comment: " . $review['comment'] . "\n";
    echo "   Service: " . $review['service_name'] . "\n";
    echo "   Date: " . $review['created_at'] . "\n\n";
}
?>
