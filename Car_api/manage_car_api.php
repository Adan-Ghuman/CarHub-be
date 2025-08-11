<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['car_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$car_id = (int)$input['car_id'];
$action = $input['action'];

try {
    $pdo->beginTransaction();
    
    switch ($action) {
        case 'approve':
            $stmt = $pdo->prepare("UPDATE cars SET carStatus = 'active', updated_at = NOW() WHERE CarID = ?");
            $stmt->execute([$car_id]);
            $message = 'Car listing approved successfully';
            break;
            
        case 'reject':
            $stmt = $pdo->prepare("UPDATE cars SET carStatus = 'rejected', updated_at = NOW() WHERE CarID = ?");
            $stmt->execute([$car_id]);
            $message = 'Car listing rejected successfully';
            break;
            
        case 'feature':
            $stmt = $pdo->prepare("UPDATE cars SET is_featured = 1, updated_at = NOW() WHERE CarID = ?");
            $stmt->execute([$car_id]);
            $message = 'Car listing featured successfully';
            break;
            
        case 'unfeature':
            $stmt = $pdo->prepare("UPDATE cars SET is_featured = 0, updated_at = NOW() WHERE CarID = ?");
            $stmt->execute([$car_id]);
            $message = 'Car listing unfeatured successfully';
            break;
            
        case 'delete':
            // First, delete related data
            // $pdo->prepare("DELETE FROM favorites WHERE CarID = ?")->execute([$car_id]);
            // $pdo->prepare("DELETE FROM cars WHERE CarID = ?")->execute([$car_id]);
            $pdo->prepare("DELETE FROM carimages WHERE CarID = ?")->execute([$car_id]);
            
            // Then delete the car
            $stmt = $pdo->prepare("DELETE FROM cars WHERE CarID = ?");
            $stmt->execute([$car_id]);
            $message = 'Car listing deleted successfully';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    if ($stmt->rowCount() === 0 && $action !== 'delete') {
        throw new Exception('Car listing not found or no changes made');
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Car management error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
