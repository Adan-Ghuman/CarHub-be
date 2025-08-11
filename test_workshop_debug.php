<?php
include "config.php";

// Test workshop details API
$workshopId = 3;

$query = "SELECT w.*, 
                 COUNT(wr.id) as total_reviews,
                 COALESCE(AVG(wr.rating), 0) as rating
          FROM workshops w 
          LEFT JOIN workshop_reviews wr ON w.id = wr.workshop_id
          WHERE w.id = '$workshopId'
          GROUP BY w.id";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $workshop = mysqli_fetch_assoc($result);
    echo "Workshop found: " . $workshop['name'] . "\n";
    echo "Status: " . $workshop['status'] . "\n";
    echo "Verified: " . $workshop['is_verified'] . "\n";
    echo "Rating: " . $workshop['rating'] . "\n";
} else {
    echo "No workshop found with ID: $workshopId\n";
    echo "Error: " . mysqli_error($conn) . "\n";
    
    // Check all workshops
    $allQuery = "SELECT id, name, status, is_verified FROM workshops LIMIT 5";
    $allResult = mysqli_query($conn, $allQuery);
    echo "\nAvailable workshops:\n";
    while($row = mysqli_fetch_assoc($allResult)) {
        echo $row['id'] . " - " . $row['name'] . " (Status: " . $row['status'] . ", Verified: " . $row['is_verified'] . ")\n";
    }
}

mysqli_close($conn);
?>
