<?php
// Test the enhanced review API
$data = [
    'workshopId' => 1,
    'user_id' => 1,
    'booking_id' => 1,
    'rating' => 4,
    'review_text' => 'Great service! Updated review test.'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/armghan/CARHUB_PK/backend/Workshop_api/add_workshop_review_api.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
?>
