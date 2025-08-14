<?php
// Test the new fetch_models_with_id_api.php endpoint

echo "<h3>Testing fetch_models_with_id_api.php</h3>";

// Test data
$testModelID = 1; // Change this to a valid model ID

$url = 'http://localhost/armghan/CARHUB_PK/backend/Carfilters_api/fetch_models_with_id_api.php';
$data = json_encode(['id' => $testModelID]);

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<strong>Request:</strong> Model ID = " . $testModelID . "<br>";
echo "<strong>Response:</strong> " . $result . "<br><br>";

// Test another ID
$testModelID = 2;
$data = json_encode(['id' => $testModelID]);
$options['http']['content'] = $data;
$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<strong>Request:</strong> Model ID = " . $testModelID . "<br>";
echo "<strong>Response:</strong> " . $result . "<br>";

?>
