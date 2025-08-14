<?php
// Test the exact API call that the frontend is making
echo "=== Testing Booking Status Update API Call ===\n";

// Test the exact scenario
$api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/update_booking_status_api.php";

// Test with booking ID 7 (we know this exists and is pending)
$test_data = [
    'bookingId' => 7,
    'workshopId' => 3,
    'status' => 'confirmed'
];

echo "Testing API call with data:\n";
echo json_encode($test_data, JSON_PRETTY_PRINT) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\nHTTP Status Code: $http_code\n";
echo "API Response: $response\n";

$response_data = json_decode($response, true);
if ($response_data) {
    echo "\nParsed Response:\n";
    if ($response_data['success'] ?? false) {
        echo "✅ SUCCESS: Update worked!\n";
    } else {
        echo "❌ FAILED: " . ($response_data['error'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ Invalid JSON response\n";
}

echo "\n=== Test Complete ===\n";
?>
