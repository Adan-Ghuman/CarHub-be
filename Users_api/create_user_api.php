
<?php

include "../config.php";

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
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
mysqli_close($conn);
?>
