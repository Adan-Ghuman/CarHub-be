<?php
include "config.php";

echo "Checking workshop_bookings table:\n";

$result = mysqli_query($conn, "SELECT id, user_id, workshop_id, status, service_id, booking_date FROM workshop_bookings ORDER BY id DESC LIMIT 10");

if ($result) {
    echo "ID | User | Workshop | Status | Service | Date\n";
    echo "----------------------------------------------------\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['id'] . " | " . $row['user_id'] . " | " . $row['workshop_id'] . " | " . $row['status'] . " | " . $row['service_id'] . " | " . $row['booking_date'] . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

// Let's update a booking to completed status for testing
echo "\nUpdating booking 1 to completed status...\n";
$updateResult = mysqli_query($conn, "UPDATE workshop_bookings SET status = 'completed' WHERE id = 1");
if ($updateResult) {
    echo "Successfully updated booking to completed status\n";
} else {
    echo "Error updating booking: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
