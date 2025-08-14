<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'message' => 'API test successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'config_loaded' => file_exists('./config.php')
]);

// Test database connection
try {
    require_once './config.php';
    echo "\n" . json_encode([
        'database_connection' => 'success',
        'mysqli_available' => isset($conn),
        'pdo_available' => isset($pdo)
    ]);
} catch (Exception $e) {
    echo "\n" . json_encode([
        'database_connection' => 'failed',
        'error' => $e->getMessage()
    ]);
}
?>
