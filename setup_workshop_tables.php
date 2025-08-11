<?php
require_once 'config.php';

echo "Setting up Workshop Dashboard Database Tables\n";
echo "=============================================\n\n";

// SQL statements to create missing tables
$sql_statements = [
    
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
    echo "Creating table " . ($index + 1) . "...\n";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Success\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
    echo "\n";
}

// Insert sample data for testing
echo "Inserting sample data...\n";

// Sample services for workshop ID 1 (if it exists)
$sample_services = [
    [1, 'Oil Change', 'Complete engine oil change with filter replacement', 50.00, 30],
    [1, 'Brake Service', 'Brake pad replacement and brake system inspection', 120.00, 60],
    [1, 'Tire Rotation', 'Four wheel tire rotation and pressure check', 25.00, 20],
    [1, 'Engine Tune-up', 'Complete engine diagnostics and tune-up service', 200.00, 120],
    [1, 'Car Wash', 'Professional exterior and interior car cleaning', 30.00, 45]
];

// Check if workshop 1 exists
$workshop_check = $conn->query("SELECT id FROM workshops WHERE id = 1");
if ($workshop_check && $workshop_check->num_rows > 0) {
    
    // Insert sample services
    $service_sql = "INSERT INTO services (workshop_id, service_name, description, price, duration) VALUES (?, ?, ?, ?, ?)";
    $service_stmt = $conn->prepare($service_sql);
    
    foreach ($sample_services as $service) {
        $service_stmt->bind_param("issdi", $service[0], $service[1], $service[2], $service[3], $service[4]);
        if ($service_stmt->execute()) {
            echo "✓ Added service: {$service[1]}\n";
        } else {
            echo "✗ Failed to add service: {$service[1]} - " . $conn->error . "\n";
        }
    }
    
    // Sample bookings (if user ID 1 exists)
    $user_check = $conn->query("SELECT id FROM users WHERE id = 1");
    if ($user_check && $user_check->num_rows > 0) {
        
        $sample_bookings = [
            [1, 1, 1, '2024-01-15', '10:00:00', 'completed', 50.00],
            [1, 1, 2, '2024-01-20', '14:00:00', 'confirmed', 120.00],
            [1, 1, 3, '2024-01-25', '09:00:00', 'pending', 25.00]
        ];
        
        $booking_sql = "INSERT INTO bookings (user_id, workshop_id, service_id, booking_date, booking_time, status, price) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $booking_stmt = $conn->prepare($booking_sql);
        
        foreach ($sample_bookings as $booking) {
            $booking_stmt->bind_param("iiisssd", $booking[0], $booking[1], $booking[2], $booking[3], $booking[4], $booking[5], $booking[6]);
            if ($booking_stmt->execute()) {
                echo "✓ Added booking for service ID: {$booking[2]}\n";
                
                // Add review for completed booking
                if ($booking[5] === 'completed') {
                    $booking_id = $conn->insert_id;
                    $review_sql = "INSERT INTO reviews (booking_id, rating, comment) VALUES (?, ?, ?)";
                    $review_stmt = $conn->prepare($review_sql);
                    $rating = 5;
                    $comment = "Excellent service! Very professional and quick.";
                    $review_stmt->bind_param("iis", $booking_id, $rating, $comment);
                    if ($review_stmt->execute()) {
                        echo "✓ Added review for booking ID: {$booking_id}\n";
                    }
                }
            } else {
                echo "✗ Failed to add booking - " . $conn->error . "\n";
            }
        }
    } else {
        echo "No user with ID 1 found, skipping sample bookings\n";
    }
    
} else {
    echo "No workshop with ID 1 found, skipping sample data\n";
}

echo "\n";
echo "Database setup complete!\n";
echo "========================\n";

// Final verification
echo "Verifying tables were created:\n";
$verification_tables = ['services', 'bookings', 'reviews', 'workshop_operating_hours'];

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
