<?php
// Debug booking status update issue
require_once 'config.php';

echo "=== Debugging Booking Status Update Issue ===\n";

// First, let's check what bookings exist and their workshop IDs
echo "\n1. Current bookings in workshop_bookings table:\n";
$bookings_query = "SELECT id, user_id, workshop_id, service_id, status, booking_date, created_at 
                   FROM workshop_bookings 
                   ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $bookings_query);

echo "Booking ID | User ID | Workshop ID | Service ID | Status | Booking Date\n";
echo "-----------|---------|-------------|------------|--------|-------------\n";
while ($booking = mysqli_fetch_assoc($result)) {
    echo sprintf("%-10s | %-7s | %-11s | %-10s | %-6s | %s\n", 
                 $booking['id'], 
                 $booking['user_id'], 
                 $booking['workshop_id'], 
                 $booking['service_id'], 
                 $booking['status'], 
                 $booking['booking_date']);
}

// Check what user 11's workshop ID should be
echo "\n2. User 11 details:\n";
$user_query = "SELECT id, name, workshop_id FROM users WHERE id = 11";
$user_result = mysqli_query($conn, $user_query);
if ($user = mysqli_fetch_assoc($user_result)) {
    echo "User ID: {$user['id']}\n";
    echo "Name: {$user['name']}\n";
    echo "Workshop ID: " . ($user['workshop_id'] ?? 'NULL') . "\n";
} else {
    echo "User 11 not found\n";
}

// Check workshop 3 details
echo "\n3. Workshop 3 details:\n";
$workshop_query = "SELECT id, name, owner_id FROM workshops WHERE id = 3";
$workshop_result = mysqli_query($conn, $workshop_query);
if ($workshop = mysqli_fetch_assoc($workshop_result)) {
    echo "Workshop ID: {$workshop['id']}\n";
    echo "Name: {$workshop['name']}\n";
    echo "Owner ID: {$workshop['owner_id']}\n";
} else {
    echo "Workshop 3 not found\n";
}

// Test a specific booking update scenario
echo "\n4. Testing update scenario:\n";
$test_booking_query = "SELECT id, workshop_id, status FROM workshop_bookings WHERE status = 'pending' LIMIT 1";
$test_result = mysqli_query($conn, $test_booking_query);

if ($test_booking = mysqli_fetch_assoc($test_result)) {
    $test_booking_id = $test_booking['id'];
    $actual_workshop_id = $test_booking['workshop_id'];
    
    echo "Test Booking ID: $test_booking_id\n";
    echo "Actual Workshop ID: $actual_workshop_id\n";
    echo "Current Status: {$test_booking['status']}\n";
    
    // Test with correct workshop ID
    echo "\n5. Testing API call with CORRECT workshop ID ($actual_workshop_id):\n";
    $api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/update_booking_status_api.php";
    $correct_data = [
        'bookingId' => $test_booking_id,
        'workshopId' => $actual_workshop_id,
        'status' => 'confirmed'
    ];
    
    $response = testUpdateAPI($api_url, $correct_data);
    echo "Response with correct workshop ID: $response\n";
    
    // Test with wrong workshop ID (what might be happening)
    echo "\n6. Testing API call with WRONG workshop ID (3):\n";
    $wrong_data = [
        'bookingId' => $test_booking_id,
        'workshopId' => 3,
        'status' => 'confirmed'
    ];
    
    $response = testUpdateAPI($api_url, $wrong_data);
    echo "Response with workshop ID 3: $response\n";
    
} else {
    echo "No pending bookings found for testing\n";
}

function testUpdateAPI($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

echo "\n=== Debug Complete ===\n";
?>
