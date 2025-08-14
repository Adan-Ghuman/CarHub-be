<?php
// Test all workshop APIs with corrected database schema

echo "=== Testing All Workshop APIs ===\n\n";

// Define workshop ID to test with
$workshop_id = 1;

// Test 1: Workshop Stats API
echo "1. Testing Workshop Stats API:\n";
$statsData = json_encode(['workshop_id' => $workshop_id]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $statsData
    ]
]);

$statsResult = file_get_contents('http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_stats_api.php', false, $context);
echo "Stats Response: " . $statsResult . "\n\n";

// Test 2: Workshop Services API
echo "2. Testing Workshop Services API:\n";
$servicesData = json_encode(['workshopId' => $workshop_id, 'isActive' => true]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $servicesData
    ]
]);

$servicesResult = file_get_contents('http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_services_api.php', false, $context);
echo "Services Response: " . $servicesResult . "\n\n";

// Test 3: Workshop Bookings API
echo "3. Testing Workshop Bookings API:\n";
$bookingsData = json_encode(['workshop_id' => $workshop_id]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $bookingsData
    ]
]);

$bookingsResult = file_get_contents('http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_bookings_api.php', false, $context);
echo "Bookings Response: " . $bookingsResult . "\n\n";

// Test 4: Workshop Reviews API
echo "4. Testing Workshop Reviews API:\n";
$reviewsData = json_encode(['workshop_id' => $workshop_id]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $reviewsData
    ]
]);

$reviewsResult = file_get_contents('http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_reviews_api.php', false, $context);
echo "Reviews Response: " . $reviewsResult . "\n\n";

echo "=== All API Tests Completed ===\n";
?>
