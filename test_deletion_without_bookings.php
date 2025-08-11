<?php
require_once 'config.php';

echo "=== Creating Test Service Without Bookings ===\n";

// Create a test service that has no bookings
$insert_query = "INSERT INTO workshop_services (workshop_id, service_name, service_category, description, price, duration) 
                 VALUES (3, 'Test Delete Service', 'general', 'This service can be safely deleted', 50, 30)";

if (mysqli_query($conn, $insert_query)) {
    $new_service_id = mysqli_insert_id($conn);
    echo "✅ Created test service with ID: $new_service_id\n";
    
    // Now test deletion
    echo "\n=== Testing Deletion ===\n";
    $api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/delete_service_api.php";
    $request_data = [
        'service_id' => $new_service_id,
        'workshop_id' => 3
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo "API Response: $response\n";
    
    $response_data = json_decode($response, true);
    if ($response_data && $response_data['success']) {
        echo "✅ Deletion successful!\n";
        
        // Verify it's marked as inactive
        $check_query = "SELECT is_active FROM workshop_services WHERE id = $new_service_id";
        $result = mysqli_query($conn, $check_query);
        $service = mysqli_fetch_assoc($result);
        
        if ($service && !$service['is_active']) {
            echo "✅ Service is properly marked as inactive\n";
        } else {
            echo "❌ Service is still active\n";
        }
    } else {
        echo "❌ Deletion failed: " . ($response_data['message'] ?? 'Unknown error') . "\n";
    }
    
} else {
    echo "❌ Failed to create test service: " . mysqli_error($conn) . "\n";
}
?>
