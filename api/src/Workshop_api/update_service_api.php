<?php
include "../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Required fields
    $serviceId = isset($data['service_id']) ? (int)$data['service_id'] : 0;
    $workshopId = isset($data['workshop_id']) ? mysqli_real_escape_string($conn, $data['workshop_id']) : '';
    $serviceName = isset($data['service_name']) ? mysqli_real_escape_string($conn, trim($data['service_name'])) : '';
    $description = isset($data['description']) ? mysqli_real_escape_string($conn, trim($data['description'])) : '';
    $price = isset($data['price']) ? (float)$data['price'] : 0;
    $duration = isset($data['duration']) ? mysqli_real_escape_string($conn, trim($data['duration'])) : '60';
    $serviceCategory = isset($data['service_category']) ? mysqli_real_escape_string($conn, trim($data['service_category'])) : 'General';

    // Validation
    if ($serviceId <= 0 || empty($workshopId) || empty($serviceName) || $price <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: service_id, workshop_id, service_name, and price are required'
        ]);
        exit;
    }

    try {
        // Check if service exists and belongs to the workshop
        $checkQuery = "SELECT id FROM workshop_services WHERE id = ? AND workshop_id = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "is", $serviceId, $workshopId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Service not found or you do not have permission to update it'
            ]);
            exit;
        }

        // Update the service
        $updateQuery = "UPDATE workshop_services SET 
                        service_name = ?, 
                        description = ?, 
                        price = ?, 
                        duration = ?, 
                        service_category = ?,
                        updated_at = NOW()
                        WHERE id = ? AND workshop_id = ?";
        
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "ssdssis", 
            $serviceName, $description, $price, $duration, $serviceCategory, $serviceId, $workshopId);
        
        if (mysqli_stmt_execute($updateStmt)) {
            if (mysqli_stmt_affected_rows($updateStmt) > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Service updated successfully',
                    'service_id' => $serviceId
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No changes were made to the service'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . mysqli_error($conn)
            ]);
        }

        mysqli_stmt_close($updateStmt);
        mysqli_stmt_close($checkStmt);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only POST requests are accepted.'
    ]);
}

mysqli_close($conn);
?>
