<?php
include "../config/config.php";

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $userId = isset($data['userId']) ? mysqli_real_escape_string($conn, $data['userId']) : 
              (isset($data['user_id']) ? mysqli_real_escape_string($conn, $data['user_id']) : '');
    $workshopId = isset($data['workshopId']) ? mysqli_real_escape_string($conn, $data['workshopId']) : '';
    $status = isset($data['status']) ? mysqli_real_escape_string($conn, $data['status']) : '';
    $limit = isset($data['limit']) ? (int)$data['limit'] : 50;
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

    try {
        // Build query based on parameters
        $query = "SELECT wb.*, 
                         w.name as workshop_name, w.address as workshop_address, w.city as workshop_city, w.phone as workshop_phone,
                         ws.service_name, ws.service_category, ws.price, ws.duration,
                         u.Name as user_name, u.Email as user_email, u.PhoneNumber as user_phone,
                         wr.id as review_id, wr.rating as review_rating, wr.review_text
                  FROM workshop_bookings wb
                  LEFT JOIN workshops w ON wb.workshop_id = w.id
                  LEFT JOIN workshop_services ws ON wb.service_id = ws.id
                  LEFT JOIN users u ON wb.user_id = u.id
                  LEFT JOIN workshop_reviews wr ON wb.id = wr.booking_id AND wb.user_id = wr.user_id
                  WHERE 1=1";
        
        // Add filters
        if (!empty($userId)) {
            $query .= " AND wb.user_id = '$userId'";
        }
        
        if (!empty($workshopId)) {
            $query .= " AND wb.workshop_id = '$workshopId'";
        }
        
        if (!empty($status)) {
            $query .= " AND wb.status = '$status'";
        }
        
        $query .= " ORDER BY wb.created_at DESC LIMIT $limit OFFSET $offset";

        $result = mysqli_query($conn, $query);

        if ($result) {
            $bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);

            // Format booking data
            foreach ($bookings as &$booking) {
                $booking['price'] = number_format((float)$booking['price'], 2);
                $booking['booking_datetime'] = $booking['booking_date'] . ' ' . $booking['booking_time'];
                
                // Add review information
                $booking['has_review'] = !empty($booking['review_id']);
                if ($booking['has_review']) {
                    $booking['my_rating'] = (int)$booking['review_rating'];
                    $booking['my_review_text'] = $booking['review_text'];
                } else {
                    $booking['my_rating'] = null;
                    $booking['my_review_text'] = null;
                }
                
                // Remove raw review fields to keep response clean
                unset($booking['review_id'], $booking['review_rating'], $booking['review_text']);
                
                // Add status badge color for frontend
                switch($booking['status']) {
                    case 'pending':
                        $booking['status_color'] = '#FFA500';
                        break;
                    case 'confirmed':
                        $booking['status_color'] = '#28A745';
                        break;
                    case 'completed':
                        $booking['status_color'] = '#007BFF';
                        break;
                    case 'cancelled':
                        $booking['status_color'] = '#DC3545';
                        break;
                    default:
                        $booking['status_color'] = '#6C757D';
                }
            }

            // Get total count for pagination
            $countQuery = str_replace("SELECT wb.*, w.name as workshop_name, w.address as workshop_address, w.city as workshop_city, w.phone as workshop_phone, ws.service_name, ws.service_category, ws.price, ws.duration, u.Name as user_name, u.Email as user_email, u.PhoneNumber as user_phone FROM workshop_bookings wb LEFT JOIN workshops w ON wb.workshop_id = w.id LEFT JOIN workshop_services ws ON wb.service_id = ws.id LEFT JOIN users u ON wb.user_id = u.id", "SELECT COUNT(*) as total FROM workshop_bookings wb", $query);
            $countQuery = str_replace(" ORDER BY wb.created_at DESC LIMIT $limit OFFSET $offset", "", $countQuery);
            
            $countResult = mysqli_query($conn, $countQuery);
            $totalCount = 0;
            if ($countResult) {
                $countRow = mysqli_fetch_assoc($countResult);
                $totalCount = isset($countRow['total']) ? $countRow['total'] : 0;
            }

            echo json_encode([
                'success' => true,
                'data' => $bookings,
                'count' => count($bookings),
                'total' => (int)$totalCount,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'hasMore' => ($offset + count($bookings)) < $totalCount
                ]
            ]);
        } else {
            throw new Exception('Failed to fetch bookings: ' . mysqli_error($conn));
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
