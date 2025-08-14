<?php

include "../config/config.php";

// Add CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Modified query to join with makers and models tables to get actual names
$fetchCarsQuery = "
    SELECT 
        c.*,
        m.maker_name as MakerName,
        mo.model_name as ModelName
    FROM cars c
    LEFT JOIN tbl_makers m ON c.MakerID = m.id
    LEFT JOIN tbl_models mo ON c.ModelID = mo.id
    WHERE c.carStatus IN ('active', 'Active')
";
$result = mysqli_query($conn, $fetchCarsQuery);

if ($result) {
    $carsData = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Check if any cars were found
    if (empty($carsData)) {
        // Return empty array with success status when no cars found
        echo json_encode([]);
        mysqli_close($conn);
        exit;
    }

    // Fetch and append image URLs for each active car
    foreach ($carsData as &$car) {
        $carID = $car['CarID'];
        $fetchImagesQuery = "SELECT ImageUrl FROM carimages WHERE CarID = '$carID'";
        $imageResult = mysqli_query($conn, $fetchImagesQuery);
        
        if ($imageResult) {
            $imageUrls = mysqli_fetch_all($imageResult, MYSQLI_ASSOC);
            $car['ImageUrls'] = $imageUrls;
        } else {
            // If no images found, set empty array
            $car['ImageUrls'] = [];
        }
    }

    // Send the data as JSON
    echo json_encode($carsData);
} else {
    // Database query failed
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch active cars: ' . mysqli_error($conn)
    ]);
}

// Close the database connection
mysqli_close($conn);
?>
