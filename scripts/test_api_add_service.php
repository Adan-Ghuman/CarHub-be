<?php
// Test the add_service_api.php directly

$serviceData = [
    'workshop_id' => 3,
    'service_name' => 'Brake Repair API Test',
    'description' => 'Testing the API directly',
    'price' => 750.50,
    'duration' => '90',
    'service_category' => 'brake'
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
if ($response) {
    echo "\nFormatted Response:\n";
    echo "Success: " . ($response['success'] ? 'true' : 'false') . "\n";
    if (isset($response['message'])) {
        echo "Message: " . $response['message'] . "\n";
    }
    if (isset($response['data'])) {
        echo "Service ID: " . $response['data']['id'] . "\n";
        echo "Service Name: " . $response['data']['service_name'] . "\n";
    }
    if (isset($response['error'])) {
        echo "Error: " . $response['error'] . "\n";
    }
}
?>
