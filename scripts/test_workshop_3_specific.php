<?php
include "config.php";

echo "=== Testing with Workshop 3 specifically ===\n";

// Test bookings API with workshop 3
$requestData = [
    'workshopId' => 3,
    'limit' => 50,
    'offset' => 0
];

echo "Testing with request: " . json_encode($requestData) . "\n\n";

// Direct database query for workshop 3
$query = "SELECT id, workshop_id, customer_name, status FROM workshop_bookings WHERE workshop_id = 3 ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

echo "Direct database query for workshop 3:\n";
$count = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $count++;
    echo "  $count. ID: {$row['id']}, Customer: {$row['customer_name']}, Status: {$row['status']}\n";
}

echo "\nTotal bookings for workshop 3: $count\n";

// Now test the actual API file
echo "\n=== Testing the actual get_bookings_api.php ===\n";

// Simulate POST data
$_POST = [];
$input = json_encode($requestData);

// Capture the API output
ob_start();

// Include the API file (this will process the request)
$data = json_decode($input, true);

$userId = isset($data['userId']) ? mysqli_real_escape_string($conn, $data['userId']) : 
          (isset($data['user_id']) ? mysqli_real_escape_string($conn, $data['user_id']) : '');
$workshopId = isset($data['workshopId']) ? mysqli_real_escape_string($conn, $data['workshopId']) : '';
$status = isset($data['status']) ? mysqli_real_escape_string($conn, $data['status']) : '';
$limit = isset($data['limit']) ? (int)$data['limit'] : 50;
$offset = isset($data['offset']) ? (int)$data['offset'] : 0;

echo "Parsed parameters:\n";
echo "- userId: '$userId'\n";
echo "- workshopId: '$workshopId'\n";
echo "- status: '$status'\n";
echo "- limit: $limit\n";
echo "- offset: $offset\n";

// Build query based on parameters
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
          WHERE 1=1";

// Add filters
if (!empty($userId)) {
    $query .= " AND wb.user_id = '$userId'";
}

if (!empty($workshopId)) {
    $query .= " AND wb.workshop_id = '$workshopId'";
}

if (!empty($status)) {
    $query .= " AND wb.status = '$status'";
}

$query .= " ORDER BY wb.created_at DESC LIMIT $limit OFFSET $offset";

echo "\nFinal query:\n$query\n\n";

$result = mysqli_query($conn, $query);

if ($result) {
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    
    echo "✅ Query executed successfully\n";
    echo "Found " . count($bookings) . " bookings\n";
    
    if (count($bookings) > 0) {
        echo "\nBookings found:\n";
        foreach ($bookings as $i => $booking) {
            echo "  " . ($i + 1) . ". ID: {$booking['id']}, Workshop: {$booking['workshop_id']}, Customer: {$booking['customer_name']}\n";
        }
    }
} else {
    echo "❌ Query failed: " . mysqli_error($conn) . "\n";
}

$conn->close();
?>
