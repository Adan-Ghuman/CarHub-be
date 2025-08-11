<?php
header('Content-Type: application/json');

echo "Testing Workshop APIs with Real Data:\n";
echo "====================================\n\n";

// Test with workshop ID 1 which has data
$workshop_id = 1;

echo "Testing with Workshop ID: $workshop_id\n\n";

// Test 1: Workshop Stats API
echo "1. Testing Workshop Stats API:\n";
$stats_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_stats_api.php?workshop_id=$workshop_id";
$stats_response = @file_get_contents($stats_url);

if ($stats_response !== false) {
    $stats_data = json_decode($stats_response, true);
    if ($stats_data && isset($stats_data['success'])) {
        echo "✓ Success: " . ($stats_data['success'] ? 'Yes' : 'No') . "\n";
        echo "  Message: " . ($stats_data['message'] ?? 'No message') . "\n";
        if ($stats_data['success'] && isset($stats_data['data'])) {
            echo "  - Total Bookings: " . ($stats_data['data']['totalBookings'] ?? 0) . "\n";
            echo "  - Total Services: " . ($stats_data['data']['totalServices'] ?? 0) . "\n";
            echo "  - Total Revenue: " . ($stats_data['data']['totalRevenue'] ?? 0) . "\n";
            echo "  - Today Bookings: " . ($stats_data['data']['todayBookings'] ?? 0) . "\n";
        }
    } else {
        echo "✗ Invalid JSON response\n";
        echo "Raw response: " . substr($stats_response, 0, 500) . "\n";
    }
} else {
    echo "✗ Failed to connect to API\n";
}

echo "\n";

// Test 2: Workshop Services API (existing)
echo "2. Testing Workshop Services API (existing):\n";
$services_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_services_api.php";
$services_data = json_encode(['workshopId' => $workshop_id, 'isActive' => true]);

$services_context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $services_data
    ]
]);

$services_response = @file_get_contents($services_url, false, $services_context);
if ($services_response !== false) {
    $services_data = json_decode($services_response, true);
    if ($services_data && isset($services_data['success'])) {
        echo "✓ Success: " . ($services_data['success'] ? 'Yes' : 'No') . "\n";
        if ($services_data['success']) {
            echo "  Services count: " . count($services_data['data'] ?? []) . "\n";
            if (!empty($services_data['data'])) {
                echo "  First service: " . ($services_data['data'][0]['service_name'] ?? 'Unknown') . "\n";
            }
        } else {
            echo "  Error: " . ($services_data['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "✗ Invalid JSON response\n";
        echo "Raw response: " . substr($services_response, 0, 500) . "\n";
    }
} else {
    echo "✗ Failed to connect to API\n";
}

echo "\n";

// Test 3: Workshop Bookings API (existing)
echo "3. Testing Workshop Bookings API (existing):\n";
$bookings_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_bookings_api.php";
$bookings_data = json_encode(['workshopId' => $workshop_id]);

$bookings_context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $bookings_data
    ]
]);

$bookings_response = @file_get_contents($bookings_url, false, $bookings_context);
if ($bookings_response !== false) {
    $bookings_result = json_decode($bookings_response, true);
    if ($bookings_result && isset($bookings_result['success'])) {
        echo "✓ Success: " . ($bookings_result['success'] ? 'Yes' : 'No') . "\n";
        if ($bookings_result['success']) {
            echo "  Bookings count: " . count($bookings_result['data'] ?? []) . "\n";
            if (!empty($bookings_result['data'])) {
                echo "  First booking status: " . ($bookings_result['data'][0]['status'] ?? 'Unknown') . "\n";
            }
        } else {
            echo "  Error: " . ($bookings_result['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "✗ Invalid JSON response\n";
        echo "Raw response: " . substr($bookings_response, 0, 500) . "\n";
    }
} else {
    echo "✗ Failed to connect to API\n";
}

echo "\nAPI testing complete!\n";
?>
