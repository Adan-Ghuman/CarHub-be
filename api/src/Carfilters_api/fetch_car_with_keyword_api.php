<?php

include __DIR__ . "/../../config/config.php";


$data = json_decode(file_get_contents("php://input"), true);


$keywords = $data['keywords'];

// array to store the results
$searchResults = array();


$keywordArray = explode(' ', $keywords);

// Fetch and append results for each keyword
foreach ($keywordArray as $keyword) {
    $searchResultsForKeyword = array();

    // Build the SQL query for the current keyword
    $searchQueryForKeyword = "SELECT * FROM cars WHERE carStatus IN ('active', 'Active') AND 
        (LOWER(Description) LIKE LOWER('%$keyword%') OR 
         LOWER(Variant) LIKE LOWER('%$keyword%') OR 
         LOWER(FuelType) LIKE LOWER('%$keyword%') OR 
         LOWER(carCondition) LIKE LOWER('%$keyword%') OR 
         MakerID LIKE ('%$keyword%') OR 
         ModelID LIKE ('%$keyword%') OR
         LOWER(Title) LIKE LOWER('%$keyword%'))";

    // Execute the query
    $resultForKeyword = mysqli_query($conn, $searchQueryForKeyword);

    if ($resultForKeyword) {
        // Fetch the data as an associative array
        $searchResultsForKeyword = mysqli_fetch_all($resultForKeyword, MYSQLI_ASSOC);
    }

    // Fetch and append image URLs for each car
    foreach ($searchResultsForKeyword as &$car) {
        $carID = $car['CarID'];
        $fetchImagesQuery = "SELECT ImageUrl FROM carimages WHERE CarID = '$carID'";
        $imageResult = mysqli_query($conn, $fetchImagesQuery);
        $imageUrls = mysqli_fetch_all($imageResult, MYSQLI_ASSOC);

        // Add the image URLs to the car data
        $car['ImageUrls'] = $imageUrls;
    }

    // Merge the results for the current keyword with the overall results
    $searchResults = array_merge($searchResults, $searchResultsForKeyword);
}

// Remove duplicates from the overall results
$searchResults = array_values(array_unique($searchResults, SORT_REGULAR));

// Send the data as JSON
header('Content-Type: application/json');
echo json_encode($searchResults);

// Close the database connection
mysqli_close($conn);
?>
