<?php
require_once 'config.php';

echo "=== Active Bookings for Service ID 10 ===\n";

$bookings_query = "SELECT id, user_id, service_id, status, booking_date, total_amount 
                   FROM workshop_bookings 
                   WHERE service_id = 10 AND status IN ('pending', 'confirmed')
                   ORDER BY booking_date DESC";

$result = mysqli_query($conn, $bookings_query);

if (mysqli_num_rows($result) > 0) {
    echo "Found " . mysqli_num_rows($result) . " active bookings:\n\n";
    while ($booking = mysqli_fetch_assoc($result)) {
        echo "Booking ID: {$booking['id']}\n";
        echo "User ID: {$booking['user_id']}\n";
        echo "Status: {$booking['status']}\n";
        echo "Date: {$booking['booking_date']}\n";
        echo "Amount: {$booking['total_amount']}\n";
        echo "---\n";
    }
} else {
    echo "No active bookings found for service ID 10\n";
}

echo "\n=== All Bookings Status Summary ===\n";
$summary_query = "SELECT status, COUNT(*) as count 
                  FROM workshop_bookings 
                  WHERE service_id = 10 
                  GROUP BY status";

$summary_result = mysqli_query($conn, $summary_query);
while ($row = mysqli_fetch_assoc($summary_result)) {
    echo "Status '{$row['status']}': {$row['count']} bookings\n";
}
?>
