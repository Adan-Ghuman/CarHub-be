<?php
include "../config/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stats = [
            'totalUsers' => 0,
            'totalWorkshops' => 0,
            'totalCars' => 0,
            'pendingWorkshops' => 0
        ];

        // Count total users
        $userQuery = "SELECT COUNT(*) as count FROM users";
        $userResult = mysqli_query($conn, $userQuery);
        if ($userResult) {
            $userData = mysqli_fetch_assoc($userResult);
            $stats['totalUsers'] = (int)$userData['count'];
        }

        // Count total workshops (approved)
        $workshopQuery = "SELECT COUNT(*) as count FROM workshops WHERE status = 'active'";
        $workshopResult = mysqli_query($conn, $workshopQuery);
        if ($workshopResult) {
            $workshopData = mysqli_fetch_assoc($workshopResult);
            $stats['totalWorkshops'] = (int)$workshopData['count'];
        }

        // Count pending workshops
        $pendingQuery = "SELECT COUNT(*) as count FROM workshops WHERE status = 'pending'";
        $pendingResult = mysqli_query($conn, $pendingQuery);
        if ($pendingResult) {
            $pendingData = mysqli_fetch_assoc($pendingResult);
            $stats['pendingWorkshops'] = (int)$pendingData['count'];
        }

        // Count total cars
        $carQuery = "SELECT COUNT(*) as count FROM cars";
        $carResult = mysqli_query($conn, $carQuery);
        if ($carResult) {
            $carData = mysqli_fetch_assoc($carResult);
            $stats['totalCars'] = (int)$carData['count'];
        }

        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch statistics: ' . $e->getMessage()
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
