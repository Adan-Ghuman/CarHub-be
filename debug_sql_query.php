<?php
// Debug the exact SQL query being used in the API
require_once 'config.php';

echo "=== Debugging SQL Query in Update Booking Status API ===\n";

$bookingId = 7;
$workshopId = 3;

// This is the exact query from the API
$bookingQuery = "SELECT wb.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                       ws.service_name, ws.price
                FROM workshop_bookings wb
                LEFT JOIN users u ON wb.user_id = u.id
                LEFT JOIN workshop_services ws ON wb.service_id = ws.id
                WHERE wb.id = '$bookingId' AND wb.workshop_id = '$workshopId'";

echo "SQL Query:\n$bookingQuery\n\n";

$result = mysqli_query($conn, $bookingQuery);

if (!$result) {
    echo "❌ SQL Error: " . mysqli_error($conn) . "\n";
} else {
    $num_rows = mysqli_num_rows($result);
    echo "Number of rows returned: $num_rows\n";
    
    if ($num_rows > 0) {
        echo "✅ Query found the booking!\n";
        $booking = mysqli_fetch_assoc($result);
        echo "Booking details:\n";
        echo "- ID: {$booking['id']}\n";
        echo "- Workshop ID: {$booking['workshop_id']}\n";
        echo "- User ID: {$booking['user_id']}\n";
        echo "- Service ID: {$booking['service_id']}\n";
        echo "- Status: {$booking['status']}\n";
        echo "- User Name: " . ($booking['user_name'] ?? 'NULL') . "\n";
        echo "- Service Name: " . ($booking['service_name'] ?? 'NULL') . "\n";
    } else {
        echo "❌ No rows found - this explains the API error!\n";
        
        // Let's check what the booking record actually looks like
        echo "\nLet's check the actual booking record:\n";
        $simple_query = "SELECT * FROM workshop_bookings WHERE id = $bookingId";
        $simple_result = mysqli_query($conn, $simple_query);
        
        if ($simple_result && mysqli_num_rows($simple_result) > 0) {
            $booking = mysqli_fetch_assoc($simple_result);
            echo "✅ Booking $bookingId exists:\n";
            echo "- Workshop ID: {$booking['workshop_id']}\n";
            echo "- User ID: {$booking['user_id']}\n";
            echo "- Service ID: {$booking['service_id']}\n";
            echo "- Status: {$booking['status']}\n";
            
            // Check if the joins are the problem
            echo "\nChecking JOIN issues:\n";
            
            // Check if user exists
            $user_check = mysqli_query($conn, "SELECT id, name FROM users WHERE id = {$booking['user_id']}");
            if ($user_check && mysqli_num_rows($user_check) > 0) {
                $user = mysqli_fetch_assoc($user_check);
                echo "✅ User {$booking['user_id']} exists: {$user['name']}\n";
            } else {
                echo "❌ User {$booking['user_id']} not found!\n";
            }
            
            // Check if service exists
            $service_check = mysqli_query($conn, "SELECT id, service_name FROM workshop_services WHERE id = {$booking['service_id']}");
            if ($service_check && mysqli_num_rows($service_check) > 0) {
                $service = mysqli_fetch_assoc($service_check);
                echo "✅ Service {$booking['service_id']} exists: {$service['service_name']}\n";
            } else {
                echo "❌ Service {$booking['service_id']} not found!\n";
            }
            
        } else {
            echo "❌ Booking $bookingId doesn't exist at all!\n";
        }
    }
}

echo "\n=== Debug Complete ===\n";
?>
