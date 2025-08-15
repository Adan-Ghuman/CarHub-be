<?php
include __DIR__ . "/../../config/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, User-Agent, Accept, Cache-Control, Pragma');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Array to store results
    $results = [];

    // Admin table
    $adminTable = "
    CREATE TABLE IF NOT EXISTS admin (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $adminTable)) {
        $results[] = "Admin table created successfully";
    } else {
        $results[] = "Error creating admin table: " . mysqli_error($conn);
    }

    // Users table
    $usersTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        Name VARCHAR(100) NOT NULL,
        Email VARCHAR(100) UNIQUE NOT NULL,
        PhoneNumber VARCHAR(20),
        userPassword VARCHAR(255) NOT NULL,
        Location VARCHAR(255),
        role ENUM('customer', 'workshop_owner') DEFAULT 'customer',
        is_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $usersTable)) {
        $results[] = "Users table created successfully";
    } else {
        $results[] = "Error creating users table: " . mysqli_error($conn);
    }

    // User sessions table
    $userSessionsTable = "
    CREATE TABLE IF NOT EXISTS user_sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        user_type ENUM('admin', 'user') NOT NULL,
        token VARCHAR(255) UNIQUE NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $userSessionsTable)) {
        $results[] = "User sessions table created successfully";
    } else {
        $results[] = "Error creating user sessions table: " . mysqli_error($conn);
    }

    // Admin sessions table
    $adminSessionsTable = "
    CREATE TABLE IF NOT EXISTS admin_sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        admin_id INT,
        token VARCHAR(255) UNIQUE NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $adminSessionsTable)) {
        $results[] = "Admin sessions table created successfully";
    } else {
        $results[] = "Error creating admin sessions table: " . mysqli_error($conn);
    }

    // Insert default admin
    $defaultAdmin = "
    INSERT IGNORE INTO admin (username, email, password, full_name) 
    VALUES ('admin', 'admin@carhubpk.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator')";

    if (mysqli_query($conn, $defaultAdmin)) {
        $results[] = "Default admin created successfully";
    } else {
        $results[] = "Error creating default admin: " . mysqli_error($conn);
    }

    // Cars table
    $carsTable = "
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

    if (mysqli_query($conn, $carsTable)) {
        $results[] = "Cars table created successfully";
    } else {
        $results[] = "Error creating cars table: " . mysqli_error($conn);
    }

    // Makers table
    $makersTable = "
    CREATE TABLE IF NOT EXISTS tbl_makers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        maker_name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $makersTable)) {
        $results[] = "Makers table created successfully";
    } else {
        $results[] = "Error creating makers table: " . mysqli_error($conn);
    }

    // Models table
    $modelsTable = "
    CREATE TABLE IF NOT EXISTS tbl_models (
        id INT PRIMARY KEY AUTO_INCREMENT,
        maker_id INT NOT NULL,
        model_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (maker_id) REFERENCES tbl_makers(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $modelsTable)) {
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

    // Workshops table
    $workshopsTable = "
    CREATE TABLE IF NOT EXISTS workshops (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        name VARCHAR(255) NOT NULL,
        owner_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        description TEXT,
        status ENUM('pending', 'active', 'inactive', 'rejected') DEFAULT 'pending',
        is_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $workshopsTable)) {
        $results[] = "Workshops table created successfully";
    } else {
        $results[] = "Error creating workshops table: " . mysqli_error($conn);
    }

    // Workshop services table
    $workshopServicesTable = "
    CREATE TABLE IF NOT EXISTS workshop_services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        workshop_id INT NOT NULL,
        service_name VARCHAR(255) NOT NULL,
        service_category VARCHAR(100) DEFAULT 'General',
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        estimated_time VARCHAR(50) DEFAULT '60',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $workshopServicesTable)) {
        $results[] = "Workshop services table created successfully";
    } else {
        $results[] = "Error creating workshop services table: " . mysqli_error($conn);
    }

    // Workshop reviews table
    $workshopReviewsTable = "
    CREATE TABLE IF NOT EXISTS workshop_reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        workshop_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT,
        workshop_response TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $workshopReviewsTable)) {
        $results[] = "Workshop reviews table created successfully";
    } else {
        $results[] = "Error creating workshop reviews table: " . mysqli_error($conn);
    }

    // Workshop bookings table (note the name matches what the API expects)
    $workshopBookingsTable = "
    CREATE TABLE IF NOT EXISTS workshop_bookings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        workshop_id INT NOT NULL,
        service_id INT NOT NULL,
        user_id INT NOT NULL,
        booking_date DATE NOT NULL,
        booking_time TIME NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_email VARCHAR(255),
        status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
        total_price DECIMAL(10,2) NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES workshop_services(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $workshopBookingsTable)) {
        $results[] = "Workshop bookings table created successfully";
    } else {
        $results[] = "Error creating workshop bookings table: " . mysqli_error($conn);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Database setup completed',
        'results' => $results
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database setup failed: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
