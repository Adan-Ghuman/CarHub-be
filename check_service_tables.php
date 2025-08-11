<?php
include "config.php";

// Check if services table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'services'");
if (mysqli_num_rows($result) > 0) {
    echo "✅ services table exists\n";
} else {
    echo "❌ services table does not exist\n";
}

// Check if workshop_services table exists
$result2 = mysqli_query($conn, "SHOW TABLES LIKE 'workshop_services'");
if (mysqli_num_rows($result2) > 0) {
    echo "✅ workshop_services table exists\n";
    
    // Show structure
    $structure = mysqli_query($conn, "DESCRIBE workshop_services");
    echo "\nworkshop_services table structure:\n";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "❌ workshop_services table does not exist\n";
}

mysqli_close($conn);
?>
