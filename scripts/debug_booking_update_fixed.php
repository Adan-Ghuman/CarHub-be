<?php
// Debug booking status update issue - Fixed version
require_once 'config.php';

echo "=== Debugging Booking Status Update Issue ===\n";

// First, let's check what bookings exist and their workshop IDs
echo "\n1. Current bookings in workshop_bookings table:\n";
$bookings_query = "SELECT id, user_id, workshop_id, service_id, status, booking_date, created_at 
                   FROM workshop_bookings 
                   ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $bookings_query);

if ($result) {
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
} else {
    echo "Error querying bookings: " . mysqli_error($conn) . "\n";
}

// Check what user 11's details are
echo "\n2. User 11 details:\n";
$user_query = "SELECT id, name, email FROM users WHERE id = 11";
$user_result = mysqli_query($conn, $user_query);
if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);
    echo "User ID: {$user['id']}\n";
    echo "Name: {$user['name']}\n";
    echo "Email: {$user['email']}\n";
} else {
    echo "User 11 not found or query error: " . mysqli_error($conn) . "\n";
}

// Check workshop 3 details
echo "\n3. Workshop 3 details:\n";
$workshop_query = "SELECT id, name, owner_user_id FROM workshops WHERE id = 3";
$workshop_result = mysqli_query($conn, $workshop_query);
if ($workshop_result && mysqli_num_rows($workshop_result) > 0) {
    $workshop = mysqli_fetch_assoc($workshop_result);
    echo "Workshop ID: {$workshop['id']}\n";
    echo "Name: {$workshop['name']}\n";
    echo "Owner User ID: {$workshop['owner_user_id']}\n";
} else {
    echo "Workshop 3 not found or query error: " . mysqli_error($conn) . "\n";
    
    // Let's check what workshops exist
    echo "Available workshops:\n";
    $all_workshops = mysqli_query($conn, "SELECT id, name, owner_user_id FROM workshops LIMIT 5");
    if ($all_workshops) {
        while ($w = mysqli_fetch_assoc($all_workshops)) {
            echo "  ID: {$w['id']}, Name: {$w['name']}, Owner: {$w['owner_user_id']}\n";
        }
    }
}

// Test a specific booking update scenario
echo "\n4. Testing update scenario:\n";
$test_booking_query = "SELECT id, workshop_id, status FROM workshop_bookings WHERE status = 'pending' LIMIT 1";
$test_result = mysqli_query($conn, $test_booking_query);

if ($test_result && mysqli_num_rows($test_result) > 0) {
    $test_booking = mysqli_fetch_assoc($test_result);
    $test_booking_id = $test_booking['id'];
    $actual_workshop_id = $test_booking['workshop_id'];
    
    echo "Test Booking ID: $test_booking_id\n";
    echo "Actual Workshop ID: $actual_workshop_id\n";
    echo "Current Status: {$test_booking['status']}\n";
    
    // The issue might be here - let's see what workshop ID the frontend is sending
    echo "\n5. The problem:\n";
    echo "Frontend is probably sending workshop ID: 3 (hardcoded for user 11)\n";
    echo "But booking actually belongs to workshop ID: $actual_workshop_id\n";
    
    if ($actual_workshop_id == 3) {
        echo "✅ Workshop IDs match - this shouldn't be the issue\n";
    } else {
        echo "❌ Workshop ID MISMATCH! This is the problem!\n";
        echo "Frontend needs to use workshop ID: $actual_workshop_id instead of 3\n";
    }
    
} else {
    echo "No pending bookings found for testing\n";
}

echo "\n6. Summary:\n";
echo "- All bookings shown above belong to workshop_id = 3\n";
echo "- User 11 should be managing workshop 3\n";
echo "- If frontend is sending workshopId = 3, it should work\n";
echo "- The error suggests there's a mismatch somewhere\n";

echo "\n=== Debug Complete ===\n";
?>
