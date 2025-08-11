<?php
include 'config.php';

// Test the actual API endpoint with different workshop IDs
echo "=== Testing get_bookings_api.php ===\n";

// Test with all workshop IDs
$workshops = [1, 2, 3];

foreach ($workshops as $workshopId) {
    echo "\n--- Testing Workshop ID: $workshopId ---\n";
    
    // Simulate the API request
    $requestData = [
        'workshop_id' => $workshopId,
        'limit' => 50,
        'offset' => 0
    ];
    
    // Include the actual API file and capture output
    ob_start();
    $_POST = $requestData;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Simulate the API call manually
    $workshop_id = $requestData['workshop_id'];
    $limit = $requestData['limit'] ?? 50;
    $offset = $requestData['offset'] ?? 0;
    
    $stmt = $conn->prepare("
        SELECT b.*, 
               u.full_name as user_name, 
               u.phone as user_phone,
               ws.service_name
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN workshop_services ws ON b.service_id = ws.id
        WHERE b.workshop_id = ?
        ORDER BY b.created_at DESC 
        LIMIT $limit OFFSET $offset
    ");
    
    if ($stmt) {
        $stmt->bind_param('i', $workshop_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        echo "Workshop $workshopId: " . count($bookings) . " bookings\n";
        
        if (count($bookings) > 0) {
            echo "Sample bookings:\n";
            foreach (array_slice($bookings, 0, 3) as $booking) {
                echo "  - ID: " . $booking['id'] . ", Status: " . $booking['status'] . ", User: " . ($booking['user_name'] ?? 'N/A') . "\n";
            }
        }
        
        $stmt->close();
    } else {
        echo "Failed to prepare statement\n";
    }
}

// Now let's check what happens if we don't filter by workshop_id
echo "\n--- Testing WITHOUT workshop_id filter (ALL bookings) ---\n";
$result = $conn->query("
    SELECT b.*, 
           u.full_name as user_name, 
           u.phone as user_phone,
           ws.service_name
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN workshop_services ws ON b.service_id = ws.id
    ORDER BY b.created_at DESC 
    LIMIT 50
");

if ($result) {
    $allBookings = [];
    while ($row = $result->fetch_assoc()) {
        $allBookings[] = $row;
    }
    
    echo "Total bookings in database: " . count($allBookings) . "\n";
    
    // Group by workshop_id
    $byWorkshop = [];
    foreach ($allBookings as $booking) {
        $wid = $booking['workshop_id'] ?? 'null';
        $byWorkshop[$wid] = ($byWorkshop[$wid] ?? 0) + 1;
    }
    
    echo "Bookings by workshop:\n";
    foreach ($byWorkshop as $wid => $count) {
        echo "  Workshop $wid: $count bookings\n";
    }
}

$conn->close();
?>
