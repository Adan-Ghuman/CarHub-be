<?php
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = file_get_contents('php://input');
    
    $requestData = json_decode($jsonInput, true);
    if ($requestData === null || !isset($requestData['carID']) || !isset($requestData['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data or missing required parameters']);
        exit;
    }

    $carIDToUpdate = $requestData['carID'];
    $newStatus = $requestData['status'];

    $updateStatusQuery = "UPDATE cars SET carStatus='$newStatus' WHERE CarID='$carIDToUpdate'";
    mysqli_query($conn, $updateStatusQuery);

    echo 'Car status updated successfully!';
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>
