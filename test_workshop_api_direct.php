<?php
// Test the workshop details API by making a real POST request

// Create a JSON payload
$data = json_encode(['workshopId' => 3]);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/armghan/CARHUB_PK/backend/Workshop_api/workshop_details_api.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
?>
