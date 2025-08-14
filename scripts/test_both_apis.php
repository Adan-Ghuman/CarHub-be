<?php
// Test both maker and model APIs with ID

echo "<h3>Testing fetch_makers_with_id_api.php</h3>";

// Test makers API
$testMakerID = 1;
$url = 'http://localhost/armghan/CARHUB_PK/backend/Carfilters_api/fetch_makers_with_id_api.php';
$data = json_encode(['id' => $testMakerID]);

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<strong>Maker ID = " . $testMakerID . ":</strong> " . $result . "<br><br>";

echo "<h3>Testing fetch_models_with_id_api.php</h3>";

// Test models API
$testModelID = 1;
$url = 'http://localhost/armghan/CARHUB_PK/backend/Carfilters_api/fetch_models_with_id_api.php';
$data = json_encode(['id' => $testModelID]);
$options['http']['content'] = $data;
$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<strong>Model ID = " . $testModelID . ":</strong> " . $result . "<br><br>";

// Check what's in the cars table to get some real IDs
echo "<h3>Sample Car Data (to get real MakerID and ModelID)</h3>";
include 'config.php';
$query = "SELECT CarID, MakerID, ModelID, Location, Price FROM tbl_cars LIMIT 5";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "CarID: " . $row['CarID'] . 
             ", MakerID: " . $row['MakerID'] . 
             ", ModelID: " . $row['ModelID'] . 
             ", Location: " . $row['Location'] . 
             ", Price: " . $row['Price'] . "<br>";
    }
} else {
    echo "No cars found";
}

mysqli_close($conn);
?>
