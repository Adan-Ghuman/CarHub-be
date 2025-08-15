<?php
require_once __DIR__ . '/../api/config/config.php';

echo "Setting Up Complete CarHub Database\n";
echo "===================================\n\n";

// Array to track all table creation results
$results = [];

// SQL statements for all required tables
$sql_statements = [
    
    // Cars table - Main car listings
    "CREATE TABLE IF NOT EXISTS cars (
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
    )",
    
    // Makers table - Car manufacturers
    "CREATE TABLE IF NOT EXISTS tbl_makers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        maker_name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Models table - Car models
    "CREATE TABLE IF NOT EXISTS tbl_models (
        id INT PRIMARY KEY AUTO_INCREMENT,
        maker_id INT NOT NULL,
        model_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (maker_id) REFERENCES tbl_makers(id) ON DELETE CASCADE
    )",
    
    // Car images table
    "CREATE TABLE IF NOT EXISTS car_images (
        id INT PRIMARY KEY AUTO_INCREMENT,
        car_id INT NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        is_primary BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (car_id) REFERENCES cars(CarID) ON DELETE CASCADE
    )",
    
    // Workshops table (if not exists)
    "CREATE TABLE IF NOT EXISTS workshops (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        name VARCHAR(255) NOT NULL,
        owner_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        description TEXT,
        specialization TEXT,
        status ENUM('pending', 'active', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    // Services table
    "CREATE TABLE IF NOT EXISTS services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        workshop_id INT NOT NULL,
        service_name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        duration INT DEFAULT 60,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
    )",
    
    // Bookings table
    "CREATE TABLE IF NOT EXISTS bookings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        workshop_id INT NOT NULL,
        service_id INT NOT NULL,
        booking_date DATE NOT NULL,
        booking_time TIME NOT NULL,
        status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
        price DECIMAL(10,2) NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    )",
    
    // Reviews table
    "CREATE TABLE IF NOT EXISTS reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )",
    
    // Workshop operating hours table
    "CREATE TABLE IF NOT EXISTS workshop_operating_hours (
        id INT PRIMARY KEY AUTO_INCREMENT,
        workshop_id INT NOT NULL,
        day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
        open_time TIME,
        close_time TIME,
        is_closed BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
        UNIQUE KEY unique_workshop_day (workshop_id, day_of_week)
    )"
];

// Execute each SQL statement
foreach ($sql_statements as $index => $sql) {
    $table_name = '';
    if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $matches)) {
        $table_name = $matches[1];
    }
    
    echo "Creating table: $table_name\n";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Success - Table $table_name created/verified\n";
        $results[$table_name] = "✓ Created successfully";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
        $results[$table_name] = "✗ Error: " . $conn->error;
    }
    echo "\n";
}

// Insert sample makers and models data
echo "Adding sample car makers and models...\n";

$sample_makers = [
    ['Toyota'], ['Honda'], ['Suzuki'], ['Hyundai'], ['Kia'], 
    ['Nissan'], ['Mitsubishi'], ['BMW'], ['Mercedes-Benz'], ['Audi']
];

$maker_sql = "INSERT IGNORE INTO tbl_makers (maker_name) VALUES (?)";
$maker_stmt = $conn->prepare($maker_sql);

foreach ($sample_makers as $maker) {
    $maker_stmt->bind_param("s", $maker[0]);
    if ($maker_stmt->execute()) {
        echo "✓ Added maker: {$maker[0]}\n";
    }
}

// Add sample models for some makers
$sample_models = [
    [1, 'Corolla'], [1, 'Camry'], [1, 'Prius'], [1, 'Yaris'],
    [2, 'Civic'], [2, 'Accord'], [2, 'City'], [2, 'CR-V'],
    [3, 'Alto'], [3, 'Cultus'], [3, 'Swift'], [3, 'Vitara'],
    [4, 'Elantra'], [4, 'Sonata'], [4, 'Tucson'], [4, 'Santa Fe'],
    [5, 'Picanto'], [5, 'Rio'], [5, 'Cerato'], [5, 'Sportage']
];

$model_sql = "INSERT IGNORE INTO tbl_models (maker_id, model_name) VALUES (?, ?)";
$model_stmt = $conn->prepare($model_sql);

foreach ($sample_models as $model) {
    $model_stmt->bind_param("is", $model[0], $model[1]);
    if ($model_stmt->execute()) {
        echo "✓ Added model: {$model[1]}\n";
    }
}

echo "\n";
echo "Database setup complete!\n";
echo "========================\n";

// Final verification
echo "Verifying all tables were created:\n";
$verification_tables = ['cars', 'tbl_makers', 'tbl_models', 'car_images', 'workshops', 'services', 'bookings', 'reviews', 'workshop_operating_hours'];

foreach ($verification_tables as $table) {
    $check_result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check_result && $check_result->num_rows > 0) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $count_result->fetch_assoc()['count'];
        echo "✓ Table $table exists with $count records\n";
    } else {
        echo "✗ Table $table was not created\n";
    }
}

$conn->close();
?>
