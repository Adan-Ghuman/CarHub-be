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
    
    // Required fields - updated to match frontend
    $workshopId = isset($data['workshop_id']) ? mysqli_real_escape_string($conn, $data['workshop_id']) : '';
    $serviceName = isset($data['service_name']) ? mysqli_real_escape_string($conn, trim($data['service_name'])) : '';
    $description = isset($data['description']) ? mysqli_real_escape_string($conn, trim($data['description'])) : '';
    $price = isset($data['price']) ? (float)$data['price'] : 0;
    $duration = isset($data['duration']) ? mysqli_real_escape_string($conn, trim($data['duration'])) : '60'; // Default 60 minutes
    $serviceCategory = isset($data['service_category']) ? mysqli_real_escape_string($conn, trim($data['service_category'])) : 'general';

    // Validation - simplified to match frontend requirements
    if (empty($workshopId) || empty($serviceName) || $price <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Workshop ID, service name, and price are required'
        ]);
        exit;
    }

    try {
        // Verify workshop exists and is active
        $workshopQuery = "SELECT id FROM workshops WHERE id = '$workshopId' AND status = 'active'";
        $workshopResult = mysqli_query($conn, $workshopQuery);

        if (!$workshopResult || mysqli_num_rows($workshopResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Workshop not found or inactive'
            ]);
            exit;
        }

        // Insert new service - updated field names
        $insertQuery = "INSERT INTO workshop_services 
                       (workshop_id, service_name, service_category, description, price, duration, is_active, created_at) 
                       VALUES 
                       ('$workshopId', '$serviceName', '$serviceCategory', '$description', '$price', '$duration', TRUE, NOW())";

        if (mysqli_query($conn, $insertQuery)) {
            $serviceId = mysqli_insert_id($conn);
            
            // Fetch the created service
            $fetchQuery = "SELECT * FROM workshop_services WHERE id = '$serviceId'";
            $fetchResult = mysqli_query($conn, $fetchQuery);
            $service = mysqli_fetch_assoc($fetchResult);

            echo json_encode([
                'success' => true,
                'message' => 'Service added successfully',
                'data' => $service
            ]);
        } else {
            throw new Exception('Failed to add service: ' . mysqli_error($conn));
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
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed. Use POST method.']);
}

mysqli_close($conn);
?>
