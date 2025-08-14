<?php
include "config.php";

echo "Checking workshop_reviews table structure:\n";

$result = mysqli_query($conn, "DESCRIBE workshop_reviews");

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . " - " . $row['Type'] . " (" . $row['Null'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
