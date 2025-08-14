<?php
include "../config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query to get the highest car ID
        $query = "SELECT MAX(CarID) as LastCarID FROM cars";
        $result = mysqli_query($conn, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $lastCarID = $row['LastCarID'] ? (int)$row['LastCarID'] : 0;
            
            echo json_encode([
                'success' => true,
                'LastCarID' => $lastCarID
            ]);
        } else {
            throw new Exception('Query failed: ' . mysqli_error($conn));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage(),
            'LastCarID' => 0
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed',
        'LastCarID' => 0
    ]);
}

mysqli_close($conn);
?>