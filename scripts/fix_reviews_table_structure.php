<?php
require_once 'config.php';

echo "Adding missing columns to reviews table...\n\n";

try {
    // Add missing columns
    $alterQueries = [
        "ALTER TABLE reviews ADD COLUMN customer_name VARCHAR(255) NULL AFTER comment",
        "ALTER TABLE reviews ADD COLUMN customer_email VARCHAR(255) NULL AFTER customer_name", 
        "ALTER TABLE reviews ADD COLUMN service_name VARCHAR(255) NULL AFTER customer_email",
        "ALTER TABLE reviews ADD COLUMN workshop_id INT(11) NULL AFTER service_name",
        "ALTER TABLE reviews ADD COLUMN workshop_response TEXT NULL AFTER workshop_id",
        "ALTER TABLE reviews ADD COLUMN response_date TIMESTAMP NULL AFTER workshop_response"
    ];
    
    foreach ($alterQueries as $query) {
        echo "Executing: " . $query . "\n";
        $result = $conn->query($query);
        
        if ($result) {
            echo "✅ Success\n";
        } else {
            echo "❌ Error: " . $conn->error . "\n";
        }
        echo "\n";
    }
    
    // Update existing reviews with workshop_id from bookings
    echo "Updating existing reviews with workshop_id...\n";
    $updateQuery = "
        UPDATE reviews r 
        JOIN bookings b ON r.booking_id = b.id 
        SET r.workshop_id = b.workshop_id,
            r.customer_name = b.user_name,
            r.service_name = b.service_name
        WHERE r.workshop_id IS NULL
    ";
    
    $result = $conn->query($updateQuery);
    if ($result) {
        echo "✅ Updated existing reviews with workshop data\n";
    } else {
        echo "❌ Error updating reviews: " . $conn->error . "\n";
    }
    
    echo "\nChecking updated table structure...\n";
    $result = $conn->query("DESCRIBE reviews");
    
    if ($result) {
        echo "Updated Reviews table columns:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
