<?php
include "config.php";

echo "Checking workshop_services table for workshop_id = 3:\n\n";

$query = "SELECT * FROM workshop_services WHERE workshop_id = 3 ORDER BY id";
$result = mysqli_query($conn, $query);

if ($result) {
    $count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $count++;
        echo "Service #$count:\n";
        echo "  ID: " . $row['id'] . "\n";
        echo "  Name: " . $row['service_name'] . "\n";
        echo "  Category: " . $row['service_category'] . "\n";
        echo "  Price: " . $row['price'] . "\n";
        echo "  Duration: " . $row['duration'] . "\n";
        echo "  Description: " . $row['description'] . "\n";
        echo "  Active: " . ($row['is_active'] ? 'Yes' : 'No') . "\n";
        echo "  Created: " . $row['created_at'] . "\n";
        echo "  Updated: " . $row['updated_at'] . "\n";
        echo "\n";
    }
    
    echo "Total services found: $count\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
