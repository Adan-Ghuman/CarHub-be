<?php
// Test delete service API
echo "=== Testing Delete Service API ===\n";

// First, let's check what services exist
require_once 'config.php';

echo "\n1. Current services in database:\n";
$services_query = "SELECT id, service_name, workshop_id, is_active FROM workshop_services WHERE workshop_id = 3 ORDER BY id";
$result = mysqli_query($conn, $services_query);
while ($service = mysqli_fetch_assoc($result)) {
    $status = $service['is_active'] ? 'ACTIVE' : 'INACTIVE';
    echo "   ID: {$service['id']} | Name: {$service['service_name']} | Status: {$status}\n";
}

echo "\n2. Testing delete API call...\n";

// Test the API endpoint
$api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/delete_service_api.php";

// Let's try to delete service ID 15 (the test service "Hsjj")
$test_data = [
    'service_id' => 15,
    'workshop_id' => 3
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "API Response: $response\n";

echo "\n3. Services after delete attempt:\n";
$result = mysqli_query($conn, $services_query);
while ($service = mysqli_fetch_assoc($result)) {
    $status = $service['is_active'] ? 'ACTIVE' : 'INACTIVE';
    echo "   ID: {$service['id']} | Name: {$service['service_name']} | Status: {$status}\n";
}

// Let's also check if there are any active bookings for service 15
echo "\n4. Checking for active bookings on service ID 15:\n";
$booking_check = "SELECT COUNT(*) as count FROM workshop_bookings WHERE service_id = 15 AND status IN ('pending', 'confirmed')";
$booking_result = mysqli_query($conn, $booking_check);
$booking_count = mysqli_fetch_assoc($booking_result)['count'];
echo "Active bookings for service 15: $booking_count\n";

if ($booking_count > 0) {
    echo "⚠️  Cannot delete service with active bookings!\n";
    echo "Listing active bookings:\n";
    $bookings_query = "SELECT id, user_id, status, booking_date FROM workshop_bookings WHERE service_id = 15 AND status IN ('pending', 'confirmed')";
    $bookings_result = mysqli_query($conn, $bookings_query);
    while ($booking = mysqli_fetch_assoc($bookings_result)) {
        echo "   Booking ID: {$booking['id']} | User: {$booking['user_id']} | Status: {$booking['status']} | Date: {$booking['booking_date']}\n";
    }
}

?>
