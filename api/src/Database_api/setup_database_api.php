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

    // Insert default admin users
    $defaultAdmins = [
        [
            'username' => 'admin',
            'email' => 'admin@carhubpk.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT), // Clear password: admin123
            'full_name' => 'System Administrator'
        ],
        [
            'username' => 'carhub_admin',
            'email' => 'carhubadmin@example.com',
            'password' => password_hash('CarHub2025!', PASSWORD_DEFAULT), // Clear password: CarHub2025!
            'full_name' => 'CarHub Admin'
        ]
    ];

    foreach ($defaultAdmins as $admin) {
        $adminInsertQuery = "INSERT IGNORE INTO admin (username, email, password, full_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($adminInsertQuery);
        if ($stmt) {
            $stmt->bind_param("ssss", $admin['username'], $admin['email'], $admin['password'], $admin['full_name']);
            if ($stmt->execute()) {
                $results[] = "Created admin user: " . $admin['username'] . " (email: " . $admin['email'] . ")";
            } else {
                $results[] = "Admin user " . $admin['username'] . " might already exist";
            }
            $stmt->close();
        }
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
        specialization VARCHAR(255) DEFAULT NULL,
        status ENUM('pending', 'active', 'inactive', 'rejected') DEFAULT 'pending',
        is_verified BOOLEAN DEFAULT FALSE,
        rating DECIMAL(3,2) DEFAULT 0.0,
        total_reviews INT DEFAULT 0,
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
        booking_id INT DEFAULT NULL,
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

    // Workshop operating hours table
    $operatingHoursTable = "
    CREATE TABLE IF NOT EXISTS workshop_operating_hours (
        id INT PRIMARY KEY AUTO_INCREMENT,
        workshop_id INT NOT NULL,
        day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
        opening_time TIME,
        closing_time TIME,
        is_closed BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
        UNIQUE KEY unique_workshop_day (workshop_id, day_of_week)
    )";

    if (mysqli_query($conn, $operatingHoursTable)) {
        $results[] = "Workshop operating hours table created successfully";
    } else {
        $results[] = "Error creating workshop operating hours table: " . mysqli_error($conn);
    }

    // Booking status logs table (for audit trail)
    $bookingStatusLogsTable = "
    CREATE TABLE IF NOT EXISTS booking_status_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT NOT NULL,
        old_status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL,
        new_status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        FOREIGN KEY (booking_id) REFERENCES workshop_bookings(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $bookingStatusLogsTable)) {
        $results[] = "Booking status logs table created successfully";
    } else {
        $results[] = "Error creating booking status logs table: " . mysqli_error($conn);
    }

    // Admin logs table (for admin actions)
    $adminLogsTable = "
    CREATE TABLE IF NOT EXISTS admin_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        action_type VARCHAR(100) NOT NULL,
        target_id INT,
        target_type VARCHAR(50),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $adminLogsTable)) {
        $results[] = "Admin logs table created successfully";
    } else {
        $results[] = "Error creating admin logs table: " . mysqli_error($conn);
    }

    // Reviews table (alternative name used by some APIs)
    $reviewsTable = "
    CREATE TABLE IF NOT EXISTS reviews (
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

    if (mysqli_query($conn, $reviewsTable)) {
        $results[] = "Reviews table (alternative) created successfully";
    } else {
        $results[] = "Error creating reviews table: " . mysqli_error($conn);
    }

    // Service bookings table (alternative name used by some APIs)
    $serviceBookingsTable = "
    CREATE TABLE IF NOT EXISTS service_bookings (
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

    if (mysqli_query($conn, $serviceBookingsTable)) {
        $results[] = "Service bookings table (alternative) created successfully";
    } else {
        $results[] = "Error creating service bookings table: " . mysqli_error($conn);
    }

    // Bookings table (alternative name used by some APIs) 
    $bookingsTable = "
    CREATE TABLE IF NOT EXISTS bookings (
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

    if (mysqli_query($conn, $bookingsTable)) {
        $results[] = "Bookings table (alternative) created successfully";
    } else {
        $results[] = "Error creating bookings table: " . mysqli_error($conn);
    }

    // Services table (alternative name used by some APIs)
    $servicesTable = "
    CREATE TABLE IF NOT EXISTS services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        workshop_id INT NOT NULL,
        service_name VARCHAR(255) NOT NULL,
        service_category VARCHAR(100) DEFAULT 'General',
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        estimated_time VARCHAR(50) DEFAULT '60',
        duration VARCHAR(50) DEFAULT '60',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $servicesTable)) {
        $results[] = "Services table (alternative) created successfully";
    } else {
        $results[] = "Error creating services table: " . mysqli_error($conn);
    }

    // Car images table for uploaded car photos
    $carImagesTable = "
    CREATE TABLE IF NOT EXISTS carimages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        CarID INT NOT NULL,
        ImageUrl TEXT NOT NULL,
        is_primary BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (CarID) REFERENCES cars(CarID) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $carImagesTable)) {
        $results[] = "Car images table created successfully";
    } else {
        $results[] = "Error creating car images table: " . mysqli_error($conn);
    }

    // Create indexes for better performance (skip if already exist)
    $indexQueries = [
        "CREATE INDEX idx_workshop_services_workshop_id ON workshop_services(workshop_id)",
        "CREATE INDEX idx_workshop_services_active ON workshop_services(is_active)",
        "CREATE INDEX idx_workshops_status ON workshops(status)",
        "CREATE INDEX idx_workshops_verified ON workshops(is_verified)",
        "CREATE INDEX idx_workshop_bookings_workshop_id ON workshop_bookings(workshop_id)",
        "CREATE INDEX idx_workshop_bookings_user_id ON workshop_bookings(user_id)",
        "CREATE INDEX idx_workshop_bookings_date ON workshop_bookings(booking_date)",
        "CREATE INDEX idx_workshop_reviews_workshop_id ON workshop_reviews(workshop_id)",
        "CREATE INDEX idx_reviews_workshop_id ON reviews(workshop_id)",
        "CREATE INDEX idx_service_bookings_workshop_id ON service_bookings(workshop_id)",
        "CREATE INDEX idx_operating_hours_workshop_id ON workshop_operating_hours(workshop_id)",
        "CREATE INDEX idx_bookings_workshop_id ON bookings(workshop_id)",
        "CREATE INDEX idx_bookings_user_id ON bookings(user_id)",
        "CREATE INDEX idx_bookings_date ON bookings(booking_date)",
        "CREATE INDEX idx_services_workshop_id ON services(workshop_id)",
        "CREATE INDEX idx_services_active ON services(is_active)",
        "CREATE INDEX idx_carimages_car_id ON carimages(CarID)",
        "CREATE INDEX idx_cars_seller_id ON cars(SellerID)",
        "CREATE INDEX idx_cars_status ON cars(carStatus)",
        "CREATE INDEX idx_users_email ON users(Email)",
        "CREATE INDEX idx_users_role ON users(role)",
        "CREATE INDEX idx_admin_username ON admin(username)",
        "CREATE INDEX idx_admin_email ON admin(email)",
        "CREATE INDEX idx_user_sessions_token ON user_sessions(token)",
        "CREATE INDEX idx_admin_sessions_token ON admin_sessions(token)"
    ];

    $indexCount = 0;
    foreach ($indexQueries as $indexQuery) {
        $result = mysqli_query($conn, $indexQuery);
        if ($result) {
            $indexCount++;
        }
        // Skip errors for duplicate indexes - this is expected on subsequent runs
    }
    $results[] = "Processed $indexCount indexes (some may already exist)";

    // Insert sample workshop data
    $sampleWorkshops = [
        [
            'user_id' => 1,
            'name' => 'AutoCare Workshop',
            'owner_name' => 'Ahmed Khan',
            'email' => 'autocare@example.com',
            'phone' => '+92-300-1234567',
            'address' => '123 Main Street, Block A',
            'city' => 'Karachi',
            'description' => 'Professional car repair and maintenance services',
            'specialization' => 'Engine Repair, Brake Service',
            'status' => 'active'
        ]
    ];

    foreach ($sampleWorkshops as $workshop) {
        $insertWorkshopSQL = "INSERT IGNORE INTO workshops (user_id, name, owner_name, email, phone, address, city, description, specialization, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertWorkshopSQL);
        if ($stmt) {
            $stmt->bind_param("isssssssss", 
                $workshop['user_id'],
                $workshop['name'],
                $workshop['owner_name'],
                $workshop['email'],
                $workshop['phone'],
                $workshop['address'],
                $workshop['city'],
                $workshop['description'],
                $workshop['specialization'],
                $workshop['status']
            );
            
            if ($stmt->execute()) {
                $results[] = "Added sample workshop: " . $workshop['name'];
                $workshop_id = mysqli_insert_id($conn);
                
                // Insert sample services for this workshop
                $sampleServices = [
                    ['service_name' => 'Oil Change', 'price' => 2500.00, 'category' => 'Maintenance'],
                    ['service_name' => 'Brake Repair', 'price' => 8000.00, 'category' => 'Brake Service'],
                    ['service_name' => 'Engine Diagnostic', 'price' => 3000.00, 'category' => 'Engine Repair']
                ];
                
                foreach ($sampleServices as $service) {
                    $insertServiceSQL = "INSERT IGNORE INTO workshop_services (workshop_id, service_name, service_category, price) VALUES (?, ?, ?, ?)";
                    $serviceStmt = $conn->prepare($insertServiceSQL);
                    if ($serviceStmt) {
                        $serviceStmt->bind_param("issd", $workshop_id, $service['service_name'], $service['category'], $service['price']);
                        if ($serviceStmt->execute()) {
                            $results[] = "Added sample service: " . $service['service_name'];
                            
                            // Also add to alternative services table
                            $insertAltServiceSQL = "INSERT IGNORE INTO services (workshop_id, service_name, service_category, price, duration) VALUES (?, ?, ?, ?, '60')";
                            $altServiceStmt = $conn->prepare($insertAltServiceSQL);
                            if ($altServiceStmt) {
                                $altServiceStmt->bind_param("issd", $workshop_id, $service['service_name'], $service['category'], $service['price']);
                                $altServiceStmt->execute();
                                $altServiceStmt->close();
                            }
                        }
                        $serviceStmt->close();
                    }
                }
            } else {
                $results[] = "Workshop sample data might already exist";
            }
            $stmt->close();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Database setup completed with all workshop tables and admin users',
        'results' => $results,
        'admin_credentials' => [
            [
                'username' => 'admin',
                'email' => 'admin@carhubpk.com',
                'password' => 'admin123',
                'description' => 'Default system administrator'
            ],
            [
                'username' => 'carhub_admin',
                'email' => 'carhubadmin@example.com', 
                'password' => 'CarHub2025!',
                'description' => 'CarHub main administrator'
            ]
        ]
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
