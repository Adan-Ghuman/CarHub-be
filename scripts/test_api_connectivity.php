<?php
// Test the API endpoints directly to see what they return

echo "<h3>Testing Current API Setup</h3>";

// Test current ngrok URL for makers
$url = 'https://225d0157b561.ngrok-free.app/armghan/CARHUB_PK/backend/Carfilters_api/fetch_makers_with_id_api.php';
$data = json_encode(['id' => 1]);

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\nngrok-skip-browser-warning: true\r\n",
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
echo "<strong>Testing Makers API with ID 1:</strong><br>";

// Test if ngrok URL is accessible
$result = @file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "❌ Ngrok URL not accessible, testing localhost instead...<br><br>";
    
    // Test local API
    $url = 'http://localhost/armghan/CARHUB_PK/backend/Carfilters_api/fetch_makers_with_id_api.php';
    $options['http']['header'] = "Content-type: application/json\r\n";
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    echo "<strong>Local API Response:</strong> " . $result . "<br><br>";
    
    // Test models API too
    $url = 'http://localhost/armghan/CARHUB_PK/backend/Carfilters_api/fetch_models_with_id_api.php';
    $result = file_get_contents($url, false, $context);
    echo "<strong>Local Models API Response:</strong> " . $result . "<br>";
    
} else {
    echo "✅ Ngrok URL accessible<br>";
    echo "<strong>Response:</strong> " . $result . "<br>";
}

// Show some sample data from cars table
echo "<h3>Sample Car Data (MakerID and ModelID)</h3>";
include 'config.php';
$query = "SELECT CarID, MakerID, ModelID, Location, Price, Year FROM tbl_cars LIMIT 3";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<strong>CarID:</strong> " . $row['CarID'] . " | ";
        echo "<strong>MakerID:</strong> " . $row['MakerID'] . " | ";
        echo "<strong>ModelID:</strong> " . $row['ModelID'] . " | ";
        echo "<strong>Year:</strong> " . $row['Year'] . "<br>";
    }
}

mysqli_close($conn);
?>
