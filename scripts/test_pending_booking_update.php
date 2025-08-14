<?php
// Test with a pending booking
echo "=== Testing with Pending Booking ===\n";

require_once 'config.php';

// Find a pending booking
$pending_query = "SELECT id, status FROM workshop_bookings WHERE status = 'pending' AND workshop_id = 3 LIMIT 1";
$result = mysqli_query($conn, $pending_query);

if ($result && mysqli_num_rows($result) > 0) {
    $booking = mysqli_fetch_assoc($result);
    $bookingId = $booking['id'];
    
    echo "Found pending booking ID: $bookingId\n";
    echo "Current status: {$booking['status']}\n";
    
    // Test the API
    $api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/update_booking_status_api.php";
    $test_data = [
        'bookingId' => $bookingId,
        'workshopId' => 3,
        'status' => 'confirmed'
    ];
    
    echo "\nTesting status update API...\n";
    echo "Request data: " . json_encode($test_data) . "\n";
    
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
    echo "Response: $response\n";
    
    $response_data = json_decode($response, true);
    if ($response_data) {
        if ($response_data['success'] ?? false) {
            echo "✅ SUCCESS! Booking status updated successfully!\n";
        } else {
            echo "❌ FAILED: " . ($response_data['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ Invalid JSON response\n";
    }
    
} else {
    echo "No pending bookings found\n";
}

echo "\n=== Test Complete ===\n";
?>
