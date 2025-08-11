<?php
require_once 'config.php';

echo "=== Workshops Table Structure ===\n";
$result = mysqli_query($conn, 'DESCRIBE workshops');
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo 'Error: ' . mysqli_error($conn) . "\n";
}
?>
