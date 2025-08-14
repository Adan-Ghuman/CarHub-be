<?php
header('Content-Type: application/json');

echo "Direct API Test Results:\n";
echo "========================\n\n";

// Test 1: Workshop Stats API
echo "1. Testing Workshop Stats API (GET):\n";
$stats_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_stats_api.php?workshop_id=1";
$stats_context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$stats_response = @file_get_contents($stats_url, false, $stats_context);
if ($stats_response !== false) {
    $stats_data = json_decode($stats_response, true);
    if ($stats_data && isset($stats_data['success'])) {
        echo "✓ API Response: " . ($stats_data['success'] ? 'Success' : 'Failed') . "\n";
        echo "  Message: " . ($stats_data['message'] ?? 'No message') . "\n";
        if ($stats_data['success'] && isset($stats_data['data'])) {
            echo "  Total Bookings: " . ($stats_data['data']['totalBookings'] ?? 0) . "\n";
            echo "  Total Revenue: " . ($stats_data['data']['totalRevenue'] ?? 0) . "\n";
        }
    } else {
        echo "✗ Invalid JSON response\n";
        echo "Raw response: " . substr($stats_response, 0, 200) . "\n";
    }
} else {
    echo "✗ Failed to connect to API\n";
}

echo "\n";

// Test 2: Workshop Reviews API
echo "2. Testing Workshop Reviews API (GET):\n";
$reviews_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_reviews_api.php?workshop_id=1";
$reviews_response = @file_get_contents($reviews_url, false, $stats_context);

if ($reviews_response !== false) {
    $reviews_data = json_decode($reviews_response, true);
    if ($reviews_data && isset($reviews_data['success'])) {
        echo "✓ API Response: " . ($reviews_data['success'] ? 'Success' : 'Failed') . "\n";
        echo "  Message: " . ($reviews_data['message'] ?? 'No message') . "\n";
        if ($reviews_data['success'] && isset($reviews_data['data'])) {
            echo "  Reviews Count: " . count($reviews_data['data']['reviews'] ?? []) . "\n";
            echo "  Average Rating: " . ($reviews_data['data']['average_rating'] ?? 0) . "\n";
        }
    } else {
        echo "✗ Invalid JSON response\n";
        echo "Raw response: " . substr($reviews_response, 0, 200) . "\n";
    }
} else {
    echo "✗ Failed to connect to API\n";
}

echo "\n";

// Test 3: Test with non-existent workshop
echo "3. Testing with non-existent workshop (validation):\n";
$invalid_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_stats_api.php?workshop_id=99999";
$invalid_response = @file_get_contents($invalid_url, false, $stats_context);

if ($invalid_response !== false) {
    $invalid_data = json_decode($invalid_response, true);
    if ($invalid_data && isset($invalid_data['success'])) {
        echo "✓ Validation working: " . ($invalid_data['success'] ? 'Unexpected success' : 'Correctly failed') . "\n";
        echo "  Message: " . ($invalid_data['message'] ?? 'No message') . "\n";
    } else {
        echo "✗ Invalid JSON response\n";
    }
} else {
    echo "✗ Failed to connect to API\n";
}

echo "\nDirect API test complete!\n";
?>
