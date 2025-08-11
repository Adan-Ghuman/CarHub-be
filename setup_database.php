<?php
include "config.php";

// Read and execute the SQL file
$sql = file_get_contents('create_workshop_tables.sql');

// Remove comments and split by semicolons
$statements = array_filter(
    array_map('trim', explode(';', preg_replace('/--.*$/m', '', $sql))),
    function($stmt) { return !empty($stmt); }
);

echo "<h2>Database Setup Results:</h2>\n";

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    echo "<p><strong>Executing:</strong> " . substr($statement, 0, 50) . "...</p>\n";
    
    if (mysqli_query($conn, $statement)) {
        echo "<p style='color: green;'>✓ Success</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>\n";
    }
}

// Check table structure
echo "<h3>Table Structure Check:</h3>\n";

$tables = ['workshops', 'workshop_services', 'workshop_reviews', 'service_bookings'];

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>\n";
        
        // Show columns
        $columns = mysqli_query($conn, "SHOW COLUMNS FROM $table");
        echo "<ul>\n";
        while ($col = mysqli_fetch_assoc($columns)) {
            echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' does not exist</p>\n";
    }
}

mysqli_close($conn);
echo "<p><strong>Database setup complete!</strong></p>\n";
?>
