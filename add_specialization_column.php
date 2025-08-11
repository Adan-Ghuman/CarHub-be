<?php
// Include database configuration
include 'config.php';

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $query = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

// Check if the specialization column exists in the workshops table
if (!columnExists($conn, 'workshops', 'specialization')) {
    echo "Adding specialization column to workshops table...\n";
    
    // Add the specialization column
    $query = "ALTER TABLE workshops ADD COLUMN specialization VARCHAR(255) DEFAULT NULL AFTER description";
    if (mysqli_query($conn, $query)) {
        echo "Specialization column added successfully.\n";
    } else {
        echo "Error adding specialization column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Specialization column already exists in workshops table.\n";
}

mysqli_close($conn);

echo "Database check completed.\n";
?>
