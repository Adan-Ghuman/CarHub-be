<?php
// Test the review API locally
$data = [
    'workshopId' => 3,
    'user_id' => 1,
    'rating' => 5,
    'review_text' => 'Great workshop with excellent service!'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/armghan/CARHUB_PK/backend/Workshop_api/add_workshop_review_api.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($data))
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
?>
