<?php
include "config.php";

// Test adding a service for Workshop 3
$serviceData = [
    'workshop_id' => 3,
    'service_name' => 'Test Service',
    'description' => 'A test service for debugging',
    'price' => 500.00,
    'estimated_time' => '60',
    'service_category' => 'maintenance'
];

echo "Testing service addition for Workshop 3...\n";

// Insert test service
$insertQuery = "INSERT INTO workshop_services 
               (workshop_id, service_name, service_category, description, price, duration, is_active, created_at) 
               VALUES 
               (?, ?, ?, ?, ?, ?, TRUE, NOW())";

$stmt = mysqli_prepare($conn, $insertQuery);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "isssds", 
        $serviceData['workshop_id'],
        $serviceData['service_name'],
        $serviceData['service_category'],
        $serviceData['description'],
        $serviceData['price'],
        $serviceData['estimated_time']
    );

    if (mysqli_stmt_execute($stmt)) {
        $serviceId = mysqli_insert_id($conn);
        echo "✅ Service added successfully with ID: $serviceId\n";
        
        // Fetch the service to verify
        $fetchQuery = "SELECT * FROM workshop_services WHERE id = ?";
        $fetchStmt = mysqli_prepare($conn, $fetchQuery);
        mysqli_stmt_bind_param($fetchStmt, "i", $serviceId);
        mysqli_stmt_execute($fetchStmt);
        $result = mysqli_stmt_get_result($fetchStmt);
        $service = mysqli_fetch_assoc($result);
        
        echo "Service details:\n";
        echo "- ID: " . $service['id'] . "\n";
        echo "- Name: " . $service['service_name'] . "\n";
        echo "- Category: " . $service['service_category'] . "\n";
        echo "- Price: " . $service['price'] . "\n";
        echo "- Duration: " . $service['duration'] . " minutes\n";
        echo "- Workshop ID: " . $service['workshop_id'] . "\n";
        
    } else {
        echo "❌ Failed to execute: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "❌ Failed to prepare statement: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
