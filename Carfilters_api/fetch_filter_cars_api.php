<?php

include "../config.php";


$data = json_decode(file_get_contents("php://input"), true);

$priceMin = $data['priceMin'] ?? null;
$priceMax = $data['priceMax'] ?? null;
$mileageMin = $data['mileageMin'] ?? null;
$mileageMax = $data['mileageMax'] ?? null;
$variant = $data['variant'] ?? null;
$condition = $data['condition'] ?? null;
$location = $data['location'] ?? null;
$availabilityStatus = $data['availabilityStatus'] ?? null;


$filterQuery = "SELECT * FROM cars WHERE 1";

if ($priceMin !== null && $priceMax !== null) {
    $filterQuery .= " AND Price BETWEEN $priceMin AND $priceMax";
}

if ($mileageMin !== null && $mileageMax !== null) {
    $filterQuery .= " AND Mileage BETWEEN $mileageMin AND $mileageMax";
}

if ($variant !== null) {
    $filterQuery .= " AND Variant = '$variant'";
}

if ($condition !== null) {
    $filterQuery .= " AND carCondition = '$condition'";
}

if ($location !== null) {
    $filterQuery .= " AND Location = '$location'";
}

if ($availabilityStatus !== null) {
    $filterQuery .= " AND carStatus = '$availabilityStatus'";
}

// Execute the query
$result = mysqli_query($conn, $filterQuery);

if ($result) {
    // Fetch the filtered data as an associative array
    $filteredCarsData = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Send the filtered data as JSON
    header('Content-Type: application/json');
    echo json_encode($filteredCarsData);
} else {
    // Handle the case when filtering fails
    $response = array('success' => false, 'message' => 'Failed to apply filters');
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the database connection
mysqli_close($conn);
?>
