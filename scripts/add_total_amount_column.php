<?php
include __DIR__ . "/../api/config/config.php";

echo "🔧 Adding total_amount column to workshop_bookings table...\n";

try {
    // First, check if the column already exists
    $check_column_query = "SHOW COLUMNS FROM workshop_bookings LIKE 'total_amount'";
    $result = mysqli_query($conn, $check_column_query);
    
    if (mysqli_num_rows($result) > 0) {
        echo "✅ total_amount column already exists\n";
    } else {
        // Add the total_amount column
        $add_column_query = "ALTER TABLE workshop_bookings ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER booking_time";
        
        if (mysqli_query($conn, $add_column_query)) {
            echo "✅ Successfully added total_amount column\n";
            
            // Update existing records to have a default value
            $update_existing = "UPDATE workshop_bookings SET total_amount = 0.00 WHERE total_amount IS NULL";
            if (mysqli_query($conn, $update_existing)) {
                echo "✅ Updated existing records with default total_amount value\n";
            }
        } else {
            echo "❌ Error adding column: " . mysqli_error($conn) . "\n";
        }
    }
    
    // Show the current table structure
    echo "\n📋 Current workshop_bookings table structure:\n";
    $describe_query = "DESCRIBE workshop_bookings";
    $describe_result = mysqli_query($conn, $describe_query);
    
    while ($row = mysqli_fetch_assoc($describe_result)) {
        echo "- {$row['Field']}: {$row['Type']} ({$row['Null']}, Default: {$row['Default']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

mysqli_close($conn);
?>
