<?php
include __DIR__ . '/../../config/config.php';



// Fetch cities data directly without a function
$query = "SELECT * FROM locations";
$result = mysqli_query($conn, $query);

$citiesData = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $citiesData[] = $row;
    }
    // Output the data as JSON
    echo json_encode($citiesData);
} else {
    // Output an error message if the query fails
    echo json_encode(['error' => mysqli_error($conn)]);
}

// Close the database connection
mysqli_close($conn);
?>
