<?php
include "../config/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, User-Agent, Accept, Cache-Control, Pragma');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate required fields (including location for your existing table structure)
    $required_fields = ['name', 'email', 'password', 'phone', 'role'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => "Field '$field' is required"
            ]);
            exit;
        }
    }

    $name = mysqli_real_escape_string($conn, trim($data['name']));
    $email = mysqli_real_escape_string($conn, trim($data['email']));
    $password = trim($data['password']);
    $phone = mysqli_real_escape_string($conn, trim($data['phone']));
    $location = mysqli_real_escape_string($conn, trim($data['location'] ?? 'Not specified'));
    $role = mysqli_real_escape_string($conn, trim($data['role']));

    // Validate role
    if (!in_array($role, ['customer', 'workshop_owner'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Invalid role. Must be either customer or workshop_owner'
        ]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Invalid email format'
        ]);
        exit;
    }

    // Validate password strength
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Password must be at least 6 characters long'
        ]);
        exit;
    }

    try {
        // Check if email already exists (using Email column name)
        $checkQuery = "SELECT id FROM users WHERE Email = '$email' LIMIT 1";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Email already exists'
            ]);
            exit;
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user (using your existing column names)
        $insertQuery = "INSERT INTO users (Name, Email, userPassword, PhoneNumber, Location, role, is_verified) 
                       VALUES ('$name', '$email', '$hashedPassword', '$phone', '$location', '$role', FALSE)";
        
        if (mysqli_query($conn, $insertQuery)) {
            $userId = mysqli_insert_id($conn);
            
            // Generate session token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
            
            // Store user session
            $sessionQuery = "INSERT INTO user_sessions (user_id, user_type, token, expires_at) 
                           VALUES ('$userId', 'user', '$token', '$expires')";
            mysqli_query($conn, $sessionQuery);
            
            echo json_encode([
                'success' => true,
                'message' => ucfirst($role) . ' registered successfully!',
                'role' => $role,
                'token' => $token,
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'location' => $location,
                    'role' => $role,
                    'is_verified' => false
                ]
            ]);
            
        } else {
            throw new Exception('Failed to create user: ' . mysqli_error($conn));
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Registration failed: ' . $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method Not Allowed'
    ]);
}

mysqli_close($conn);
?>
