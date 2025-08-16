<?php
require_once __DIR__ . '/../api/config/config.php';

echo "Fixing workshop_reviews table structure...\n\n";

try {
    // First, check current table structure
    echo "Current workshop_reviews table structure:\n";
    $describeQuery = "DESCRIBE workshop_reviews";
    $result = $conn->query($describeQuery);
    
    $existingColumns = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existingColumns[] = $row['Field'];
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "Error checking table structure: " . $conn->error . "\n";
        exit;
    }
    
    echo "\n";
    
    // Check if rating column exists
    if (!in_array('rating', $existingColumns)) {
        echo "Adding missing 'rating' column...\n";
        $addRatingQuery = "ALTER TABLE workshop_reviews 
                          ADD COLUMN rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5) AFTER user_id";
        
        if ($conn->query($addRatingQuery)) {
            echo "✓ Successfully added rating column.\n";
        } else {
            echo "✗ Error adding rating column: " . $conn->error . "\n";
        }
    } else {
        echo "✓ Rating column already exists.\n";
    }
    
    // Check if booking_id column exists
    if (!in_array('booking_id', $existingColumns)) {
        echo "Adding missing 'booking_id' column...\n";
        $addBookingIdQuery = "ALTER TABLE workshop_reviews 
                             ADD COLUMN booking_id INT NULL AFTER user_id";
        
        if ($conn->query($addBookingIdQuery)) {
            echo "✓ Successfully added booking_id column.\n";
            
            // Add foreign key constraint if workshop_bookings table exists
            $checkBookingsTable = "SHOW TABLES LIKE 'workshop_bookings'";
            $bookingsResult = $conn->query($checkBookingsTable);
            
            if ($bookingsResult && $bookingsResult->num_rows > 0) {
                echo "Adding foreign key constraint for booking_id...\n";
                $fkQuery = "ALTER TABLE workshop_reviews 
                           ADD CONSTRAINT fk_workshop_reviews_booking_id 
                           FOREIGN KEY (booking_id) REFERENCES workshop_bookings(id) ON DELETE CASCADE";
                
                if ($conn->query($fkQuery)) {
                    echo "✓ Successfully added foreign key constraint.\n";
                } else {
                    echo "⚠ Warning: Could not add foreign key constraint: " . $conn->error . "\n";
                }
                
                // Add index for better performance
                $indexQuery = "CREATE INDEX idx_workshop_reviews_booking_id ON workshop_reviews(booking_id)";
                if ($conn->query($indexQuery)) {
                    echo "✓ Successfully added index on booking_id.\n";
                } else {
                    echo "⚠ Warning: Could not add index: " . $conn->error . "\n";
                }
            }
        } else {
            echo "✗ Error adding booking_id column: " . $conn->error . "\n";
        }
    } else {
        echo "✓ Booking_id column already exists.\n";
    }
    
    echo "\n=== Final table structure ===\n";
    $finalResult = $conn->query($describeQuery);
    if ($finalResult) {
        while ($row = $finalResult->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ", " . $row['Null'] . ")\n";
        }
    }
    
    echo "\n✓ Table structure fix completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
