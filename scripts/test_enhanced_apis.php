<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Test configuration
$test_workshop_id = 1; // Use an existing workshop ID for testing

echo "Testing Enhanced Workshop APIs\n";
echo "===============================\n\n";

// Test 1: Workshop Stats API
echo "1. Testing Workshop Stats API:\n";
$stats_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_stats_api.php?workshop_id=" . $test_workshop_id;
$stats_response = file_get_contents($stats_url);
$stats_data = json_decode($stats_response, true);

if ($stats_data && $stats_data['success']) {
    echo "✓ Workshop Stats API working\n";
    echo "  - Total Revenue: " . ($stats_data['data']['totalRevenue'] ?? 'N/A') . "\n";
    echo "  - Total Bookings: " . ($stats_data['data']['totalBookings'] ?? 'N/A') . "\n";
    echo "  - Average Rating: " . ($stats_data['data']['averageRating'] ?? 'N/A') . "\n";
    echo "  - Total Services: " . ($stats_data['data']['totalServices'] ?? 'N/A') . "\n";
} else {
    echo "✗ Workshop Stats API failed\n";
    echo "Response: " . $stats_response . "\n";
}

echo "\n";

// Test 2: Workshop Reviews API
echo "2. Testing Workshop Reviews API:\n";
$reviews_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_reviews_api.php?workshop_id=" . $test_workshop_id;
$reviews_response = file_get_contents($reviews_url);
$reviews_data = json_decode($reviews_response, true);

if ($reviews_data && $reviews_data['success']) {
    echo "✓ Workshop Reviews API working\n";
    echo "  - Total Reviews: " . count($reviews_data['data']['reviews'] ?? []) . "\n";
    if (!empty($reviews_data['data']['reviews'])) {
        $latest_review = $reviews_data['data']['reviews'][0];
        echo "  - Latest Review: " . ($latest_review['rating'] ?? 'N/A') . "/5 stars\n";
    }
} else {
    echo "✗ Workshop Reviews API failed\n";
    echo "Response: " . $reviews_response . "\n";
}

echo "\n";

// Test 3: Delete Service API (Test with a non-existent service ID)
echo "3. Testing Delete Service API (with validation):\n";
$delete_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/delete_service_api.php";
$delete_data = json_encode([
    'service_id' => 99999, // Non-existent service ID for testing
    'workshop_id' => $test_workshop_id
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $delete_data
    ]
]);

$delete_response = file_get_contents($delete_url, false, $context);
$delete_result = json_decode($delete_response, true);

if ($delete_result) {
    if (!$delete_result['success'] && strpos($delete_result['message'], 'not found') !== false) {
        echo "✓ Delete Service API validation working (correctly rejected non-existent service)\n";
    } else {
        echo "? Delete Service API response: " . $delete_result['message'] . "\n";
    }
} else {
    echo "✗ Delete Service API failed to respond\n";
    echo "Response: " . $delete_response . "\n";
}

echo "\n";

// Test 4: Check if all required APIs exist
echo "4. API Endpoint Verification:\n";
$api_endpoints = [
    'get_workshop_stats_api.php',
    'delete_service_api.php',
    'get_workshop_reviews_api.php'
];

foreach ($api_endpoints as $endpoint) {
    $file_path = __DIR__ . "/Workshop_api/" . $endpoint;
    if (file_exists($file_path)) {
        echo "✓ " . $endpoint . " exists\n";
    } else {
        echo "✗ " . $endpoint . " missing\n";
    }
}

echo "\n";
echo "Enhanced API Integration Test Complete!\n";
echo "======================================\n";
?>
