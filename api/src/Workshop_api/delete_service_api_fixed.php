<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/config.php';

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $service_id = $input['service_id'] ?? $_POST['service_id'] ?? null;
    $workshop_id = $input['workshop_id'] ?? $_POST['workshop_id'] ?? null;
    
    if (!$service_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Service ID is required'
        ]);
        exit;
    }
    
    // Verify service exists and belongs to the workshop
    $service_check = $conn->prepare("SELECT id, workshop_id, service_name FROM services WHERE id = ?");
    $service_check->bind_param("i", $service_id);
    $service_check->execute();
    $service_result = $service_check->get_result()->fetch_assoc();
    
    if (!$service_result) {
        echo json_encode([
            'success' => false,
            'message' => 'Service not found'
        ]);
        exit;
    }
    
    // Verify workshop ownership if workshop_id is provided
    if ($workshop_id && $service_result['workshop_id'] != $workshop_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized: Service does not belong to this workshop'
        ]);
        exit;
    }
    
    // Check if service has any pending or confirmed bookings
    $booking_check = $conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE service_id = ? AND status IN ('pending', 'confirmed')");
    $booking_check->bind_param("i", $service_id);
    $booking_check->execute();
    $booking_result = $booking_check->get_result()->fetch_assoc();
    
    if ($booking_result['booking_count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete service with active bookings. Please cancel or complete all bookings first.',
            'active_bookings' => (int)$booking_result['booking_count']
        ]);
        exit;
    }
    
    // Soft delete the service by setting is_active to 0
    $delete_sql = "UPDATE services SET is_active = 0, updated_at = NOW() WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $service_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Service deleted successfully',
            'service_name' => $service_result['service_name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete service: ' . $conn->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting service: ' . $e->getMessage()
    ]);
}
?>
