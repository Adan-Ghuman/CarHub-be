<?php

include "../config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jsonInput = file_get_contents('php://input');

    // Validate JSON data
    $requestData = json_decode($jsonInput, true);
    if ($requestData === null || !isset($requestData['userID'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data or missing userID']);
        exit;
    }

    // Get the user ID to count user ads
    $userID = $requestData['userID'];

    try {
        // Query to count total ads by user (including all statuses)
        $countAllQuery = "SELECT COUNT(*) as totalAds FROM cars WHERE SellerID = '$userID'";
        $countAllResult = mysqli_query($conn, $countAllQuery);
        
        // Query to count active ads by user
        $countActiveQuery = "SELECT COUNT(*) as activeAds FROM cars WHERE SellerID = '$userID' AND carStatus IN ('active', 'Active')";
        $countActiveResult = mysqli_query($conn, $countActiveQuery);

        if ($countAllResult && $countActiveResult) {
            $totalAdsData = mysqli_fetch_assoc($countAllResult);
            $activeAdsData = mysqli_fetch_assoc($countActiveResult);
            
            echo json_encode([
                'success' => true,
                'totalAds' => (int)$totalAdsData['totalAds'],
                'activeAds' => (int)$activeAdsData['activeAds'],
                'userID' => $userID
            ]);
        } else {
            throw new Exception('Query failed: ' . mysqli_error($conn));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Database error: ' . $e->getMessage(),
            'totalAds' => 0,
            'activeAds' => 0
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method Not Allowed'
    ]);
}

mysqli_close($conn);
?>
