<?php
require_once 'config.php';

echo "Workshop Tables Structure\n";
echo "========================\n\n";

$tables = ['workshop_bookings', 'workshop_services', 'workshop_reviews'];

foreach ($tables as $table) {
    echo "Table: $table\n";
    echo str_repeat("-", strlen("Table: $table")) . "\n";
    
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo sprintf("  %-20s %-15s %-10s %-10s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key']
            );
        }
    } else {
        echo "  Error: " . $conn->error . "\n";
    }
    echo "\n";
}
?>
