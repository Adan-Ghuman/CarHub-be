<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$user_id = (int)$input['user_id'];
$action = $input['action'];

try {
    $pdo->beginTransaction();
    
    switch ($action) {
        case 'activate':
            $stmt = $pdo->prepare("UPDATE users SET is_verified = '1', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = 'User activated successfully';
            break;
            
        case 'deactivate':
            $stmt = $pdo->prepare("UPDATE users SET is_verified = '0', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = 'User deactivated successfully';
            break;
            
        case 'delete':
            // First, delete related data
            $pdo->prepare("DELETE FROM cars WHERE SellerID = ?")->execute([$user_id]);
            $pdo->prepare("DELETE FROM workshops WHERE user_id = ?")->execute([$user_id]);
            $pdo->prepare("DELETE FROM workshop_bookings WHERE user_id = ?")->execute([$user_id]);
            $pdo->prepare("DELETE FROM workshop_reviews WHERE user_id = ?")->execute([$user_id]);
            
            // Then delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = 'User deleted successfully';
            break;
            
            
        case 'demote':
            $stmt = $pdo->prepare("UPDATE users SET role = 'customer', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = 'User demoted successfully';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    if ($stmt->rowCount() === 0 && $action !== 'delete') {
        throw new Exception('User not found or no changes made');
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("User management error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
