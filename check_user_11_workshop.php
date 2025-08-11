<?php
include 'config.php';

echo "Checking User 11 workshop data..." . PHP_EOL;

// Check user
$stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$user_id = 11;
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
echo "User 11: " . print_r($user, true) . PHP_EOL;

// Check if user has workshop
$stmt = $conn->prepare('SELECT * FROM workshops WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$workshop = $result->fetch_assoc();
echo "Workshop for User 11: " . print_r($workshop, true) . PHP_EOL;

// Check all workshops
$result = $conn->query('SELECT id, name, user_id FROM workshops ORDER BY id');
echo "All workshops:" . PHP_EOL;
while($w = $result->fetch_assoc()) {
    echo "Workshop " . $w['id'] . ": " . $w['name'] . " (User " . $w['user_id'] . ")" . PHP_EOL;
}

// Check if User 11 should have workshop_id field
echo "\nChecking if User 11 has workshop_id field..." . PHP_EOL;
if (isset($user['workshop_id'])) {
    echo "User has workshop_id: " . $user['workshop_id'] . PHP_EOL;
} else {
    echo "User does NOT have workshop_id field" . PHP_EOL;
}
?>
