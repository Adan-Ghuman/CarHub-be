
<?php

include "../config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, User-Agent, Accept, Cache-Control, Pragma');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON data from the mobile app
    $data = json_decode(file_get_contents("php://input"));

    // Extract user attributes
    $name = $data->name;
    $email = $data->email;
    $phoneNumber = $data->phoneNumber;
    $password = $data->password;
    $location = $data->location;


// Insert user data into the 'users' table
$insertUserQuery = "INSERT INTO users (Name, Email, PhoneNumber, userPassword, Location) 
                    VALUES ('$name', '$email', '$phoneNumber', '$password', '$location')";
$result = mysqli_query($conn, $insertUserQuery);

// Check if the query was successful
if ($result) {
    $response = array('success' => true, 'message' => 'User added successfully');
} else {
    $response = array('success' => false, 'message' => 'Failed to add user');
}

// Send the JSON response
echo json_encode($response);

// Close the database connection
mysqli_close($conn);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
