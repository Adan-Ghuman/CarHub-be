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
