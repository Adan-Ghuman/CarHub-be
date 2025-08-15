<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get raw POST data
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    // Log the received data
    error_log("Respond to Review API - Received data: " . print_r($data, true));
    
    // Validate required fields
    if (!isset($data['review_id']) || !isset($data['workshop_response'])) {
        echo json_encode([
            "success" => false,
            "error" => "Missing required fields: review_id and workshop_response"
        ]);
        exit;
    }
    
    $review_id = (int)$data['review_id'];
    $workshop_response = trim($data['workshop_response']);
    
    if (empty($workshop_response)) {
        echo json_encode([
            "success" => false,
            "error" => "Workshop response cannot be empty"
        ]);
        exit;
    }
    
    // Create database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if review exists
    $checkStmt = $pdo->prepare("SELECT id, workshop_id FROM reviews WHERE id = ?");
    $checkStmt->execute([$review_id]);
    $review = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$review) {
        echo json_encode([
            "success" => false,
            "error" => "Review not found"
        ]);
        exit;
    }
    
    // Update the review with workshop response
    $updateStmt = $pdo->prepare("
        UPDATE reviews 
        SET workshop_response = ?, 
            response_date = CURRENT_TIMESTAMP,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    $result = $updateStmt->execute([$workshop_response, $review_id]);
    
    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Response submitted successfully",
            "data" => [
                "review_id" => $review_id,
                "workshop_response" => $workshop_response
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Failed to submit response"
        ]);
    }
    
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "An error occurred: " . $e->getMessage()
    ]);
}
?>
