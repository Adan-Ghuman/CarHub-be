<?php
include "config.php";

echo "=== Workshop Ownership Analysis ===\n";

echo "1. All workshops and their owners:\n";
$result = $conn->query("SELECT id, user_id, name, owner_name FROM workshops ORDER BY id");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "   Workshop {$row['id']}: {$row['name']} (Owner: {$row['owner_name']}, User ID: {$row['user_id']})\n";
    }
} else {
    echo "   No workshops found\n";
}

echo "\n2. All users and their details:\n";
$result = $conn->query("SELECT * FROM users LIMIT 5");
if ($result && $result->num_rows > 0) {
    $first = true;
    while ($row = $result->fetch_assoc()) {
        if ($first) {
            echo "   User columns: " . implode(', ', array_keys($row)) . "\n";
            $first = false;
        }
        echo "   User {$row['id']}: " . ($row['Name'] ?? $row['name'] ?? 'Unknown') . " (" . ($row['Email'] ?? $row['email'] ?? 'No email') . ")\n";
    }
} else {
    echo "   No users found\n";
}

echo "\n3. Bookings distribution:\n";
$result = $conn->query("SELECT workshop_id, COUNT(*) as count FROM workshop_bookings GROUP BY workshop_id ORDER BY workshop_id");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "   Workshop {$row['workshop_id']}: {$row['count']} bookings\n";
    }
}

echo "\n4. The Solution:\n";
echo "Based on the data:\n";
echo "- Workshop 3 (Zawar HAIDER) has 8 bookings\n";
echo "- The user expects to see 8 bookings\n";
echo "- Therefore, the user should be associated with Workshop 3\n";

$result = $conn->query("SELECT user_id FROM workshops WHERE id = 3");
if ($result && $result->num_rows > 0) {
    $workshop3 = $result->fetch_assoc();
    echo "- Workshop 3 is owned by User ID: {$workshop3['user_id']}\n";
    echo "- The mobile app user should log in with User ID {$workshop3['user_id']} to see 8 bookings\n";
} else {
    echo "- Could not find Workshop 3 ownership info\n";
}

echo "\n5. Fix Instructions:\n";
echo "The user should either:\n";
echo "A) Log in with the account that owns Workshop 3, OR\n";
echo "B) Update the database to associate their current user account with Workshop 3\n";

$conn->close();
?>
