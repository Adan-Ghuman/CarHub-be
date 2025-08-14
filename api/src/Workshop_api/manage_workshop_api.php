<?php
include __DIR__ . "/../../config/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['workshop_id']) || !isset($data['action'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Workshop ID and action are required'
        ]);
        exit;
    }

    $workshopId = mysqli_real_escape_string($conn, $data['workshop_id']);
    $action = mysqli_real_escape_string($conn, $data['action']);

    try {
        switch ($action) {
            case 'update':
                // Get workshop fields
                $workshopName = isset($data['workshop_name']) ? mysqli_real_escape_string($conn, $data['workshop_name']) : '';
                $ownerName = isset($data['owner_name']) ? mysqli_real_escape_string($conn, $data['owner_name']) : '';
                $email = isset($data['email']) ? mysqli_real_escape_string($conn, $data['email']) : '';
                $phone = isset($data['phone']) ? mysqli_real_escape_string($conn, $data['phone']) : '';
                $address = isset($data['address']) ? mysqli_real_escape_string($conn, $data['address']) : '';
                $city = isset($data['city']) ? mysqli_real_escape_string($conn, $data['city']) : '';
                $description = isset($data['description']) ? mysqli_real_escape_string($conn, $data['description']) : '';
                $specialization = isset($data['specialization']) ? mysqli_real_escape_string($conn, $data['specialization']) : '';
                
                // Build update fields array
                $updateFields = [];
                if (!empty($workshopName)) $updateFields[] = "name = '$workshopName'";
                if (!empty($ownerName)) $updateFields[] = "owner_name = '$ownerName'";
                if (!empty($email)) $updateFields[] = "email = '$email'";
                if (!empty($phone)) $updateFields[] = "phone = '$phone'";
                if (!empty($address)) $updateFields[] = "address = '$address'";
                if (!empty($city)) $updateFields[] = "city = '$city'";
                if (!empty($description)) $updateFields[] = "description = '$description'";
                if (!empty($specialization)) $updateFields[] = "specialization = '$specialization'";
                
                // If no fields to update, return early
                if (empty($updateFields)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'No changes to update'
                    ]);
                    exit;
                }
                
                $query = "UPDATE workshops SET " . implode(", ", $updateFields) . " WHERE id = '$workshopId'";
                $message = 'Workshop updated successfully';
                break;
                
            case 'deactivate':
                $query = "UPDATE workshops SET status = 'inactive' WHERE id = '$workshopId'";
                $message = 'Workshop deactivated successfully';
                break;
                
            case 'reactivate':
                $query = "UPDATE workshops SET status = 'active' WHERE id = '$workshopId'";
                $message = 'Workshop reactivated successfully';
                break;
                
            case 'delete':
                // First, delete related data (services, bookings, reviews)
                mysqli_query($conn, "DELETE FROM workshop_services WHERE workshop_id = '$workshopId'");
                mysqli_query($conn, "DELETE FROM service_bookings WHERE workshop_id = '$workshopId'");
                mysqli_query($conn, "DELETE FROM workshop_reviews WHERE workshop_id = '$workshopId'");
                
                // Then delete the workshop
                $query = "DELETE FROM workshops WHERE id = '$workshopId'";
                $message = 'Workshop and all related data deleted successfully';
                break;
                
            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid action. Use: update, deactivate, reactivate, or delete'
                ]);
                exit;
        }

        $result = mysqli_query($conn, $query);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => $message
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . mysqli_error($conn)
            ]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method Not Allowed'
    ]);
}

mysqli_close($conn);
?>
