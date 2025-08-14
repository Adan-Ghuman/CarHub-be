<?php
// Comprehensive test of the service deletion flow
echo "=== Comprehensive Service Deletion Test ===\n";

require_once 'config.php';

// Step 1: Check current services
echo "\n1. Current active services:\n";
$services_query = "SELECT id, service_name, is_active FROM workshop_services WHERE workshop_id = 3 AND is_active = 1";
$result = mysqli_query($conn, $services_query);
$services = [];
while ($service = mysqli_fetch_assoc($result)) {
    $services[] = $service;
    echo "   ID: {$service['id']} | Name: {$service['service_name']}\n";
}

if (empty($services)) {
    echo "   No active services found.\n";
    exit;
}

// Step 2: Test frontend API call format
$service_to_delete = $services[0];
echo "\n2. Testing deletion of service ID: {$service_to_delete['id']} ({$service_to_delete['service_name']})\n";

$api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/delete_service_api.php";
$request_data = [
    'service_id' => (int)$service_to_delete['id'],
    'workshop_id' => 3
];

echo "Request data: " . json_encode($request_data) . "\n";

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
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "API Response: $response\n";

$response_data = json_decode($response, true);

// Step 3: Verify the result
echo "\n3. Verification:\n";
if ($response_data && $response_data['success']) {
    echo "✅ API reported success\n";
    
    // Check if service is now inactive
    $check_query = "SELECT is_active FROM workshop_services WHERE id = {$service_to_delete['id']}";
    $check_result = mysqli_query($conn, $check_query);
    $updated_service = mysqli_fetch_assoc($check_result);
    
    if ($updated_service && !$updated_service['is_active']) {
        echo "✅ Service is now marked as inactive in database\n";
    } else {
        echo "❌ Service is still active in database\n";
    }
    
    // Test the get services API
    echo "\n4. Testing get services API (should not include deleted service):\n";
    $get_api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_workshop_services_api.php";
    $get_data = [
        'workshopId' => 3,
        'isActive' => true
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $get_api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($get_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $get_response = curl_exec($ch);
    curl_close($ch);
    
    $get_data = json_decode($get_response, true);
    if ($get_data && $get_data['success']) {
        $found_deleted_service = false;
        foreach ($get_data['data'] as $service) {
            if ($service['id'] == $service_to_delete['id']) {
                $found_deleted_service = true;
                break;
            }
        }
        
        if (!$found_deleted_service) {
            echo "✅ Deleted service is not returned by get services API\n";
        } else {
            echo "❌ Deleted service is still returned by get services API\n";
        }
        
        echo "   Total active services now: " . count($get_data['data']) . "\n";
    }
    
} else {
    echo "❌ API reported failure\n";
    if ($response_data) {
        echo "   Error: " . ($response_data['message'] ?? $response_data['error'] ?? 'Unknown error') . "\n";
    }
}

echo "\n=== Test complete ===\n";
echo "Note: If deletion was successful, you can restore the service by running:\n";
echo "UPDATE workshop_services SET is_active = 1 WHERE id = {$service_to_delete['id']};\n";
?>
