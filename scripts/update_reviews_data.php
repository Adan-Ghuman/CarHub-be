<?php
require_once 'config.php';

echo "Updating existing reviews with workshop data...\n";

try {
    // Update reviews with workshop_id, customer info, and service info
    $updateQuery = "
        UPDATE reviews r 
        JOIN bookings b ON r.booking_id = b.id 
        JOIN users u ON b.user_id = u.id
        JOIN services s ON b.service_id = s.id
        SET r.workshop_id = b.workshop_id,
            r.customer_name = u.name,
            r.customer_email = u.email,
            r.service_name = s.service_name
        WHERE r.workshop_id IS NULL
    ";
    
    $result = $conn->query($updateQuery);
    if ($result) {
        echo "✅ Updated existing reviews with workshop data\n";
        echo "Affected rows: " . $conn->affected_rows . "\n";
    } else {
        echo "❌ Error updating reviews: " . $conn->error . "\n";
    }
    
    // Check the updated data
    echo "\nChecking updated review data...\n";
    $checkQuery = "SELECT id, customer_name, service_name, workshop_id, rating, comment FROM reviews LIMIT 5";
    $result = $conn->query($checkQuery);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Review ID: " . $row['id'] . ", Customer: " . $row['customer_name'] . 
                 ", Service: " . $row['service_name'] . ", Workshop: " . $row['workshop_id'] . 
                 ", Rating: " . $row['rating'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
