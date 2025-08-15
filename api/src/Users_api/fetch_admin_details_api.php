<?php

include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Handle both POST and GET requests
$adminID = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = file_get_contents('php://input');
    $requestData = json_decode($jsonInput, true);
    
    if ($requestData === null || !isset($requestData['adminID'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data or missing adminID']);
        exit;
    }
    
    $adminID = $requestData['adminID'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['adminID'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing adminID parameter']);
        exit;
    }
    
    $adminID = $_GET['adminID'];
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Query to get admin details
    $fetchAdminQuery = "SELECT 
                        id as AdminID, 
                        username, 
                        email, 
                        full_name, 
                        created_at
                      FROM admin 
                      WHERE id = '$adminID'";
    
    $result = mysqli_query($conn, $fetchAdminQuery);

    if ($result && mysqli_num_rows($result) > 0) {
        $adminData = mysqli_fetch_assoc($result);
        
        // Format response
        echo json_encode([
            'success' => true,
            'AdminID' => $adminData['AdminID'],
            'id' => $adminData['AdminID'],
            'username' => $adminData['username'],
            'email' => $adminData['email'],
            'full_name' => $adminData['full_name'],
            'role' => 'admin',
            'created_at' => $adminData['created_at']
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Admin not found'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
