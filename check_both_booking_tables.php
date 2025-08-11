<?php
include 'config.php';

echo "=== BOOKINGS table ===\n";
$result = $conn->query('SELECT COUNT(*) as count FROM bookings');
$row = $result->fetch_assoc();
echo "Total records: " . $row['count'] . "\n";

$result = $conn->query('SELECT workshop_id, COUNT(*) as count FROM bookings GROUP BY workshop_id');
echo "By workshop:\n";
while ($row = $result->fetch_assoc()) {
    echo "  Workshop " . $row['workshop_id'] . ": " . $row['count'] . " bookings\n";
}

echo "\n=== WORKSHOP_BOOKINGS table ===\n";
$result = $conn->query('SELECT COUNT(*) as count FROM workshop_bookings');
$row = $result->fetch_assoc();
echo "Total records: " . $row['count'] . "\n";

$result = $conn->query('SELECT workshop_id, COUNT(*) as count FROM workshop_bookings GROUP BY workshop_id');
echo "By workshop:\n";
while ($row = $result->fetch_assoc()) {
    echo "  Workshop " . $row['workshop_id'] . ": " . $row['count'] . " bookings\n";
}

echo "\n=== WORKSHOP_BOOKINGS structure ===\n";
$result = $conn->query('DESCRIBE workshop_bookings');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\nSample workshop_bookings data:\n";
$result = $conn->query('SELECT id, workshop_id, user_id, status FROM workshop_bookings LIMIT 5');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Workshop: " . $row['workshop_id'] . ", User: " . $row['user_id'] . ", Status: " . $row['status'] . "\n";
    }
}

$conn->close();
?>
