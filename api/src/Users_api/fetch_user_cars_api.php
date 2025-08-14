<?php

include "../config/config.php";

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
    if ($requestData === null || !isset($requestData['userID'])) {
        
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Invalid JSON data or missing userID']);
        exit;
    }

    // Get the user ID to fetch cars
    $userID = $requestData['userID'];
    
    // Add some logging for debugging
    error_log("Fetching cars for userID: " . $userID);

    $fetchCarsQuery = "SELECT * FROM cars WHERE SellerID = '$userID'";
    $result = mysqli_query($conn, $fetchCarsQuery);

    if ($result) {
        $carsData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Log the number of cars found
        error_log("Found " . count($carsData) . " cars for user " . $userID);

        // Handle empty results gracefully
        if (empty($carsData)) {
            echo json_encode([]);
            mysqli_close($conn);
            exit;
        }

        // Fetch and append image URLs for each car
        foreach ($carsData as &$car) {
            $carID = $car['CarID'];
            $fetchImagesQuery = "SELECT ImageUrl FROM carimages WHERE CarID = '$carID'";
            $imageResult = mysqli_query($conn, $fetchImagesQuery);
            
            if ($imageResult) {
                $imageUrls = mysqli_fetch_all($imageResult, MYSQLI_ASSOC);
                $car['ImageUrls'] = $imageUrls;
            } else {
                $car['ImageUrls'] = [];
            }
        }

        // Send the data as JSON
        echo json_encode($carsData);
    } else {
        // Database error
        error_log("Database error: " . mysqli_error($conn));
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to fetch cars: ' . mysqli_error($conn)
        ]);
    }
} else {
    // Handle other HTTP methods if needed
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method Not Allowed']);
}

// Close the database connection
mysqli_close($conn);
?>
