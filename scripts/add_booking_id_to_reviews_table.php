<?php
require_once __DIR__ . '/../api/config/config.php';

echo "Adding booking_id column to workshop_reviews table...\n";

try {
    // Check if column already exists
    $checkQuery = "SHOW COLUMNS FROM workshop_reviews LIKE 'booking_id'";
    $result = $conn->query($checkQuery);
    
    if ($result->num_rows > 0) {
        echo "booking_id column already exists in workshop_reviews table.\n";
    } else {
        // Add the booking_id column
        $alterQuery = "ALTER TABLE workshop_reviews 
                      ADD COLUMN booking_id INT NULL AFTER user_id,
                      ADD FOREIGN KEY (booking_id) REFERENCES workshop_bookings(id) ON DELETE CASCADE";
        
        if ($conn->query($alterQuery)) {
            echo "Successfully added booking_id column to workshop_reviews table.\n";
            
            // Add index for better performance
            $indexQuery = "CREATE INDEX idx_workshop_reviews_booking_id ON workshop_reviews(booking_id)";
            if ($conn->query($indexQuery)) {
                echo "Successfully added index on booking_id column.\n";
            } else {
                echo "Warning: Failed to add index: " . $conn->error . "\n";
            }
        } else {
            echo "Error adding booking_id column: " . $conn->error . "\n";
        }
    }
    
    // Show current table structure
    echo "\nCurrent workshop_reviews table structure:\n";
    $describeQuery = "DESCRIBE workshop_reviews";
    $result = $conn->query($describeQuery);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
