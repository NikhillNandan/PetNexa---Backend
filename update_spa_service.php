<?php
/**
 * update_spa_service.php - Update existing spa service
 * Deploy to: htdocs/petnexa_API/update_spa_service.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $duration_minutes = isset($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : 0;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    
    if ($user_id <= 0 || $service_id <= 0 || empty($service_name) || $price <= 0) {
        $response['error'] = true;
        $response['message'] = 'User ID, service ID, service name, and price are required';
        echo json_encode($response);
        exit;
    }
    
    // Get spa_id
    $spa_query = $conn->prepare("SELECT spa_id FROM spa_profiles WHERE user_id = ?");
    $spa_query->bind_param("i", $user_id);
    $spa_query->execute();
    $spa_result = $spa_query->get_result();
    
    if ($spa_result->num_rows == 0) {
        $response['error'] = true;
        $response['message'] = 'Spa profile not found';
        echo json_encode($response);
        $spa_query->close();
        exit;
    }
    
    $spa_row = $spa_result->fetch_assoc();
    $spa_id = $spa_row['spa_id'];
    $spa_query->close();
    
    // Update service (ensure it belongs to this spa)
    $stmt = $conn->prepare("UPDATE spa_services SET service_name = ?, description = ?, duration_minutes = ?, price = ? WHERE service_id = ? AND spa_id = ?");
    $stmt->bind_param("ssdiii", $service_name, $description, $duration_minutes, $price, $service_id, $spa_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['error'] = false;
            $response['message'] = 'Service updated successfully';
        } else {
            $response['error'] = true;
            $response['message'] = 'Service not found or no changes made';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Failed to update service: ' . $stmt->error;
    }
    
    $stmt->close();
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
