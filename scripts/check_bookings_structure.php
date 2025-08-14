<?php
require_once 'config.php';

echo "Checking bookings table structure...\n";
$result = $conn->query("DESCRIBE bookings");

if ($result) {
    echo "Bookings table columns:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}
?>
