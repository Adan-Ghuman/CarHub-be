<?php
include "../config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Email and password are required'
        ]);
        exit;
    }

    $email = mysqli_real_escape_string($conn, trim($data['email']));
    $password = trim($data['password']);
    $loginType = isset($data['login_type']) ? $data['login_type'] : 'user';

    try {
        if ($loginType === 'admin') {
            // Admin login
            $adminQuery = "SELECT * FROM admin WHERE email = '$email' OR username = '$email' LIMIT 1";
            $adminResult = mysqli_query($conn, $adminQuery);
            
            if ($adminResult && mysqli_num_rows($adminResult) > 0) {
                $admin = mysqli_fetch_assoc($adminResult);
                
                if (password_verify($password, $admin['password'])) {
                    // Generate admin session token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    // Store admin session
                    $sessionQuery = "INSERT INTO admin_sessions (admin_id, token, expires_at) 
                                   VALUES ('{$admin['id']}', '$token', '$expires')";
                    mysqli_query($conn, $sessionQuery);
                    
                    echo json_encode([
                        'success' => true,
                        'role' => 'admin',
                        'token' => $token,
                        'user' => [
                            'id' => $admin['id'],
                            'email' => $admin['email'],
                            'username' => $admin['username'],
                            'full_name' => $admin['full_name'],
                            'role' => 'admin'
                        ]
                    ]);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid admin credentials']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Admin not found']);
                exit;
            }
        } else {
            // User login (customer/workshop_owner)
            $userQuery = "SELECT * FROM users WHERE Email = '$email' LIMIT 1";
            $userResult = mysqli_query($conn, $userQuery);
            
            if ($userResult && mysqli_num_rows($userResult) > 0) {
                $user = mysqli_fetch_assoc($userResult);
                
                if (password_verify($password, $user['userPassword'])) {
                    // Generate user session token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
                    
                    // Store user session
                    $sessionQuery = "INSERT INTO user_sessions (user_id, user_type, token, expires_at) 
                                   VALUES ('{$user['id']}', 'user', '$token', '$expires')";
                    mysqli_query($conn, $sessionQuery);
                    
                    echo json_encode([
                        'success' => true,
                        'role' => $user['role'] ?? 'customer',
                        'token' => $token,
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['Name'],
                            'email' => $user['Email'],
                            'phone' => $user['PhoneNumber'],
                            'location' => $user['Location'],
                            'role' => $user['role'] ?? 'customer',
                            'is_verified' => (bool)($user['is_verified'] ?? false)
                        ]
                    ]);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid password']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Login failed: ' . $e->getMessage()
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
