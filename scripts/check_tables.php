<?php
include 'config.php';

echo "Tables in database:\n";
$result = $conn->query('SHOW TABLES');
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "\n";
}

echo "\nChecking bookings table structure:\n";
$result = $conn->query('DESCRIBE bookings');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Bookings table does not exist\n";
}

echo "\nSample bookings data:\n";
$result = $conn->query('SELECT id, workshop_id, user_id, status FROM bookings LIMIT 5');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Workshop: " . $row['workshop_id'] . ", User: " . $row['user_id'] . ", Status: " . $row['status'] . "\n";
    }
} else {
    echo "No bookings found or table does not exist\n";
}

$conn->close();
?>
