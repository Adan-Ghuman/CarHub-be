<?php
// Simple script to create cars table on Railway
$host = "interchange.proxy.rlwy.net:44546";
$username = "root";
$password = "FzXNJlQnXBSgtCZhGqzWjABfhCjgAZZe";
$database = "railway";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to Railway database successfully!\n";

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

if ($conn->query($createCarsTable) === TRUE) {
    echo "✓ Cars table created successfully!\n";
} else {
    echo "✗ Error creating cars table: " . $conn->error . "\n";
}

// Create makers table
$createMakersTable = "
CREATE TABLE IF NOT EXISTS tbl_makers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    maker_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createMakersTable) === TRUE) {
    echo "✓ Makers table created successfully!\n";
} else {
    echo "✗ Error creating makers table: " . $conn->error . "\n";
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

if ($conn->query($createModelsTable) === TRUE) {
    echo "✓ Models table created successfully!\n";
} else {
    echo "✗ Error creating models table: " . $conn->error . "\n";
}

// Insert sample makers
$sampleMakers = ['Toyota', 'Honda', 'Suzuki', 'Hyundai', 'Kia', 'Nissan', 'BMW', 'Mercedes-Benz', 'Audi', 'Ford'];

foreach ($sampleMakers as $maker) {
    $insertMaker = "INSERT IGNORE INTO tbl_makers (maker_name) VALUES ('$maker')";
    if ($conn->query($insertMaker) === TRUE) {
        echo "✓ Added maker: $maker\n";
    }
}

// Insert sample models for Toyota (ID = 1)
$sampleModels = [
    [1, 'Corolla'], [1, 'Camry'], [1, 'Prius'], [1, 'Yaris'],
    [2, 'Civic'], [2, 'Accord'], [2, 'City'], [2, 'CR-V'],
    [3, 'Alto'], [3, 'Cultus'], [3, 'Swift'], [3, 'Vitara']
];

foreach ($sampleModels as $model) {
    $insertModel = "INSERT IGNORE INTO tbl_models (maker_id, model_name) VALUES ({$model[0]}, '{$model[1]}')";
    if ($conn->query($insertModel) === TRUE) {
        echo "✓ Added model: {$model[1]}\n";
    }
}

// Check tables
echo "\nVerifying tables:\n";
$tables = ['cars', 'tbl_makers', 'tbl_models'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        $count = $conn->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc()['cnt'];
        echo "✓ Table $table exists with $count records\n";
    } else {
        echo "✗ Table $table does not exist\n";
    }
}

$conn->close();
echo "\nSetup complete!\n";
?>
