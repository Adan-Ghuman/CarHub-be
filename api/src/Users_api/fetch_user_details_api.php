<?php

include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jsonInput = file_get_contents('php://input');

    // Validate JSON data
    $requestData = json_decode($jsonInput, true);
    if ($requestData === null || (!isset($requestData['userID']) && !isset($requestData['adminID']))) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data or missing userID/adminID']);
        exit;
    }

    // Check if this is an admin request
    if (isset($requestData['adminID'])) {
        $adminID = $requestData['adminID'];
        
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
        exit;
    }

    // Get the user ID to fetch user details
    $userID = $requestData['userID'];

    try {
        // Updated query to get all user fields including creation date
        $fetchUserQuery = "SELECT 
                            id as UserID, 
                            Name, 
                            Email, 
                            PhoneNumber, 
                            Location, 
                            created_at
                          FROM users 
                          WHERE id = '$userID'";
        
        $result = mysqli_query($conn, $fetchUserQuery);

        if ($result && mysqli_num_rows($result) > 0) {
            $userData = mysqli_fetch_assoc($result);
            
            // Remove sensitive information and format response
            echo json_encode([
                'success' => true,
                'UserID' => $userData['UserID'],
                'id' => $userData['UserID'], // For compatibility
                'Name' => $userData['Name'],
                'Email' => $userData['Email'],
                'PhoneNumber' => $userData['PhoneNumber'],
                'Location' => $userData['Location'],
                'created_at' => $userData['created_at']
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false, 
                'error' => 'User not found'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    // Handle other HTTP methods if needed
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method Not Allowed']);
}

// Close the database connection
mysqli_close($conn);
