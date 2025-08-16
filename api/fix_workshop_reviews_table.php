<?php
// Direct database structure fix for workshop_reviews table
// Run this script via web browser to fix the database structure

include __DIR__ . "/config/config.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Workshop Reviews Table Structure Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Workshop Reviews Table Structure Fix</h1>
        
        <?php
        try {
            echo "<h2>Current Table Structure:</h2>";
            $describeQuery = "DESCRIBE workshop_reviews";
            $result = $conn->query($describeQuery);
            
            $existingColumns = [];
            if ($result) {
                echo "<pre>";
                while ($row = $result->fetch_assoc()) {
                    $existingColumns[] = $row['Field'];
                    echo sprintf("%-15s %-20s %-10s %-10s\n", 
                        $row['Field'], $row['Type'], $row['Null'], $row['Key']);
                }
                echo "</pre>";
            } else {
                echo "<p class='error'>❌ Error checking table structure: " . htmlspecialchars($conn->error) . "</p>";
                exit;
            }
            
            echo "<h2>Applying Fixes:</h2>";
            
            // Check and fix rating column
            if (!in_array('rating', $existingColumns)) {
                echo "<p class='info'>🔧 Adding missing 'rating' column...</p>";
                $addRatingQuery = "ALTER TABLE workshop_reviews 
                                  ADD COLUMN rating INT NOT NULL DEFAULT 5 CHECK (rating >= 1 AND rating <= 5) AFTER user_id";
                
                if ($conn->query($addRatingQuery)) {
                    echo "<p class='success'>✅ Successfully added rating column.</p>";
                } else {
                    echo "<p class='error'>❌ Error adding rating column: " . htmlspecialchars($conn->error) . "</p>";
                }
            } else {
                echo "<p class='success'>✅ Rating column already exists.</p>";
            }
            
            // Check and fix booking_id column
            if (!in_array('booking_id', $existingColumns)) {
                echo "<p class='info'>🔧 Adding missing 'booking_id' column...</p>";
                $addBookingIdQuery = "ALTER TABLE workshop_reviews 
                                     ADD COLUMN booking_id INT NULL AFTER user_id";
                
                if ($conn->query($addBookingIdQuery)) {
                    echo "<p class='success'>✅ Successfully added booking_id column.</p>";
                    
                    // Check if workshop_bookings table exists before adding foreign key
                    $checkBookingsTable = "SHOW TABLES LIKE 'workshop_bookings'";
                    $bookingsResult = $conn->query($checkBookingsTable);
                    
                    if ($bookingsResult && $bookingsResult->num_rows > 0) {
                        echo "<p class='info'>🔧 Adding foreign key constraint...</p>";
                        $fkQuery = "ALTER TABLE workshop_reviews 
                                   ADD CONSTRAINT fk_workshop_reviews_booking_id 
                                   FOREIGN KEY (booking_id) REFERENCES workshop_bookings(id) ON DELETE CASCADE";
                        
                        if ($conn->query($fkQuery)) {
                            echo "<p class='success'>✅ Successfully added foreign key constraint.</p>";
                        } else {
                            echo "<p class='warning'>⚠️ Warning: Could not add foreign key constraint: " . htmlspecialchars($conn->error) . "</p>";
                        }
                        
                        // Add index
                        $indexQuery = "CREATE INDEX idx_workshop_reviews_booking_id ON workshop_reviews(booking_id)";
                        if ($conn->query($indexQuery)) {
                            echo "<p class='success'>✅ Successfully added index on booking_id.</p>";
                        } else {
                            echo "<p class='warning'>⚠️ Warning: Could not add index: " . htmlspecialchars($conn->error) . "</p>";
                        }
                    } else {
                        echo "<p class='warning'>⚠️ workshop_bookings table not found, skipping foreign key constraint.</p>";
                    }
                } else {
                    echo "<p class='error'>❌ Error adding booking_id column: " . htmlspecialchars($conn->error) . "</p>";
                }
            } else {
                echo "<p class='success'>✅ Booking_id column already exists.</p>";
            }
            
            // Show final structure
            echo "<h2>Final Table Structure:</h2>";
            $finalResult = $conn->query($describeQuery);
            if ($finalResult) {
                echo "<pre>";
                printf("%-15s %-20s %-10s %-10s %-15s\n", 
                    "Field", "Type", "Null", "Key", "Extra");
                echo str_repeat("-", 80) . "\n";
                while ($row = $finalResult->fetch_assoc()) {
                    printf("%-15s %-20s %-10s %-10s %-15s\n", 
                        $row['Field'], $row['Type'], $row['Null'], $row['Key'], $row['Extra']);
                }
                echo "</pre>";
            }
            
            echo "<h2>✅ Database Structure Fix Completed!</h2>";
            echo "<p class='success'>The workshop_reviews table now has the correct structure for the API to work properly.</p>";
            echo "<p><strong>You can now test the API again.</strong></p>";
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        $conn->close();
        ?>
    </div>
</body>
</html>
