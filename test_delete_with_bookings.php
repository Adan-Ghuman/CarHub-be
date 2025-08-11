<?php
// Test delete service with active bookings
require_once 'config.php';

echo "=== Testing Delete Service with Active Bookings ===\n";

// First, create a test service
echo "\n1. Creating a test service...\n";
$insert_service = "INSERT INTO workshop_services (workshop_id, service_name, service_category, description, price, duration) 
                   VALUES (3, 'Test Service for Booking', 'general', 'Test service to check booking restrictions', 100, 60)";
mysqli_query($conn, $insert_service);
$test_service_id = mysqli_insert_id($conn);
echo "Created test service with ID: $test_service_id\n";

// Create a test booking
echo "\n2. Creating a test booking...\n";
$insert_booking = "INSERT INTO workshop_bookings (user_id, workshop_id, service_id, booking_date, status, total_amount) 
                   VALUES (1, 3, $test_service_id, '2025-08-01', 'pending', 100)";
mysqli_query($conn, $insert_booking);
$test_booking_id = mysqli_insert_id($conn);
echo "Created test booking with ID: $test_booking_id\n";

// Now try to delete the service
echo "\n3. Attempting to delete service with active booking...\n";
$api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/delete_service_api.php";
$test_data = ['service_id' => $test_service_id, 'workshop_id' => 3];

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
curl_close($ch);

echo "API Response: $response\n";

// Clean up - remove the test booking and service
echo "\n4. Cleaning up test data...\n";
mysqli_query($conn, "DELETE FROM workshop_bookings WHERE id = $test_booking_id");
mysqli_query($conn, "DELETE FROM workshop_services WHERE id = $test_service_id");
echo "✅ Test data cleaned up\n";

echo "\n=== Test complete ===\n";
?>
