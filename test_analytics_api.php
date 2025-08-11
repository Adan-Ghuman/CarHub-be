<?php
// Test the analytics API
require_once 'config.php';

echo "Testing Analytics API...\n\n";

// Test get workshop analytics
echo "Testing Get Workshop Analytics API...\n";
$postData = json_encode([
    'workshop_id' => 3,
    'period' => 'month'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $postData
    ]
]);

$result = file_get_contents('http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_analytics_api.php', false, $context);
echo "Response: " . $result . "\n\n";

echo "Analytics API test completed!\n";
?>
