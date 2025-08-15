<?php
require_once '../api/config/config.php';

echo "=== Creating Workshops Table for Railway Database ===\n";

// Create workshops table
$create_workshops_sql = "
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

echo "Creating workshops table...\n";
if (mysqli_query($conn, $create_workshops_sql)) {
    echo "✅ Workshops table created successfully\n";
} else {
    echo "❌ Error creating workshops table: " . mysqli_error($conn) . "\n";
}

// Create workshop_services table
$create_services_sql = "
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

echo "Creating workshop_services table...\n";
if (mysqli_query($conn, $create_services_sql)) {
    echo "✅ Workshop_services table created successfully\n";
} else {
    echo "❌ Error creating workshop_services table: " . mysqli_error($conn) . "\n";
}

// Create workshop_reviews table
$create_reviews_sql = "
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

echo "Creating workshop_reviews table...\n";
if (mysqli_query($conn, $create_reviews_sql)) {
    echo "✅ Workshop_reviews table created successfully\n";
} else {
    echo "❌ Error creating workshop_reviews table: " . mysqli_error($conn) . "\n";
}

// Create service_bookings table
$create_bookings_sql = "
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

echo "Creating service_bookings table...\n";
if (mysqli_query($conn, $create_bookings_sql)) {
    echo "✅ Service_bookings table created successfully\n";
} else {
    echo "❌ Error creating service_bookings table: " . mysqli_error($conn) . "\n";
}

// Add indexes for better performance
echo "Adding indexes...\n";
$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_workshop_services_workshop_id ON workshop_services(workshop_id)",
    "CREATE INDEX IF NOT EXISTS idx_workshop_services_active ON workshop_services(is_active)",
    "CREATE INDEX IF NOT EXISTS idx_workshops_status ON workshops(status)",
    "CREATE INDEX IF NOT EXISTS idx_workshops_verified ON workshops(is_verified)",
    "CREATE INDEX IF NOT EXISTS idx_service_bookings_workshop_id ON service_bookings(workshop_id)",
    "CREATE INDEX IF NOT EXISTS idx_service_bookings_user_id ON service_bookings(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_workshop_reviews_workshop_id ON workshop_reviews(workshop_id)"
];

foreach ($indexes as $index_sql) {
    if (mysqli_query($conn, $index_sql)) {
        echo "✅ Index created successfully\n";
    } else {
        echo "❌ Error creating index: " . mysqli_error($conn) . "\n";
    }
}

// Add some sample workshop data
echo "Adding sample workshop data...\n";
$sample_workshops = [
    [
        'user_id' => 11,
        'name' => 'AutoCare Workshop',
        'owner_name' => 'Ahmed Khan',
        'email' => 'autocare@example.com',
        'phone' => '+92-300-1234567',
        'address' => '123 Main Street, Block A',
        'city' => 'Karachi',
        'description' => 'Professional car repair and maintenance services',
        'status' => 'active'
    ],
    [
        'user_id' => 12,
        'name' => 'Speed Motors',
        'owner_name' => 'Muhammad Ali',
        'email' => 'speedmotors@example.com',
        'phone' => '+92-300-2345678',
        'address' => '456 Auto Street, Gulshan',
        'city' => 'Karachi',
        'description' => 'Expert engine repair and performance tuning',
        'status' => 'active'
    ],
    [
        'user_id' => 13,
        'name' => 'CarFix Solutions',
        'owner_name' => 'Fatima Sheikh',
        'email' => 'carfix@example.com',
        'phone' => '+92-300-3456789',
        'address' => '789 Service Road, DHA',
        'city' => 'Lahore',
        'description' => 'Complete automotive solutions and electrical work',
        'status' => 'active'
    ]
];

foreach ($sample_workshops as $workshop) {
    $insert_sql = "INSERT INTO workshops (user_id, name, owner_name, email, phone, address, city, description, status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("issssssss", 
        $workshop['user_id'],
        $workshop['name'],
        $workshop['owner_name'],
        $workshop['email'],
        $workshop['phone'],
        $workshop['address'],
        $workshop['city'],
        $workshop['description'],
        $workshop['status']
    );
    
    if ($stmt->execute()) {
        echo "✅ Added workshop: " . $workshop['name'] . "\n";
    } else {
        echo "❌ Error adding workshop " . $workshop['name'] . ": " . $stmt->error . "\n";
    }
}

// Verify tables exist
echo "\n=== Verifying Tables ===\n";
$tables = ['workshops', 'workshop_services', 'workshop_reviews', 'service_bookings'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "✅ $table table exists\n";
    } else {
        echo "❌ $table table not found\n";
    }
}

echo "\n=== Workshop Tables Setup Complete ===\n";
mysqli_close($conn);
?>
