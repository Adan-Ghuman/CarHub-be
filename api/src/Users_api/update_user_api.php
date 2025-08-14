<?php

include __DIR__ . "/../../config/config.php";

// Decode JSON data from the mobile app
$data = json_decode(file_get_contents("php://input"));

// Extract user attributes
$userID = $data->userID ?? null;
$email = $data->email ?? null;
$phoneNumber = $data->phoneNumber ?? null;
$password = $data->password ?? null;
$location = $data->location ?? null;


if ($userID === null && $email === null && $phoneNumber === null) {
    $response = array('success' => false, 'message' => 'User ID, Email, or PhoneNumber must be provided');
} else {
    
    $updateUserQuery = "UPDATE users SET ";

    // Add fields to update if they are not null
    if ($email !== null) {
        $updateUserQuery .= "Email = '$email', ";
    }

    if ($phoneNumber !== null) {
        $updateUserQuery .= "PhoneNumber = '$phoneNumber', ";
    }

    if ($password !== null) {
        $updateUserQuery .= "userPassword = '$password', ";
    }

    if ($location !== null) {
        $updateUserQuery .= "Location = '$location', ";
    }

    // Remove the trailing comma and space
    $updateUserQuery = rtrim($updateUserQuery, ", ");



        $updateUserQuery .= " WHERE UserID = '$userID'"; 

    // Execute the query
    $result = mysqli_query($conn, $updateUserQuery);

    // Check if the query was successful
    if ($result) {
        $response = array('success' => true, 'message' => 'User updated successfully');
    } else {
        $response = array('success' => false, 'message' => 'Failed to update user');
    }
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
mysqli_close($conn);
?>
