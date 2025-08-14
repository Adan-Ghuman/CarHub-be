<?php
include __DIR__ . "/../../config/config.php";



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $jsonInput = file_get_contents('php://input');
    
    // Validate JSON data
    $requestData = json_decode($jsonInput, true);
    if ($requestData === null || !isset($requestData['carID'])) {
        // Invalid JSON data or missing carID
        http_response_code(400); 
        echo json_encode(['error' => 'Invalid JSON data or missing carID']);
        exit;
    }

    
    $carIDToDelete = $requestData['carID'];

    
    $deleteCarQuery = "DELETE FROM cars WHERE CarID='$carIDToDelete'";
    mysqli_query($conn, $deleteCarQuery);

   
    $deleteImageQuery = "DELETE FROM carimages WHERE CarID='$carIDToDelete'";
    mysqli_query($conn, $deleteImageQuery);

    
    echo 'Car information and images deleted successfully!';
} else {
    // Handle other HTTP methods if needed
    http_response_code(405); // Method Not Allowed reuired data
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>
