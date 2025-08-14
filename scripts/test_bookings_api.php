<?php
// Test the workshop bookings API
echo "Testing Workshop Bookings API...\n\n";

$url = "http://localhost/armghan/CARHUB_PK/backend/Workshop_api/get_bookings_api.php";

$data = [
    'workshopId' => 3,
    'limit' => 10,
    'offset' => 0
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "Error: Failed to fetch data from API\n";
} else {
    echo "Raw response:\n";
    echo $result . "\n\n";
    
    $decoded = json_decode($result, true);
    if ($decoded) {
        echo "Parsed response:\n";
        print_r($decoded);
    } else {
        echo "Failed to decode JSON response\n";
    }
}
?>
