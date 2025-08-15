<?php
include __DIR__ . "/config/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, User-Agent, Accept, Cache-Control, Pragma');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $results = [];

    // Create cars table
    $createCarsTable = "
    CREATE TABLE IF NOT EXISTS cars (
        CarID INT PRIMARY KEY AUTO_INCREMENT,
        MakerID INT DEFAULT 0,
        ModelID INT DEFAULT 0,
        MakerName VARCHAR(100),
        ModelName VARCHAR(100),
        Variant VARCHAR(100),
        RegistrationYear INT DEFAULT 2020,
        Price DECIMAL(12,2) NOT NULL,
        Mileage INT DEFAULT 0,
        FuelType VARCHAR(50) DEFAULT 'Petrol',
        Transmission VARCHAR(50) DEFAULT 'Manual',
        carCondition VARCHAR(50) DEFAULT 'Used',
        Description TEXT,
        SellerID INT NOT NULL,
        Location VARCHAR(255),
        carStatus VARCHAR(50) DEFAULT 'active',
        title VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (SellerID) REFERENCES users(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $createCarsTable)) {
        $results[] = "Cars table created successfully";
    } else {
        $results[] = "Error creating cars table: " . mysqli_error($conn);
    }

    // Create makers table
    $createMakersTable = "
    CREATE TABLE IF NOT EXISTS tbl_makers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        maker_name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $createMakersTable)) {
        $results[] = "Makers table created successfully";
    } else {
        $results[] = "Error creating makers table: " . mysqli_error($conn);
    }

    // Create models table
    $createModelsTable = "
    CREATE TABLE IF NOT EXISTS tbl_models (
        id INT PRIMARY KEY AUTO_INCREMENT,
        maker_id INT NOT NULL,
        model_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (maker_id) REFERENCES tbl_makers(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $createModelsTable)) {
        $results[] = "Models table created successfully";
    } else {
        $results[] = "Error creating models table: " . mysqli_error($conn);
    }

    // Insert sample makers
    $sampleMakers = ['Toyota', 'Honda', 'Suzuki', 'Hyundai', 'Kia', 'Nissan', 'BMW', 'Mercedes-Benz', 'Audi', 'Ford'];

    foreach ($sampleMakers as $maker) {
        $insertMaker = "INSERT IGNORE INTO tbl_makers (maker_name) VALUES (?)";
        $stmt = mysqli_prepare($conn, $insertMaker);
        mysqli_stmt_bind_param($stmt, "s", $maker);
        if (mysqli_stmt_execute($stmt)) {
            $results[] = "Added maker: $maker";
        }
    }

    // Insert sample models
    $sampleModels = [
        [1, 'Corolla'], [1, 'Camry'], [1, 'Prius'], [1, 'Yaris'],
        [2, 'Civic'], [2, 'Accord'], [2, 'City'], [2, 'CR-V'],
        [3, 'Alto'], [3, 'Cultus'], [3, 'Swift'], [3, 'Vitara']
    ];

    foreach ($sampleModels as $model) {
        $insertModel = "INSERT IGNORE INTO tbl_models (maker_id, model_name) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insertModel);
        mysqli_stmt_bind_param($stmt, "is", $model[0], $model[1]);
        if (mysqli_stmt_execute($stmt)) {
            $results[] = "Added model: {$model[1]}";
        }
    }

    // Verify tables
    $verification = [];
    $tables = ['cars', 'tbl_makers', 'tbl_models'];
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if ($result && mysqli_num_rows($result) > 0) {
            $countResult = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM $table");
            $count = mysqli_fetch_assoc($countResult)['cnt'];
            $verification[$table] = "exists with $count records";
        } else {
            $verification[$table] = "does not exist";
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cars tables setup completed',
        'results' => $results,
        'verification' => $verification
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
