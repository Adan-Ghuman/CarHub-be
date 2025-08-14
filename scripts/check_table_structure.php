<?php
require_once 'config.php';

echo "=== Users Table Structure ===\n";
$result = mysqli_query($conn, 'DESCRIBE users');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\n=== Workshop_bookings Table Structure ===\n";
$result2 = mysqli_query($conn, 'DESCRIBE workshop_bookings');
while($row = mysqli_fetch_assoc($result2)) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>
