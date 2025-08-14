<?php
// Test delete service API with various error scenarios
echo "=== Testing Delete Service Error Scenarios ===\n";

$api_url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/delete_service_api.php";

// Test 1: Missing service_id
echo "\n1. Testing with missing service_id:\n";
$test_data = ['workshop_id' => 3];
$response = testDeleteAPI($api_url, $test_data);
echo "Response: $response\n";

// Test 2: Non-existent service_id
echo "\n2. Testing with non-existent service_id:\n";
$test_data = ['service_id' => 999, 'workshop_id' => 3];
$response = testDeleteAPI($api_url, $test_data);
echo "Response: $response\n";

// Test 3: Wrong workshop_id
echo "\n3. Testing with wrong workshop_id:\n";
$test_data = ['service_id' => 10, 'workshop_id' => 999];
$response = testDeleteAPI($api_url, $test_data);
echo "Response: $response\n";

// Test 4: Valid deletion
echo "\n4. Testing valid deletion (should work):\n";
$test_data = ['service_id' => 13, 'workshop_id' => 3];
$response = testDeleteAPI($api_url, $test_data);
echo "Response: $response\n";

function testDeleteAPI($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

echo "\n=== Testing complete ===\n";
?>
