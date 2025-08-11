<?php
// Test the workshop details API
echo "=== Testing Workshop Details API ===\n";

$api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_details_api.php";

// Test with workshop ID 3 (for user 11)
$test_data = [
    'workshopId' => 3
];

echo "\nTesting with workshop ID 3:\n";
echo "Request data: " . json_encode($test_data) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Response: $response\n";

$response_data = json_decode($response, true);
if ($response_data) {
    echo "\nParsed Response:\n";
    if ($response_data['success'] ?? false) {
        echo "✅ SUCCESS!\n";
        echo "Workshop Name: " . ($response_data['data']['name'] ?? 'N/A') . "\n";
        echo "Workshop Rating: " . ($response_data['data']['rating'] ?? 'N/A') . "\n";
        echo "Total Reviews: " . ($response_data['data']['total_reviews'] ?? 'N/A') . "\n";
        echo "Total Services: " . ($response_data['data']['total_services'] ?? 'N/A') . "\n";
        echo "Total Bookings: " . ($response_data['data']['total_bookings'] ?? 'N/A') . "\n";
        echo "Completion Rate: " . ($response_data['data']['completion_rate'] ?? 'N/A') . "%\n";
    } else {
        echo "❌ FAILED: " . ($response_data['error'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ Failed to parse response\n";
}

// Test with invalid workshop ID
echo "\n" . str_repeat("-", 50) . "\n";
echo "Testing with invalid workshop ID (999):\n";

$invalid_data = ['workshopId' => 999];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalid_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Response: $response\n";

// Test without workshop ID
echo "\n" . str_repeat("-", 50) . "\n";
echo "Testing without workshop ID:\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Response: $response\n";
?>
