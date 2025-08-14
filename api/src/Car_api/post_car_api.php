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
    
    // Log the raw input for debugging
    error_log("Raw JSON Input: " . $jsonInput);
    
    // Validate JSON data
    $requestData = json_decode($jsonInput, true);
    if ($requestData === null) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON data',
            'json_error' => json_last_error_msg()
        ]);
        exit;
    }

    // Log parsed data for debugging
    error_log("Parsed Request Data: " . print_r($requestData, true));

    // Validate required fields
    $requiredFields = ['title', 'location', 'price', 'sellerID'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($requestData[$field]) || empty(trim($requestData[$field]))) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => "Missing required fields: " . implode(', ', $missingFields),
            'received_data' => array_keys($requestData)
        ]);
        exit;
    }

    // Sanitize input data with fallbacks
    $makerID = isset($requestData['makerID']) ? (int)$requestData['makerID'] : 0;
    $modelID = isset($requestData['modelID']) ? (int)$requestData['modelID'] : 0;
    $variant = isset($requestData['variant']) ? mysqli_real_escape_string($conn, $requestData['variant']) : '';
    $registrationYear = isset($requestData['registrationYear']) ? (int)$requestData['registrationYear'] : date('Y');
    $price = (float)$requestData['price'];
    $mileage = isset($requestData['mileage']) ? (int)$requestData['mileage'] : 0;
    $fuelType = isset($requestData['fuelType']) ? mysqli_real_escape_string($conn, $requestData['fuelType']) : 'Petrol';
    $transmission = isset($requestData['transmission']) ? mysqli_real_escape_string($conn, $requestData['transmission']) : 'Manual';
    $carCondition = isset($requestData['carCondition']) ? mysqli_real_escape_string($conn, $requestData['carCondition']) : 'Used';
    $description = isset($requestData['description']) ? mysqli_real_escape_string($conn, $requestData['description']) : '';
    $sellerID = (int)$requestData['sellerID'];
    $location = mysqli_real_escape_string($conn, $requestData['location']);
    $carStatus = isset($requestData['carStatus']) ? mysqli_real_escape_string($conn, $requestData['carStatus']) : 'active';
    $title = mysqli_real_escape_string($conn, $requestData['title']);

    // If makerID and modelID are 0, get maker and model names instead
    $makerName = isset($requestData['makerName']) ? mysqli_real_escape_string($conn, $requestData['makerName']) : '';
    $modelName = isset($requestData['modelName']) ? mysqli_real_escape_string($conn, $requestData['modelName']) : '';

    // Log sanitized data
    error_log("Sanitized data: makerID=$makerID, modelID=$modelID, makerName=$makerName, modelName=$modelName");

    // Determine which fields to use for the insert
    if ($makerID > 0 && $modelID > 0) {
        // Use IDs
        $insertCarQuery = "INSERT INTO cars (MakerID, ModelID, Variant, RegistrationYear, Price, Mileage, FuelType, Transmission, carCondition, Description, SellerID, Location, carStatus, title) 
                           VALUES ('$makerID', '$modelID', '$variant', '$registrationYear', '$price', '$mileage', '$fuelType', '$transmission', '$carCondition', '$description', '$sellerID', '$location', '$carStatus', '$title')";
    } else {
        // Use names (check if your table has MakerName/ModelName columns)
        $insertCarQuery = "INSERT INTO cars (MakerName, ModelName, Variant, RegistrationYear, Price, Mileage, FuelType, Transmission, carCondition, Description, SellerID, Location, carStatus, title) 
                           VALUES ('$makerName', '$modelName', '$variant', '$registrationYear', '$price', '$mileage', '$fuelType', '$transmission', '$carCondition', '$description', '$sellerID', '$location', '$carStatus', '$title')";
    }
    
    // Execute the insert query
    error_log("Executing query: " . $insertCarQuery);
    $result = mysqli_query($conn, $insertCarQuery);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to insert car data: ' . mysqli_error($conn),
            'query' => $insertCarQuery
        ]);
        exit;
    }

    // Get the inserted CarID
    $carID = mysqli_insert_id($conn);
    error_log("Inserted car with ID: " . $carID);

    // Handle images
    if (isset($requestData['images']) && is_array($requestData['images'])) {
        $imageCount = 0;
        foreach ($requestData['images'] as $imageUrl) {
            if (!empty($imageUrl)) {
                $cleanImageUrl = mysqli_real_escape_string($conn, $imageUrl);
                $insertImageQuery = "INSERT INTO carimages (CarID, ImageUrl) VALUES ('$carID', '$cleanImageUrl')";
                if (mysqli_query($conn, $insertImageQuery)) {
                    $imageCount++;
                } else {
                    error_log("Failed to insert image: " . mysqli_error($conn));
                }
            }
        }
        error_log("Inserted $imageCount images");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Car information and images uploaded successfully!',
        'carID' => $carID,
        'debug_info' => [
            'received_fields' => array_keys($requestData),
            'makerID' => $makerID,
            'modelID' => $modelID,
            'sellerID' => $sellerID
        ]
    ]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

mysqli_close($conn);
?>
