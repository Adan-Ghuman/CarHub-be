<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/config.php';

try {
    $results = [];
    
    // Check if column already exists
    $checkQuery = "SHOW COLUMNS FROM workshop_reviews LIKE 'booking_id'";
    $result = $conn->query($checkQuery);
    
    if ($result->num_rows > 0) {
        $results[] = "booking_id column already exists in workshop_reviews table.";
    } else {
        // Add the booking_id column
        $alterQuery = "ALTER TABLE workshop_reviews 
                      ADD COLUMN booking_id INT NULL AFTER user_id";
        
        if ($conn->query($alterQuery)) {
            $results[] = "Successfully added booking_id column to workshop_reviews table.";
            
            // Try to add foreign key constraint (might fail if workshop_bookings table doesn't exist)
            try {
                $fkQuery = "ALTER TABLE workshop_reviews 
                           ADD FOREIGN KEY (booking_id) REFERENCES workshop_bookings(id) ON DELETE CASCADE";
                if ($conn->query($fkQuery)) {
                    $results[] = "Successfully added foreign key constraint for booking_id.";
                } else {
                    $results[] = "Warning: Could not add foreign key constraint: " . $conn->error;
                }
            } catch (Exception $e) {
                $results[] = "Warning: Could not add foreign key constraint: " . $e->getMessage();
            }
            
            // Add index for better performance
            try {
                $indexQuery = "CREATE INDEX idx_workshop_reviews_booking_id ON workshop_reviews(booking_id)";
                if ($conn->query($indexQuery)) {
                    $results[] = "Successfully added index on booking_id column.";
                } else {
                    $results[] = "Warning: Failed to add index: " . $conn->error;
                }
            } catch (Exception $e) {
                $results[] = "Warning: Failed to add index: " . $e->getMessage();
            }
        } else {
            throw new Exception("Error adding booking_id column: " . $conn->error);
        }
    }
    
    // Show current table structure
    $describeQuery = "DESCRIBE workshop_reviews";
    $result = $conn->query($describeQuery);
    
    $tableStructure = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tableStructure[] = $row['Field'] . " (" . $row['Type'] . ")";
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database migration completed',
        'results' => $results,
        'table_structure' => $tableStructure
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
