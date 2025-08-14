<?php
include "config.php";

echo "Modifying workshop_reviews table...\n";

// First, make booking_id nullable
$query1 = "ALTER TABLE workshop_reviews MODIFY booking_id INT NULL";
if (mysqli_query($conn, $query1)) {
    echo "booking_id made nullable\n";
} else {
    echo "Error modifying booking_id: " . mysqli_error($conn) . "\n";
}

// Drop the foreign key constraint
$query2 = "ALTER TABLE workshop_reviews DROP FOREIGN KEY workshop_reviews_ibfk_3";
if (mysqli_query($conn, $query2)) {
    echo "Foreign key constraint removed\n";
} else {
    echo "Error removing foreign key: " . mysqli_error($conn) . "\n";
}

// Also remove the unique constraint if it exists
$query3 = "ALTER TABLE workshop_reviews DROP INDEX unique_user_workshop";
if (mysqli_query($conn, $query3)) {
    echo "Unique constraint removed\n";
} else {
    echo "Note: Unique constraint may not exist: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
