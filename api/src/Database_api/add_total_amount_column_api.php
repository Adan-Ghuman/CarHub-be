<?php
include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $results = [];
    
    // Check if the total_amount column exists
    $check_column_query = "SHOW COLUMNS FROM workshop_bookings LIKE 'total_amount'";
    $result = mysqli_query($conn, $check_column_query);
    
    if (mysqli_num_rows($result) > 0) {
        $results[] = "✅ total_amount column already exists";
        $column_exists = true;
    } else {
        $column_exists = false;
        
        // Add the total_amount column
        $add_column_query = "ALTER TABLE workshop_bookings ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER booking_time";
        
        if (mysqli_query($conn, $add_column_query)) {
            $results[] = "✅ Successfully added total_amount column";
            
            // Update existing records to have a default value
            $update_existing = "UPDATE workshop_bookings SET total_amount = 0.00 WHERE total_amount IS NULL";
            if (mysqli_query($conn, $update_existing)) {
                $results[] = "✅ Updated existing records with default total_amount value";
            }
        } else {
            throw new Exception("Error adding column: " . mysqli_error($conn));
        }
    }
    
    // Get current table structure
    $describe_query = "DESCRIBE workshop_bookings";
    $describe_result = mysqli_query($conn, $describe_query);
    
    $table_structure = [];
    while ($row = mysqli_fetch_assoc($describe_result)) {
        $table_structure[] = [
            'field' => $row['Field'],
            'type' => $row['Type'],
            'null' => $row['Null'],
            'default' => $row['Default']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database schema update completed',
        'results' => $results,
        'table_structure' => $table_structure,
        'column_exists' => $column_exists
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
