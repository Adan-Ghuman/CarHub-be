<?php
include "../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // Get workshop ID from request
        $workshopId = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $workshopId = isset($data['workshopId']) ? (int)$data['workshopId'] : null;
        } else {
            $workshopId = isset($_GET['workshopId']) ? (int)$_GET['workshopId'] : null;
        }
        
        if (!$workshopId) {
            throw new Exception('Workshop ID is required');
        }
        
        // Query to get workshop details with aggregated data
        $query = "SELECT w.*, 
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
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $workshopId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
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
            
            // Get operating hours if available
            $hoursQuery = "SELECT * FROM workshop_operating_hours WHERE workshop_id = ? ORDER BY day_of_week";
            $hoursStmt = mysqli_prepare($conn, $hoursQuery);
            mysqli_stmt_bind_param($hoursStmt, "i", $workshopId);
            mysqli_stmt_execute($hoursStmt);
            $hoursResult = mysqli_stmt_get_result($hoursStmt);
            $workshop['operating_hours'] = mysqli_fetch_all($hoursResult, MYSQLI_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $workshop
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Workshop not found'
            ]);
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
