<?php
// Test the improved cars API with joined maker and model names

echo "<h3>Testing Improved Cars API with Joined Names</h3>";

$url = 'http://localhost/armghan/CARHUB_PK/backend/Car_api/fetch_cars_api.php';

$options = [
    'http' => [
        'header' => "ngrok-skip-browser-warning: true\r\n",
        'method' => 'GET'
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

$data = json_decode($result, true);

if ($data && is_array($data)) {
    echo "<strong>✅ API Response successful!</strong><br>";
    echo "<strong>Total cars found:</strong> " . count($data) . "<br><br>";
    
    // Show first car details
    if (count($data) > 0) {
        echo "<h4>First Car Details:</h4>";
        $firstCar = $data[0];
        
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
        echo "<strong>CarID:</strong> " . ($firstCar['CarID'] ?? 'N/A') . "<br>";
        echo "<strong>MakerID:</strong> " . ($firstCar['MakerID'] ?? 'N/A') . "<br>";
        echo "<strong>ModelID:</strong> " . ($firstCar['ModelID'] ?? 'N/A') . "<br>";
        echo "<strong>🎯 MakerName:</strong> " . ($firstCar['MakerName'] ?? 'NOT JOINED') . "<br>";
        echo "<strong>🎯 ModelName:</strong> " . ($firstCar['ModelName'] ?? 'NOT JOINED') . "<br>";
        echo "<strong>Price:</strong> " . ($firstCar['Price'] ?? 'N/A') . "<br>";
        echo "<strong>Location:</strong> " . ($firstCar['Location'] ?? 'N/A') . "<br>";
        echo "</div>";
        
        // Check if we successfully got the joined names
        if (isset($firstCar['MakerName']) && isset($firstCar['ModelName'])) {
            echo "<strong style='color: green;'>✅ SUCCESS: Maker and Model names are now included!</strong><br>";
        } else {
            echo "<strong style='color: red;'>❌ ISSUE: Maker and Model names not found in response</strong><br>";
        }
    }
} else {
    echo "<strong style='color: red;'>❌ API Error or No Data</strong><br>";
    echo "Response: " . $result;
}
?>
