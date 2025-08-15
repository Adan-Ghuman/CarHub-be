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
    
    "CREATE TABLE IF NOT EXISTS workshops (
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
    )",
    
    "CREATE TABLE IF NOT EXISTS workshop_services (
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
    )",
    
    "CREATE TABLE IF NOT EXISTS workshop_reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        workshop_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT,
        workshop_response TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS service_bookings (
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
    )",
    
    "INSERT IGNORE INTO admin (username, email, password, full_name) 
     VALUES ('admin', 'admin@carhubpk.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator')",
     
    "INSERT IGNORE INTO workshops (id, user_id, name, owner_name, email, phone, address, city, description, status) VALUES 
     (1, 11, 'AutoCare Workshop', 'Ahmed Khan', 'autocare@example.com', '+92-300-1234567', '123 Main Street, Block A', 'Karachi', 'Professional car repair and maintenance services', 'active'),
     (2, 12, 'Speed Motors', 'Muhammad Ali', 'speedmotors@example.com', '+92-300-2345678', '456 Auto Street, Gulshan', 'Karachi', 'Expert engine repair and performance tuning', 'active'),
     (3, 13, 'CarFix Solutions', 'Fatima Sheikh', 'carfix@example.com', '+92-300-3456789', '789 Service Road, DHA', 'Lahore', 'Complete automotive solutions and electrical work', 'active')"
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
    'message' => 'Database setup completed with workshop tables. You can now test workshop features and user registration.'
]);

mysqli_close($conn);
?>
