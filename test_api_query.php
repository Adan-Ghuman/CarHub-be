<?php
include "config.php";

echo "Testing the exact API query:\n\n";

$workshopId = 3;
$isActive = true;

// Same query as the API
$query = "SELECT * FROM workshop_services WHERE workshop_id = '$workshopId'";

if ($isActive !== null) {
    $activeFilter = $isActive ? 'TRUE' : 'FALSE';
    $query .= " AND is_active = $activeFilter";
}

$query .= " ORDER BY service_category ASC, service_name ASC";

echo "Query: $query\n\n";

$result = mysqli_query($conn, $query);

if ($result) {
    $services = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    echo "Raw database result:\n";
    echo "Number of rows: " . count($services) . "\n\n";
    
    foreach ($services as $index => $service) {
        echo "Row #" . ($index + 1) . ":\n";
        foreach ($service as $key => $value) {
            echo "  $key: $value\n";
        }
        echo "\n";
    }
    
    // Format services data (same as API)
    foreach ($services as &$service) {
        $service['price'] = number_format((float)$service['price'], 2);
        $service['is_active'] = (bool)$service['is_active'];
        
        // Add formatted created date
        $service['created_date'] = date('M j, Y', strtotime($service['created_at']));
    }
    unset($service); // Important: unset the reference
    
    echo "After formatting:\n";
    foreach ($services as $index => $service) {
        echo "Service #" . ($index + 1) . ":\n";
        echo "  ID: " . $service['id'] . "\n";
        echo "  Name: " . $service['service_name'] . "\n";
        echo "  Category: " . $service['service_category'] . "\n";
        echo "  Price: " . $service['price'] . "\n";
        echo "\n";
    }
    
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
