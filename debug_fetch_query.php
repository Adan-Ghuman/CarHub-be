<?php
// Debug the exact failing query
require_once 'config.php';

echo "=== Testing the Fetch Query ===\n";

$bookingId = 7;

// This is the exact query from line 96-105 of the API
$fetchQuery = "SELECT wb.*, 
                     w.name as workshop_name, w.address as workshop_address,
                     ws.service_name, ws.price, ws.estimated_time,
                     u.Name as user_name, u.Email as user_email, u.PhoneNumber as user_phone
              FROM workshop_bookings wb
              LEFT JOIN workshops w ON wb.workshop_id = w.id
              LEFT JOIN workshop_services ws ON wb.service_id = ws.id
              LEFT JOIN users u ON wb.user_id = u.id
              WHERE wb.id = '$bookingId'";

echo "SQL Query:\n$fetchQuery\n\n";

$result = mysqli_query($conn, $fetchQuery);

if (!$result) {
    echo "❌ SQL Error: " . mysqli_error($conn) . "\n";
} else {
    $num_rows = mysqli_num_rows($result);
    echo "✅ Query executed successfully!\n";
    echo "Number of rows: $num_rows\n";
    
    if ($num_rows > 0) {
        $data = mysqli_fetch_assoc($result);
        echo "Sample data:\n";
        echo "- Booking ID: {$data['id']}\n";
        echo "- Workshop Name: " . ($data['workshop_name'] ?? 'NULL') . "\n";
        echo "- User Name: " . ($data['user_name'] ?? 'NULL') . "\n";
        echo "- Service Name: " . ($data['service_name'] ?? 'NULL') . "\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
