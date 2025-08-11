<?php
include "config.php";

echo "Checking User ID 11:\n";
$result = $conn->query('SELECT * FROM users WHERE id = 11');
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "User 11 exists: " . $user['Name'] . " (" . $user['Email'] . ")\n";
} else {
    echo "User 11 does NOT exist\n";
    echo "Available users:\n";
    $result = $conn->query('SELECT id, Name, Email FROM users ORDER BY id');
    while ($row = $result->fetch_assoc()) {
        echo "  User " . $row['id'] . ": " . $row['Name'] . " (" . $row['Email'] . ")\n";
    }
}

echo "\nSOLUTION OPTIONS:\n";
echo "Since the user expects to see 8 bookings from Workshop 3, we have two options:\n\n";

echo "OPTION 1: Create User 11 (if it doesn't exist)\n";
echo "This would involve creating the missing user account for Workshop 3.\n\n";

echo "OPTION 2: Update Workshop 3 to be owned by an existing user\n";
echo "Update Workshop 3's user_id to point to an existing user that the mobile app user can log in as.\n\n";

echo "OPTION 3: Update the dashboard logic\n";
echo "Modify the WorkshopOwnerDashboard to correctly determine workshop ownership.\n\n";

echo "Current Workshop->User mapping:\n";
$result = $conn->query("
    SELECT w.id as workshop_id, w.name as workshop_name, w.user_id, u.Name as user_name, u.Email as user_email
    FROM workshops w
    LEFT JOIN users u ON w.user_id = u.id
    ORDER BY w.id
");
while ($row = $result->fetch_assoc()) {
    $userInfo = $row['user_name'] ? "{$row['user_name']} ({$row['user_email']})" : "❌ USER NOT FOUND";
    echo "  Workshop {$row['workshop_id']} ({$row['workshop_name']}) -> User {$row['user_id']}: $userInfo\n";
}

$conn->close();
?>
