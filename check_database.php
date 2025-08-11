<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once './config.php';

try {
    // Check if tables exist
    $tables_to_check = ['users', 'cars', 'workshops'];
    $table_status = [];
    
    foreach ($tables_to_check as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $result->rowCount() > 0;
        $table_status[$table] = $exists;
        
        if ($exists) {
            // Get table structure
            $result = $pdo->query("DESCRIBE $table");
            $table_status[$table . '_columns'] = $result->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    echo json_encode([
        'database_connection' => 'success',
        'tables_status' => $table_status,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ], JSON_PRETTY_PRINT);
}
?>
