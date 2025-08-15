<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

include __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $query = "SELECT id, maker_name FROM tbl_makers ORDER BY maker_name ASC";

    if (!empty($conn)) {    
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
                echo json_encode($data);
            } else {
                echo json_encode([]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>
