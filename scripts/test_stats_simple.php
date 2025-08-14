<?php
// Simple test for workshop stats API

$statsData = json_encode(['workshop_id' => 1]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $statsData
    ]
]);

echo "Testing Workshop Stats API:\n";
$result = file_get_contents('http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_stats_api.php', false, $context);
echo $result . "\n";
?>
