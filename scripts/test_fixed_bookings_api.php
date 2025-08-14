<?php
include "config.php";

// Simulate the API call with corrected parameter name
echo "=== Testing get_bookings_api.php with correct parameters ===\n";

// Test with workshop 3 (should have 8 bookings)
$requestData = [
    'workshopId' => 3, // Using workshopId as expected by API
    'limit' => 50,
    'offset' => 0
];

echo "Request data: " . json_encode($requestData) . "\n";

// Simulate the API logic
$workshopId = $requestData['workshopId'];
$limit = $requestData['limit'];
$offset = $requestData['offset'];

$query = "SELECT wb.*, 
                 w.name as workshop_name, w.address as workshop_address, w.city as workshop_city, w.phone as workshop_phone,
                 ws.service_name, ws.service_category, ws.price, ws.duration,
                 u.Name as user_name, u.Email as user_email, u.PhoneNumber as user_phone,
                 wr.id as review_id, wr.rating as review_rating, wr.review_text
          FROM workshop_bookings wb
          LEFT JOIN workshops w ON wb.workshop_id = w.id
          LEFT JOIN workshop_services ws ON wb.service_id = ws.id
          LEFT JOIN users u ON wb.user_id = u.id
          LEFT JOIN workshop_reviews wr ON wb.id = wr.booking_id AND wb.user_id = wr.user_id
          WHERE wb.workshop_id = '$workshopId'
          ORDER BY wb.created_at DESC LIMIT $limit OFFSET $offset";

echo "\nExecuting query for workshop $workshopId...\n";

$result = mysqli_query($conn, $query);

if ($result) {
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    
    echo "✅ SUCCESS: Found " . count($bookings) . " bookings for workshop $workshopId\n";
    
    if (count($bookings) > 0) {
        echo "\nSample bookings:\n";
        foreach (array_slice($bookings, 0, 3) as $booking) {
            echo "- ID: " . $booking['id'] . ", Status: " . $booking['status'] . ", Customer: " . ($booking['customer_name'] ?? $booking['user_name'] ?? 'N/A') . "\n";
        }
    }
    
    // Simulate API response
    $response = [
        'success' => true,
        'data' => $bookings,
        'pagination' => [
            'hasMore' => count($bookings) >= $limit,
            'offset' => $offset + count($bookings)
        ]
    ];
    
    echo "\nAPI Response structure:\n";
    echo "- success: " . ($response['success'] ? 'true' : 'false') . "\n";
    echo "- data count: " . count($response['data']) . "\n";
    echo "- pagination hasMore: " . ($response['pagination']['hasMore'] ? 'true' : 'false') . "\n";
    
} else {
    echo "❌ ERROR: " . mysqli_error($conn) . "\n";
}

// Also test workshop 1 for comparison
echo "\n=== Testing Workshop 1 (should have 14 bookings) ===\n";
$query1 = "SELECT COUNT(*) as count FROM workshop_bookings WHERE workshop_id = 1";
$result1 = mysqli_query($conn, $query1);
$count1 = mysqli_fetch_assoc($result1)['count'];
echo "Workshop 1 bookings: $count1\n";

$conn->close();
?>
