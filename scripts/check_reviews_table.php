<?php
include "config.php";

// Check if workshop_reviews table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'workshop_reviews'");

if (mysqli_num_rows($result) > 0) {
    echo "workshop_reviews table exists\n";
    
    // Show table structure
    $structure = mysqli_query($conn, "DESCRIBE workshop_reviews");
    echo "Table structure:\n";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "workshop_reviews table does not exist\n";
    echo "Creating workshop_reviews table...\n";
    
    $createTable = "CREATE TABLE workshop_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        workshop_id INT NOT NULL,
        user_id INT NOT NULL,
        booking_id INT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (booking_id) REFERENCES workshop_bookings(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_workshop (user_id, workshop_id)
    )";
    
    if (mysqli_query($conn, $createTable)) {
        echo "workshop_reviews table created successfully\n";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "\n";
    }
}

mysqli_close($conn);
?>
