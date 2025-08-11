<?php
// Test the frontend API call format
echo "=== Testing Frontend API Call Format ===\n";

$api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_details_api.php";

// Test with the exact format the frontend sends
$frontend_data = [
    'workshopId' => 3
];

echo "\nTesting with frontend format:\n";
echo "Request data: " . json_encode($frontend_data) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($frontend_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'ngrok-skip-browser-warning: true'
]);
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
        echo "✅ SUCCESS! Frontend format works correctly.\n";
        echo "Workshop: " . ($response_data['data']['name'] ?? 'N/A') . "\n";
        echo "Rating: " . ($response_data['data']['rating'] ?? 'N/A') . "\n";
        echo "Services: " . ($response_data['data']['total_services'] ?? 'N/A') . "\n";
    } else {
        echo "❌ FAILED: " . ($response_data['error'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ Failed to parse response\n";
}

echo "\n=== All tests completed ===\n";
?>
