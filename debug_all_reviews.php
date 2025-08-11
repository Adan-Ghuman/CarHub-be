<?php
include 'config.php';

try {
    echo "=== CHECKING ALL REVIEWS IN DATABASE ===\n\n";
    
    // Get all reviews
    $result = $conn->query('SELECT * FROM reviews ORDER BY workshop_id, created_at DESC');
    
    if ($result) {
        $reviews = $result->fetch_all(MYSQLI_ASSOC);
        echo "Total reviews in database: " . count($reviews) . "\n\n";
        
        if (count($reviews) > 0) {
            echo "All reviews:\n";
            foreach($reviews as $index => $review) {
                echo ($index + 1) . ". Workshop ID: " . $review['workshop_id'] . 
                     ", Customer: " . ($review['customer_name'] ?? 'N/A') . 
                     ", Rating: " . $review['rating'] . 
                     ", Created: " . $review['created_at'] . "\n";
            }
        } else {
            echo "No reviews found in the database.\n";
        }
    } else {
        echo "Error querying reviews: " . $conn->error . "\n";
    }
    
    echo "\n=== CHECKING ALL WORKSHOPS ===\n\n";
    
    // Get all workshops
    $result = $conn->query('SELECT id, name FROM workshops ORDER BY id');
    
    if ($result) {
        $workshops = $result->fetch_all(MYSQLI_ASSOC);
        echo "All workshops:\n";
        foreach($workshops as $workshop) {
            echo "ID: " . $workshop['id'] . ", Name: " . $workshop['name'] . "\n";
        }
    } else {
        echo "Error querying workshops: " . $conn->error . "\n";
    }
    
    echo "\n=== CHECKING USER ID 11 WORKSHOP ASSOCIATION ===\n\n";
    
    // Check user 11's workshop association
    $result = $conn->query('SELECT * FROM users WHERE id = 11');
    if ($result) {
        $user = $result->fetch_assoc();
        if ($user) {
            echo "User 11 details:\n";
            echo "  Name: " . ($user['name'] ?? 'N/A') . "\n";
            echo "  Role: " . ($user['role'] ?? 'N/A') . "\n";
            echo "  Workshop ID: " . ($user['workshop_id'] ?? 'N/A') . "\n";
        } else {
            echo "User 11 not found.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
