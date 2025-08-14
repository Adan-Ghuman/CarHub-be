<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

require_once '../config/config.php';

try {
    // First check if required tables exist
    $tables_check = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($tables_check->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Users table does not exist',
            'data' => []
        ]);
        exit;
    }

    // Get all users with additional stats (simplified query)
    $query = "
        SELECT 
            u.id,
            u.Name,
            u.Email,
            u.PhoneNumber,
            u.Location,
            u.role,
            u.is_verified,
            u.created_at,
            u.updated_at
        FROM users u
        ORDER BY u.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formatted_users = [];
    foreach ($users as $user) {
        $formatted_users[] = [
            'id' => (int)$user['id'],
            'Name' => $user['Name'] ?? 'Unknown User',
            'Email' => $user['Email'] ?? '',
            'PhoneNumber' => $user['PhoneNumber'] ?? '',
            'Location' => $user['Location'] ?? 'Unknown',
            'role' => $user['role'] ?? 'customer',
            'is_verified' => (int)$user['is_verified'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at'],
            'total_ads' => 0,
            'has_workshop' => false,
            'workshop_status' => null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted_users,
        'total_count' => count($formatted_users)
    ]);
    
} catch (Exception $e) {
    error_log("Get all users error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch users data: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
