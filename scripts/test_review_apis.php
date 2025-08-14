<?php
// Test the review APIs
require_once 'config.php';

echo "Testing Review APIs...\n\n";

// Test get workshop reviews
echo "1. Testing Get Workshop Reviews API...\n";
$postData = json_encode(['workshop_id' => 3]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $postData
    ]
]);

$result = file_get_contents('http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_reviews_api.php', false, $context);
echo "Response: " . $result . "\n\n";

// Test get review statistics
echo "2. Testing Get Review Statistics API...\n";
$postData = json_encode(['workshop_id' => 3]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $postData
    ]
]);

$result = file_get_contents('http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_review_statistics_api.php', false, $context);
echo "Response: " . $result . "\n\n";

echo "Review API tests completed!\n";
?>
