<?php
include "config.php";

// Get table structure
$query = "DESCRIBE cars";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "Cars table structure:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Column: " . $row['Field'] . " | Type: " . $row['Type'] . " | Null: " . $row['Null'] . " | Default: " . $row['Default'] . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
