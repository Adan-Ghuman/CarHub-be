<?php
include "../config/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    try {
        // Fetch all workshops for admin review (pending, active, rejected)
        $query = "SELECT 
            id,
            name,
            owner_name,
            email,
            phone,
            city,
            address,
            description,
            status,
            is_verified,
            created_at,
            updated_at
        FROM workshops 
        ORDER BY 
            CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'active' THEN 2 
                WHEN status = 'rejected' THEN 3 
            END, 
            created_at DESC";
            
        $result = mysqli_query($conn, $query);

        if ($result) {
            $workshops = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            // Count workshops by status
            $pending_count = 0;
            $active_count = 0;
            $rejected_count = 0;
            
            foreach ($workshops as $workshop) {
                switch ($workshop['status']) {
                    case 'pending':
                        $pending_count++;
                        break;
                    case 'active':
                        $active_count++;
                        break;
                    case 'rejected':
                        $rejected_count++;
                        break;
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $workshops,
                'stats' => [
                    'total' => count($workshops),
                    'pending' => $pending_count,
                    'active' => $active_count,
                    'rejected' => $rejected_count
                ]
            ]);
        } else {
            throw new Exception('Database query failed: ' . mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'data' => []
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method Not Allowed. Use GET method.'
    ]);
}

mysqli_close($conn);
?>
