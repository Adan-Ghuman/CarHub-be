<?php
include "config.php";

echo "Adding updated_at column to workshop_reviews table...\n";

$sql = "ALTER TABLE workshop_reviews ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP";

if (mysqli_query($conn, $sql)) {
    echo "Successfully added updated_at column\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
