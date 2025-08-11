<?php
// Test adding a service with proper category

$serviceData = [
    'workshop_id' => 3,
    'service_name' => 'Tire Rotation',
    'description' => 'Complete tire rotation service',
    'price' => 350.00,
    'duration' => '45',
    'service_category' => 'maintenance'
];

$url = 'https://225d0157b561.ngrok-free.app/armghan/CARHUB_PK/backend/Workshop_api/add_service_api.php';

$options = [
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'ngrok-skip-browser-warning: true'
        ],
        'method' => 'POST',
        'content' => json_encode($serviceData)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "API Test Result:\n";
echo $result . "\n";

// Decode and display formatted
$response = json_decode($result, true);
if ($response && isset($response['data'])) {
    echo "\nNew Service Details:\n";
    echo "ID: " . $response['data']['id'] . "\n";
    echo "Name: " . $response['data']['service_name'] . "\n";
    echo "Category: " . $response['data']['service_category'] . "\n";
    echo "Price: " . $response['data']['price'] . "\n";
    echo "Duration: " . $response['data']['duration'] . "\n";
}
?>
