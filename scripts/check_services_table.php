<?php
require_once 'config.php';

echo "=== Workshop_services Table Structure ===\n";
$result = mysqli_query($conn, 'DESCRIBE workshop_services');
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo 'Error: ' . mysqli_error($conn) . "\n";
}
?>
