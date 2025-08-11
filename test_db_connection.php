<?php
// Test database connectivity
require_once 'config.php';

echo "Testing Database Connection\n";
echo "===========================\n\n";

// Test mysqli connection (existing pattern)
if ($conn) {
    echo "✓ MySQLi connection successful\n";
    
    // Test a simple query
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "✓ Database query successful\n";
        echo "Tables found: " . $result->num_rows . "\n";
    } else {
        echo "✗ Database query failed: " . $conn->error . "\n";
    }
} else {
    echo "✗ MySQLi connection failed\n";
}

echo "\nDatabase connectivity test complete!\n";
?>
