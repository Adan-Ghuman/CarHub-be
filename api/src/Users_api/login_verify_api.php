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
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    // Extract user attributes
    $email = isset($data['email']) ? mysqli_real_escape_string($conn, $data['email']) : null;
    $phoneNumber = isset($data['phoneNumber']) ? mysqli_real_escape_string($conn, $data['phoneNumber']) : null;
    $password = isset($data['password']) ? $data['password'] : null;

    // Validate required fields
    if (($email === null && $phoneNumber === null) || $password === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email or PhoneNumber and Password must be provided']);
        exit;
    }

    // Build query based on provided credentials
    if ($email !== null && $phoneNumber !== null) {
        $whereClause = "(Email = '$email' OR PhoneNumber = '$phoneNumber')";
    } elseif ($email !== null) {
        $whereClause = "Email = '$email'";
    } else {
        $whereClause = "PhoneNumber = '$phoneNumber'";
    }

    $verificationQuery = "SELECT * FROM users WHERE $whereClause";
    $verificationResult = mysqli_query($conn, $verificationQuery);

    if ($verificationResult && mysqli_num_rows($verificationResult) > 0) {
        $userDetails = mysqli_fetch_assoc($verificationResult);
        
        // Verify password
        if ($password === $userDetails['userPassword']) {
            // Remove password from response
            unset($userDetails['userPassword']);
            
            // Map the response to match what Login.js expects
            $response = array(
                'success' => true, 
                'message' => 'Login successful',
                'userDetails' => array(
                    'UserID' => $userDetails['id'], // Map 'id' to 'UserID'
                    'Name' => $userDetails['Name'],
                    'Email' => $userDetails['Email'],
                    'PhoneNumber' => $userDetails['PhoneNumber'],
                    'Location' => $userDetails['Location']
                )
            );
        } else {
            $response = array('success' => false, 'message' => 'Invalid password');
        }
    } else {
        $response = array('success' => false, 'message' => 'User not found');
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}

// Send the JSON response
echo json_encode($response);

// Close the database connection
mysqli_close($conn);
?>