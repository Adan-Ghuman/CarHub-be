<?php

include __DIR__ . "/../../config/config.php";

// Add CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Query to get all active cars with maker and model names
    $fetchCarsQuery = "
        SELECT 
            c.*,
            m.maker_name as MakerName,
            mo.model_name as ModelName
        FROM cars c
        LEFT JOIN tbl_makers m ON c.MakerID = m.id
        LEFT JOIN tbl_models mo ON c.ModelID = mo.id
        WHERE c.carStatus IN ('active', 'Active')
        ORDER BY c.created_at DESC
    ";
    
    $result = mysqli_query($conn, $fetchCarsQuery);

    if ($result) {
        $carsData = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Fetch and append image URLs for each car
        foreach ($carsData as &$car) {
            $carID = $car['CarID'];
            $fetchImagesQuery = "SELECT ImageUrl FROM carimages WHERE CarID = '$carID'";
            $imageResult = mysqli_query($conn, $fetchImagesQuery);
            
            if ($imageResult) {
                $imageUrls = mysqli_fetch_all($imageResult, MYSQLI_ASSOC);
                $car['ImageUrls'] = array_column($imageUrls, 'ImageUrl');
            } else {
                $car['ImageUrls'] = [];
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $carsData,
            'total_count' => count($carsData)
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database query failed: ' . mysqli_error($conn)
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
