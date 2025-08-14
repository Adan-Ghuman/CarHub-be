<?php
// One-time setup script for Railway database
include __DIR__ . "/config/config.php";

$setupQueries = [
    "CREATE TABLE IF NOT EXISTS admin (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
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
    
    "INSERT IGNORE INTO admin (username, email, password, full_name) 
     VALUES ('admin', 'admin@carhubpk.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator')"
];

header('Content-Type: application/json');

$results = [];
foreach ($setupQueries as $query) {
    $result = mysqli_query($conn, $query);
    if ($result) {
        $results[] = "✅ Query executed successfully";
    } else {
        $results[] = "❌ Error: " . mysqli_error($conn);
    }
}

echo json_encode([
    'status' => 'setup_complete',
    'results' => $results,
    'message' => 'Database setup completed. You can now test user registration.'
]);

mysqli_close($conn);
?>
