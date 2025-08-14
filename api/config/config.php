<?php

$servername = "interchange.proxy.rlwy.net";
$username = "root";
$password = "RBexaveASpsEcScgfLHIfTrkpsvrQjzO";
$database = "railway";
$port = 44546;

// Define constants for PDO usage (only if not already defined)
if (!defined('DB_HOST')) define('DB_HOST', $servername);
if (!defined('DB_USER')) define('DB_USER', $username);
if (!defined('DB_PASS')) define('DB_PASS', $password);
if (!defined('DB_NAME')) define('DB_NAME', $database);
if (!defined('DB_PORT')) define('DB_PORT', $port);

// Create MySQLi connection with port
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check MySQLi connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-setup database tables if they don't exist
function setupDatabaseTables($conn) {
    // Check if users table exists
    $checkTable = "SHOW TABLES LIKE 'users'";
    $result = mysqli_query($conn, $checkTable);
    
    if (mysqli_num_rows($result) == 0) {
        // Tables don't exist, create them
        $queries = [
            // Admin table
            "CREATE TABLE IF NOT EXISTS admin (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            // Users table
            "CREATE TABLE IF NOT EXISTS users (
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
            )",
            
            // User sessions table
            "CREATE TABLE IF NOT EXISTS user_sessions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                user_type ENUM('admin', 'user') NOT NULL,
                token VARCHAR(255) UNIQUE NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            // Admin sessions table
            "CREATE TABLE IF NOT EXISTS admin_sessions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                admin_id INT,
                token VARCHAR(255) UNIQUE NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE
            )",
            
            // Insert default admin
            "INSERT IGNORE INTO admin (username, email, password, full_name) 
             VALUES ('admin', 'admin@carhubpk.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator')"
        ];
        
        foreach ($queries as $query) {
            mysqli_query($conn, $query);
        }
    }
}

// Call auto-setup
setupDatabaseTables($conn);

// Create PDO connection for APIs that require it
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database;port=$port;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("PDO Connection failed: " . $e->getMessage());
    die("Database connection failed");
}

            
