<?php
// Debug the get_workshop_services_api response

$serviceData = [
    'workshopId' => 3,
    'isActive' => true
];

$url = 'https://225d0157b561.ngrok-free.app/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_services_api.php';

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

echo "API Response:\n";
echo $result . "\n\n";

// Decode and analyze
$response = json_decode($result, true);
if ($response && isset($response['data'])) {
    echo "Number of services: " . count($response['data']) . "\n\n";
    
    foreach ($response['data'] as $index => $service) {
        echo "Service #" . ($index + 1) . ":\n";
        echo "  ID: " . $service['id'] . "\n";
        echo "  Name: " . $service['service_name'] . "\n";
        echo "  Category: " . $service['service_category'] . "\n";
        echo "  Price: " . $service['price'] . "\n";
        echo "  Duration: " . $service['duration'] . "\n";
        echo "  Description: " . $service['description'] . "\n";
        echo "  Created: " . $service['created_at'] . "\n";
        echo "\n";
    }
    
    // Check for duplicate IDs
    $ids = array_column($response['data'], 'id');
    $duplicates = array_diff_assoc($ids, array_unique($ids));
    if (!empty($duplicates)) {
        echo "⚠️  DUPLICATE IDs FOUND: " . implode(', ', $duplicates) . "\n";
    } else {
        echo "✅ All service IDs are unique\n";
    }
}
?>
