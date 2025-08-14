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

    if (empty($workshopId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Workshop ID is required'
        ]);
        exit;
    }

    try {
        // Get workshop details with rating
        $query = "SELECT w.*, 
                         COUNT(wr.id) as total_reviews,
                         COALESCE(AVG(wr.rating), 0) as rating
                  FROM workshops w 
                  LEFT JOIN workshop_reviews wr ON w.id = wr.workshop_id
                  WHERE w.id = '$workshopId' AND w.status = 'active' AND w.is_verified = TRUE
                  GROUP BY w.id";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $workshop = mysqli_fetch_assoc($result);

            // Get workshop services
            $servicesQuery = "SELECT * FROM workshop_services WHERE workshop_id = '$workshopId' AND is_active = TRUE ORDER BY service_category, price ASC";
            $servicesResult = mysqli_query($conn, $servicesQuery);
            $workshop['services'] = mysqli_fetch_all($servicesResult, MYSQLI_ASSOC);

            // Get recent reviews
            $reviewsQuery = "SELECT wr.*, u.name as user_name, u.email as user_email 
                            FROM workshop_reviews wr 
                            LEFT JOIN users u ON wr.user_id = u.id 
                            WHERE wr.workshop_id = '$workshopId' 
                            ORDER BY wr.created_at DESC LIMIT 10";
            $reviewsResult = mysqli_query($conn, $reviewsQuery);
            $workshop['reviews'] = mysqli_fetch_all($reviewsResult, MYSQLI_ASSOC);

            // Format data
            $workshop['rating'] = number_format((float)$workshop['rating'], 1);
            $workshop['total_reviews'] = (int)$workshop['total_reviews'];
            
            // Get operating hours if they exist
            $workshop['operating_hours'] = isset($workshop['operating_hours']) ? 
                json_decode($workshop['operating_hours'], true) ?? [] : [];

            echo json_encode([
                'success' => true,
                'data' => $workshop
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Workshop not found or inactive'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
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
