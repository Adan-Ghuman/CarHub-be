<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

require_once '../config.php';

try {
    // First check if required tables exist
    $tables_check = $pdo->query("SHOW TABLES LIKE 'cars'");
    if ($tables_check->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Cars table does not exist',
            'data' => []
        ]);
        exit;
    }

    // Get all cars with owner information (simplified query to avoid missing tables)
    $query = "
        SELECT 
            c.CarID,
            c.title,
            c.Price,
            c.RegistrationYear,
            c.Mileage,
            c.FuelType,
            c.Transmission,
            c.carCondition,
            c.Description,
            c.SellerID,
            c.Location,
            c.carStatus,
            c.created_at,
            c.updated_at,
            u.Name as owner_name,
            u.Email as owner_email,
            u.PhoneNumber as owner_phone
        FROM cars c
        LEFT JOIN users u ON c.SellerID = u.id
        ORDER BY c.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formatted_cars = [];
    foreach ($cars as $car) {
        $formatted_cars[] = [
            'CarID' => (int)$car['CarID'],
            'title' => $car['title'] ?? 'Unknown Car',
            'price' => $car['Price'] ?? '0',
            'year' => $car['RegistrationYear'] ?? '2020',
            'mileage' => $car['Mileage'] ?? '0',
            'fuelType' => $car['FuelType'] ?? '',
            'transmission' => $car['Transmission'] ?? '',
            'carCondition' => $car['carCondition'] ?? '',
            'description' => $car['Description'] ?? '',
            'sellerId' => $car['SellerID'] ?? '',
            'location' => $car['Location'] ?? 'Unknown',
            'status' => $car['carStatus'] ?? 'active',
            'created_at' => $car['created_at'],
            'updated_at' => $car['updated_at'],
            'owner_name' => $car['owner_name'] ?? 'Unknown Owner',
            'owner_email' => $car['owner_email'] ?? '',
            'owner_phone' => $car['owner_phone'] ?? '',
            'favorites_count' => 0,
            'views_count' => 0
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted_cars,
        'total_count' => count($formatted_cars)
    ]);
    
} catch (Exception $e) {
    error_log("Get all cars error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch cars data: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
