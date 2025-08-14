<?php
include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $workshopId = isset($data['workshopId']) ? mysqli_real_escape_string($conn, $data['workshopId']) : '';
    $isActive = isset($data['isActive']) ? $data['isActive'] : null;

    if (empty($workshopId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Workshop ID is required'
        ]);
        exit;
    }

    try {
        // Verify workshop exists
        $workshopQuery = "SELECT name FROM workshops WHERE id = '$workshopId'";
        $workshopResult = mysqli_query($conn, $workshopQuery);

        if (!$workshopResult || mysqli_num_rows($workshopResult) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Workshop not found'
            ]);
            exit;
        }

        // Build query to get services
        $query = "SELECT * FROM workshop_services WHERE workshop_id = '$workshopId'";
        
        if ($isActive !== null) {
            $activeFilter = $isActive ? 'TRUE' : 'FALSE';
            $query .= " AND is_active = $activeFilter";
        }
        
        $query .= " ORDER BY service_category ASC, service_name ASC";

        $result = mysqli_query($conn, $query);

        if ($result) {
            $services = mysqli_fetch_all($result, MYSQLI_ASSOC);

            // Format services data
            foreach ($services as &$service) {
                // Keep price as numeric value for frontend formatting
                $service['price'] = (float)$service['price'];
                $service['is_active'] = (bool)$service['is_active'];
                
                // Add formatted created date
                $service['created_date'] = date('M j, Y', strtotime($service['created_at']));
            }
            unset($service); // Important: unset the reference to avoid issues

            // Group services by category for better organization
            $groupedServices = [];
            foreach ($services as $service) {
                $category = $service['service_category'];
                if (!isset($groupedServices[$category])) {
                    $groupedServices[$category] = [];
                }
                $groupedServices[$category][] = $service;
            }

            // Get service statistics
            $statsQuery = "SELECT 
                          COUNT(*) as total_services,
                          COUNT(CASE WHEN is_active = TRUE THEN 1 END) as active_services,
                          COUNT(CASE WHEN is_active = FALSE THEN 1 END) as inactive_services,
                          COUNT(DISTINCT service_category) as categories,
                          MIN(price) as min_price,
                          MAX(price) as max_price,
                          AVG(price) as avg_price
                          FROM workshop_services 
                          WHERE workshop_id = '$workshopId'";
            
            $statsResult = mysqli_query($conn, $statsQuery);
            $stats = $statsResult ? mysqli_fetch_assoc($statsResult) : null;
            
            if ($stats) {
                // Keep stats as numeric values for frontend formatting
                $stats['min_price'] = (float)$stats['min_price'];
                $stats['max_price'] = (float)$stats['max_price'];
                $stats['avg_price'] = (float)$stats['avg_price'];
            }

            echo json_encode([
                'success' => true,
                'data' => $services,
                'grouped_services' => $groupedServices,
                'count' => count($services),
                'stats' => $stats
            ]);
        } else {
            throw new Exception('Failed to fetch services: ' . mysqli_error($conn));
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
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
}

mysqli_close($conn);
?>
