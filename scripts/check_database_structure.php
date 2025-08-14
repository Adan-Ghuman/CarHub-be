<?php
require_once 'config.php';

echo "Database Structure Check\n";
echo "========================\n\n";

// Show all tables
$tables_result = $conn->query("SHOW TABLES");
if ($tables_result) {
    echo "Tables in database:\n";
    while ($table = $tables_result->fetch_array()) {
        echo "- " . $table[0] . "\n";
    }
} else {
    echo "Failed to get tables: " . $conn->error . "\n";
}

echo "\n";

// Check specific tables that our APIs need
$required_tables = ['workshops', 'services', 'bookings', 'reviews', 'users'];

foreach ($required_tables as $table) {
    echo "Checking table: $table\n";
    $check_result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check_result && $check_result->num_rows > 0) {
        echo "✓ Table $table exists\n";
        
        // Show columns
        $columns_result = $conn->query("DESCRIBE $table");
        if ($columns_result) {
            echo "  Columns: ";
            $columns = [];
            while ($column = $columns_result->fetch_array()) {
                $columns[] = $column[0];
            }
            echo implode(', ', $columns) . "\n";
        }
    } else {
        echo "✗ Table $table missing\n";
    }
    echo "\n";
}
?>
