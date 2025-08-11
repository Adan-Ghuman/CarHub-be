<?php
require_once 'config.php';

echo "Checking reviews table structure...\n\n";

try {
    $result = $conn->query("DESCRIBE reviews");
    
    if ($result) {
        echo "Reviews table columns:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
