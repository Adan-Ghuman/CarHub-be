<?php
include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $results = [];
    
    // Get all pending workshops
    $pending_query = "SELECT id, name, status, is_verified FROM workshops WHERE status = 'pending' OR is_verified = 0";
    $pending_result = mysqli_query($conn, $pending_query);
    
    if (mysqli_num_rows($pending_result) > 0) {
        while ($workshop = mysqli_fetch_assoc($pending_result)) {
            $workshop_id = $workshop['id'];
            $workshop_name = $workshop['name'];
            
            // Approve and activate the workshop
            $approve_query = "UPDATE workshops SET status = 'active', is_verified = 1, updated_at = NOW() WHERE id = ?";
            $approve_stmt = mysqli_prepare($conn, $approve_query);
            mysqli_stmt_bind_param($approve_stmt, "i", $workshop_id);
            
            if (mysqli_stmt_execute($approve_stmt)) {
                $results[] = "✅ Approved workshop: $workshop_name (ID: $workshop_id)";
            } else {
                $results[] = "❌ Failed to approve workshop: $workshop_name (ID: $workshop_id)";
            }
            mysqli_stmt_close($approve_stmt);
        }
    } else {
        $results[] = "No pending workshops found";
    }
    
    // Get current count of active workshops
    $active_query = "SELECT COUNT(*) as count FROM workshops WHERE status = 'active' AND is_verified = 1";
    $active_result = mysqli_query($conn, $active_query);
    $active_count = mysqli_fetch_assoc($active_result)['count'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Workshop approval process completed',
        'results' => $results,
        'active_workshops_count' => (int)$active_count
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
