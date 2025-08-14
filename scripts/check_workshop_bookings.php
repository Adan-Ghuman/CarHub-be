<?php
include 'config.php';

echo "=== Available Workshop IDs ===\n";
$result = $conn->query('SELECT id, name FROM workshops ORDER BY id');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - Name: " . $row['name'] . "\n";
    }
} else {
    echo "No workshops found\n";
}

echo "\n=== Bookings per Workshop ===\n";
$result = $conn->query('SELECT workshop_id, COUNT(*) as count FROM bookings GROUP BY workshop_id ORDER BY workshop_id');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Workshop " . $row['workshop_id'] . ": " . $row['count'] . " bookings\n";
    }
} else {
    echo "No bookings found\n";
}

echo "\n=== Details for Workshop with 8 bookings ===\n";
$result = $conn->query('SELECT workshop_id, COUNT(*) as count FROM bookings GROUP BY workshop_id HAVING count = 8');
$workshop = null;
if ($result && $result->num_rows > 0) {
    $workshop = $result->fetch_assoc();
    echo "Workshop ID with 8 bookings: " . $workshop['workshop_id'] . "\n";
    
    $stmt = $conn->prepare('SELECT * FROM workshops WHERE id = ?');
    $stmt->bind_param('i', $workshop['workshop_id']);
    $stmt->execute();
    $workshopResult = $stmt->get_result();
    if ($workshopDetails = $workshopResult->fetch_assoc()) {
        echo "Workshop Name: " . $workshopDetails['name'] . "\n";
        echo "Owner ID: " . $workshopDetails['owner_id'] . "\n";
    }
} else {
    echo "No workshop found with exactly 8 bookings\n";
}

// Test API call for specific workshop
echo "\n=== Testing API for Workshop with 8 bookings ===\n";
if ($workshop) {
    $workshopId = $workshop['workshop_id'];
    
    // Simulate the API call
    $limit = 50;
    $offset = 0;
    
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
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param('iii', $workshopId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    echo "API would return " . count($bookings) . " bookings for workshop " . $workshopId . "\n";
    echo "First few bookings:\n";
    foreach (array_slice($bookings, 0, 3) as $booking) {
        echo "- ID: " . $booking['id'] . ", User: " . $booking['user_name'] . ", Status: " . $booking['status'] . "\n";
    }
} else {
    echo "No workshop with 8 bookings to test\n";
}

$conn->close();
?>
