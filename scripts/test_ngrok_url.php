<?php
// Test the ngrok URL
echo "=== Testing NGROK URL ===\n";

$ngrok_url = "https://225d0157b561.ngrok-free.app/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_details_api.php";

$test_data = [
    'workshopId' => 3
];

echo "\nTesting ngrok URL:\n";
echo "URL: $ngrok_url\n";
echo "Request data: " . json_encode($test_data) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ngrok_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'ngrok-skip-browser-warning: true'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For ngrok SSL
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $http_code\n";
if ($error) {
    echo "CURL Error: $error\n";
}
echo "Response: $response\n";

if ($http_code === 200) {
    echo "✅ NGROK URL is working!\n";
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "Workshop: " . ($data['data']['name'] ?? 'N/A') . "\n";
    }
} else {
    echo "❌ NGROK URL failed with status: $http_code\n";
}
?>
