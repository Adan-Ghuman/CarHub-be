<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

include __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Check if locations table exists, if not create sample locations
    $checkTableQuery = "SHOW TABLES LIKE 'locations'";
    $tableExists = mysqli_query($conn, $checkTableQuery);
    
    if (mysqli_num_rows($tableExists) == 0) {
        // Create locations table if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS locations (
            id INT PRIMARY KEY AUTO_INCREMENT,
            city VARCHAR(100) NOT NULL,
            province VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $createTableQuery);
        
        // Insert sample locations
        $sampleLocations = [
            'Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad',
            'Multan', 'Peshawar', 'Quetta', 'Gujranwala', 'Sialkot'
        ];
        
        foreach ($sampleLocations as $city) {
            $insertQuery = "INSERT IGNORE INTO locations (city) VALUES ('$city')";
            mysqli_query($conn, $insertQuery);
        }
    }

    // Fetch cities data
    $query = "SELECT * FROM locations ORDER BY city ASC";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $citiesData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($citiesData);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

// Close the database connection
mysqli_close($conn);
?>
