<?php
include __DIR__ . "/../../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    $city = isset($data['city']) ? mysqli_real_escape_string($conn, $data['city']) : '';
    $limit = isset($data['limit']) ? (int)$data['limit'] : 50;
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;
    
    // Check if this is an admin request (both GET and POST)
    $isAdmin = (isset($_GET['admin']) && $_GET['admin'] === 'true') || 
               (isset($data['admin']) && $data['admin'] === 'true') ||
               (isset($data['admin']) && $data['admin'] === true);

    try {
        if ($isAdmin) {
            // Admin query - get all workshops with additional info (simplified)
            $query = "SELECT w.*, 
                             0 as reviews_count,
                             0 as services_count,
                             0 as average_rating
                      FROM workshops w 
                      ORDER BY w.created_at DESC 
                      LIMIT $limit OFFSET $offset";
        } else {
            // Regular query for active and verified workshops
            $query = "SELECT w.*, 
                             COUNT(wr.id) as total_reviews,
                             COALESCE(AVG(wr.rating), 0) as rating
                      FROM workshops w 
                      LEFT JOIN workshop_reviews wr ON w.id = wr.workshop_id
                      WHERE w.status = 'active' AND w.is_verified = TRUE";
            
            if (!empty($city)) {
                $query .= " AND w.city = '$city'";
            }
            
            $query .= " GROUP BY w.id ORDER BY rating DESC, w.created_at DESC LIMIT $limit OFFSET $offset";
        }

        $result = mysqli_query($conn, $query);

        if ($result) {
            $workshops = mysqli_fetch_all($result, MYSQLI_ASSOC);

            // Fetch services for each workshop (only for non-admin requests)
            if (!$isAdmin) {
                foreach ($workshops as &$workshop) {
                    $workshopId = $workshop['id'];
                    
                    // Get services
                    $servicesQuery = "SELECT * FROM workshop_services WHERE workshop_id = '$workshopId' AND is_active = TRUE ORDER BY price ASC";
                    $servicesResult = mysqli_query($conn, $servicesQuery);
                    $workshop['services'] = mysqli_fetch_all($servicesResult, MYSQLI_ASSOC);
                    
                    // Format rating
                    $workshop['rating'] = number_format((float)$workshop['rating'], 1);
                    $workshop['total_reviews'] = (int)$workshop['total_reviews'];
                }
            } else {
                // Format admin data
                foreach ($workshops as &$workshop) {
                    $workshop['average_rating'] = $workshop['average_rating'] ? number_format((float)$workshop['average_rating'], 1) : null;
                    $workshop['reviews_count'] = (int)$workshop['reviews_count'];
                    $workshop['services_count'] = (int)$workshop['services_count'];
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $workshops,
                'count' => count($workshops)
            ]);
        } else {
            throw new Exception('Failed to fetch workshops: ' . mysqli_error($conn));
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
