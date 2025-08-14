<?php
include 'config.php';

echo "=== CHECKING WORKSHOP OWNERSHIP ===\n\n";

// Check workshop ownership
$result = $conn->query('SELECT w.id, w.name, w.owner_id FROM workshops w ORDER BY w.id');
if ($result) {
    $workshops = $result->fetch_all(MYSQLI_ASSOC);
    echo "Workshop ownership:\n";
    foreach($workshops as $w) {
        echo "Workshop ID: " . $w['id'] . ", Name: " . $w['name'] . ", Owner ID: " . ($w['owner_id'] ?? 'NULL') . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== CHECKING USER 11 CURRENT LOGIC ===\n\n";

// Test the current logic from WorkshopReviews
$userId = 11;
if ($userId == "11" || $userId == 11) {
    $workshopId = 3;
} else {
    // Check user's workshop_id
    $stmt = $conn->prepare('SELECT workshop_id FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $workshopId = $user['workshop_id'] ?? $userId;
}

echo "User ID 11 maps to Workshop ID: $workshopId\n";

// Check reviews for this workshop
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM reviews WHERE workshop_id = ?');
$stmt->bind_param('i', $workshopId);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];

echo "Reviews found for Workshop ID $workshopId: $count\n";

// Check reviews for workshop ID 1 (where the actual review is)
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM reviews WHERE workshop_id = 1');
$stmt->execute();
$result = $stmt->get_result();
$count1 = $result->fetch_assoc()['count'];

echo "Reviews found for Workshop ID 1: $count1\n";
?>
