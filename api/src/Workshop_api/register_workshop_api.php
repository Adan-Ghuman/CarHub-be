<?php
include "../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate JSON data
    if ($data === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
        exit;
    }

    // Validate required fields
    $requiredFields = ['name', 'owner_name', 'email', 'phone', 'address', 'city'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing field: $field"]);
            exit;
        }
    }

    // Sanitize input
    $name = mysqli_real_escape_string($conn, trim($data['name']));
    $owner_name = mysqli_real_escape_string($conn, trim($data['owner_name']));
    $email = mysqli_real_escape_string($conn, strtolower(trim($data['email'])));
    $phone = mysqli_real_escape_string($conn, trim($data['phone']));
    $address = mysqli_real_escape_string($conn, trim($data['address']));
    $city = mysqli_real_escape_string($conn, trim($data['city']));
    $description = mysqli_real_escape_string($conn, trim($data['description'] ?? ''));
    $user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;

    // Check if workshop email already exists
    $checkQuery = "SELECT id FROM workshops WHERE email = '$email'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Workshop with this email already exists']);
        exit;
    }

    try {
        // Build the insert query with optional user_id
        if ($user_id !== null) {
            $insertQuery = "INSERT INTO workshops (name, owner_name, email, phone, address, city, description, user_id, status) 
                           VALUES ('$name', '$owner_name', '$email', '$phone', '$address', '$city', '$description', '$user_id', 'pending')";
        } else {
            $insertQuery = "INSERT INTO workshops (name, owner_name, email, phone, address, city, description, status) 
                           VALUES ('$name', '$owner_name', '$email', '$phone', '$address', '$city', '$description', 'pending')";
        }
        
        if (mysqli_query($conn, $insertQuery)) {
            $workshopId = mysqli_insert_id($conn);
            echo json_encode([
                'success' => true,
                'message' => 'Workshop registered successfully! Pending approval.',
                'workshopId' => $workshopId,
                'user_id' => $user_id
            ]);
        } else {
            throw new Exception('Failed to register workshop: ' . mysqli_error($conn));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
}

mysqli_close($conn);
?>
