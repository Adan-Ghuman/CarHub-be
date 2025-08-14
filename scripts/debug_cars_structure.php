<?php
// Debug the exact data structure returned by the cars API

include 'config.php';

echo "<h3>Cars Table Structure and Sample Data</h3>";

// Check the structure of the cars table
$query = "DESCRIBE cars";
$result = mysqli_query($conn, $query);

echo "<h4>Cars Table Columns:</h4>";
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<strong>" . $row['Field'] . "</strong>: " . $row['Type'] . "<br>";
    }
}

echo "<h4>Sample Car Data (First 3 records):</h4>";
$query = "SELECT * FROM cars LIMIT 3";
$result = mysqli_query($conn, $query);

if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        foreach($row as $key => $value) {
            echo "<strong>$key:</strong> $value<br>";
        }
        echo "</div>";
    }
}

echo "<h4>Check if MakerName and ModelName columns exist:</h4>";
$query = "SELECT CarID, MakerID, ModelID, MakerName, ModelName FROM cars LIMIT 1";
$result = @mysqli_query($conn, $query);

if ($result) {
    echo "✅ Both MakerName and ModelName columns exist in cars table<br>";
    $row = mysqli_fetch_assoc($result);
    print_r($row);
} else {
    echo "❌ MakerName and/or ModelName columns do not exist. Error: " . mysqli_error($conn) . "<br>";
    echo "The API needs to join with tbl_makers and tbl_models tables<br>";
}

mysqli_close($conn);
?>
