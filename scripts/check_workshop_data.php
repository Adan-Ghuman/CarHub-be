<?php
require_once 'config.php';

echo "Workshop Data Check\n";
echo "==================\n\n";

// Check workshops
echo "1. Workshops in database:\n";
$workshops_result = $conn->query("SELECT id, name FROM workshops LIMIT 5");
if ($workshops_result && $workshops_result->num_rows > 0) {
    while ($workshop = $workshops_result->fetch_assoc()) {
        echo "  - ID: {$workshop['id']}, Name: {$workshop['name']}\n";
    }
} else {
    echo "  No workshops found\n";
}

echo "\n2. Workshop Services:\n";
$services_result = $conn->query("SELECT id, workshop_id, service_name FROM workshop_services LIMIT 5");
if ($services_result && $services_result->num_rows > 0) {
    while ($service = $services_result->fetch_assoc()) {
        echo "  - ID: {$service['id']}, Workshop: {$service['workshop_id']}, Service: {$service['service_name']}\n";
    }
} else {
    echo "  No services found\n";
}

echo "\n3. Workshop Bookings:\n";
$bookings_result = $conn->query("SELECT id, workshop_id, user_id, status FROM workshop_bookings LIMIT 5");
if ($bookings_result && $bookings_result->num_rows > 0) {
    while ($booking = $bookings_result->fetch_assoc()) {
        echo "  - ID: {$booking['id']}, Workshop: {$booking['workshop_id']}, User: {$booking['user_id']}, Status: {$booking['status']}\n";
    }
} else {
    echo "  No bookings found\n";
}

echo "\n4. Workshop Reviews:\n";
$reviews_result = $conn->query("SELECT id, booking_id, rating FROM workshop_reviews LIMIT 5");
if ($reviews_result && $reviews_result->num_rows > 0) {
    while ($review = $reviews_result->fetch_assoc()) {
        echo "  - ID: {$review['id']}, Booking: {$review['booking_id']}, Rating: {$review['rating']}\n";
    }
} else {
    echo "  No reviews found\n";
}

echo "\n5. Users:\n";
$users_result = $conn->query("SELECT id, Name FROM users LIMIT 5");
if ($users_result && $users_result->num_rows > 0) {
    while ($user = $users_result->fetch_assoc()) {
        echo "  - ID: {$user['id']}, Name: {$user['Name']}\n";
    }
} else {
    echo "  No users found\n";
}
?>
