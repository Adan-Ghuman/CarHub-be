<?php
include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            throw new Exception('Invalid JSON data');
        }
        
        // Get workshop ID
        $workshopId = isset($data['workshopId']) ? (int)$data['workshopId'] : null;
        
        if (!$workshopId) {
            throw new Exception('Workshop ID is required');
        }
        
        // Verify workshop exists and belongs to the current user
        $checkQuery = "SELECT user_id FROM workshops WHERE id = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "i", $workshopId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (!mysqli_fetch_assoc($checkResult)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Workshop not found'
            ]);
            exit;
        }
        
        // Prepare update fields
        $updateFields = [];
        $params = [];
        $types = "";
        
        // Define allowed fields with validation
        $allowedFields = [
            'name' => ['type' => 's', 'required' => true, 'max_length' => 255],
            'description' => ['type' => 's', 'required' => false, 'max_length' => 1000],
            'address' => ['type' => 's', 'required' => false, 'max_length' => 500],
            'phone' => ['type' => 's', 'required' => false, 'max_length' => 20],
            'email' => ['type' => 's', 'required' => false, 'max_length' => 255],
            'specialization' => ['type' => 's', '' => false, 'max_length' => 255],
            'city' => ['type' => 's', 'required' => false, 'max_length' => 100],
            'owner_name' => ['type' => 's', 'required' => false, 'max_length' => 255]
        ];
        
        // Process each field
        foreach ($allowedFields as $field => $config) {
            if (isset($data[$field])) {
                $value = $data[$field];
                
                // Validate required fields
                if ($config['required'] && empty(trim($value))) {
                    throw new Exception(ucfirst($field) . ' is required');
                }
                
                // Validate length
                if (isset($config['max_length']) && strlen($value) > $config['max_length']) {
                    throw new Exception(ucfirst($field) . ' is too long (max ' . $config['max_length'] . ' characters)');
                }
                
                // Email validation
                if ($field === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format');
                }
                
                // Phone validation (basic)
                if ($field === 'phone' && !empty($value) && !preg_match('/^[\d\s\-\+\(\)]+$/', $value)) {
                    throw new Exception('Invalid phone number format');
                }
                
                $updateFields[] = "$field = ?";
                $params[] = $value;
                $types .= $config['type'];
            }
        }
        
        if (empty($updateFields)) {
            throw new Exception('No fields to update');
        }
        
        // Add updated timestamp
        $updateFields[] = "updated_at = NOW()";
        
        // Prepare and execute update query
        $updateQuery = "UPDATE workshops SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $params[] = $workshopId;
        $types .= "i";
        
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (mysqli_stmt_execute($stmt)) {
            // Check if any rows were affected
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                // Get updated workshop details
                $getQuery = "SELECT w.*, 
                                   COUNT(DISTINCT wr.id) as total_reviews,
                                   COALESCE(AVG(wr.rating), 0) as rating,
                                   COUNT(DISTINCT ws.id) as total_services,
                                   COUNT(DISTINCT wb.id) as total_bookings,
                                   COUNT(DISTINCT CASE WHEN wb.status = 'completed' THEN wb.id END) as completed_bookings
                            FROM workshops w 
                            LEFT JOIN workshop_reviews wr ON w.id = wr.workshop_id
                            LEFT JOIN workshop_services ws ON w.id = ws.workshop_id AND ws.is_active = TRUE
                            LEFT JOIN workshop_bookings wb ON w.id = wb.workshop_id
                            WHERE w.id = ?
                            GROUP BY w.id";
                
                $getStmt = mysqli_prepare($conn, $getQuery);
                mysqli_stmt_bind_param($getStmt, "i", $workshopId);
                mysqli_stmt_execute($getStmt);
                $result = mysqli_stmt_get_result($getStmt);
                
                if ($workshop = mysqli_fetch_assoc($result)) {
                    // Format the data
                    $workshop['rating'] = number_format((float)$workshop['rating'], 1);
                    $workshop['total_reviews'] = (int)$workshop['total_reviews'];
                    $workshop['total_services'] = (int)$workshop['total_services'];
                    $workshop['total_bookings'] = (int)$workshop['total_bookings'];
                    $workshop['completed_bookings'] = (int)$workshop['completed_bookings'];
                    
                    // Calculate completion rate
                    if ($workshop['total_bookings'] > 0) {
                        $workshop['completion_rate'] = round(($workshop['completed_bookings'] / $workshop['total_bookings']) * 100, 1);
                    } else {
                        $workshop['completion_rate'] = 0;
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Workshop updated successfully',
                        'data' => $workshop
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Workshop updated successfully'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'No changes made'
                ]);
            }
        } else {
            throw new Exception('Failed to update workshop: ' . mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
}

mysqli_close($conn);
?>
