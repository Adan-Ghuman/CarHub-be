<?php
include "config.php";

echo "Checking workshop_bookings table structure and data...\n";

// Check if table exists
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'workshop_bookings'");
if (mysqli_num_rows($tableCheck) == 0) {
    echo "workshop_bookings table does not exist!\n";
    exit;
}

// Show table structure
echo "\nTable structure:\n";
$structure = mysqli_query($conn, "DESCRIBE workshop_bookings");
while ($row = mysqli_fetch_assoc($structure)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

// Check for any bookings
echo "\nChecking for bookings...\n";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM workshop_bookings");
$count = mysqli_fetch_assoc($result);
echo "Total bookings: " . $count['count'] . "\n";

// Show sample data
if ($count['count'] > 0) {
    echo "\nSample booking data:\n";
    $sample = mysqli_query($conn, "SELECT * FROM workshop_bookings LIMIT 1");
    $booking = mysqli_fetch_assoc($sample);
    foreach ($booking as $key => $value) {
        echo "$key: $value\n";
    }
}

mysqli_close($conn);
?>
