<?php
include __DIR__ . "/../../config/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($data['workshop_id']) || !isset($data['action'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Missing required fields: workshop_id and action are required'
        ]);
        exit;
    }

    $workshop_id = (int)$data['workshop_id'];
    $action = trim(strtolower($data['action'])); // 'approve' or 'reject'

    // Validate action
    if (!in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Invalid action. Use "approve" or "reject".'
        ]);
        exit;
    }

    try {
        // First, check if workshop exists and is in pending status
        $checkQuery = "SELECT id, name, status FROM workshops WHERE id = '$workshop_id'";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if (!$checkResult || mysqli_num_rows($checkResult) === 0) {
            throw new Exception('Workshop not found');
        }
        
        $workshop = mysqli_fetch_assoc($checkResult);
        
        if ($workshop['status'] !== 'pending') {
            throw new Exception('Workshop is not in pending status. Current status: ' . $workshop['status']);
        }
        
        // Update workshop based on action
        if ($action === 'approve') {
            $updateQuery = "UPDATE workshops SET 
                status = 'active', 
                is_verified = TRUE, 
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = '$workshop_id'";
            $message = 'Workshop "' . $workshop['name'] . '" has been approved successfully!';
        } else { // reject
            $updateQuery = "UPDATE workshops SET 
                status = 'rejected', 
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = '$workshop_id'";
            $message = 'Workshop "' . $workshop['name'] . '" has been rejected.';
        }
        
        if (mysqli_query($conn, $updateQuery)) {
            // Log the action (optional - create admin_logs table if needed)
            $logQuery = "INSERT INTO admin_logs (action_type, target_id, target_type, description, created_at) 
                        VALUES ('$action', '$workshop_id', 'workshop', '$message', CURRENT_TIMESTAMP)";
            @mysqli_query($conn, $logQuery); // Use @ to suppress errors if table doesn't exist
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'workshop_id' => $workshop_id,
                'action' => $action
            ]);
        } else {
            throw new Exception('Failed to update workshop status: ' . mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method Not Allowed. Use POST method.'
    ]);
}

mysqli_close($conn);
?>
