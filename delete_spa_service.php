<?php
/**
 * delete_spa_service.php - Delete a spa service
 * Deploy to: htdocs/petnexa_API/delete_spa_service.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    
    if ($user_id <= 0 || $service_id <= 0) {
        $response['error'] = true;
        $response['message'] = 'User ID and service ID are required';
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
    
    // Delete service (ensure it belongs to this spa)
    $stmt = $conn->prepare("DELETE FROM spa_services WHERE service_id = ? AND spa_id = ?");
    $stmt->bind_param("ii", $service_id, $spa_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['error'] = false;
            $response['message'] = 'Service deleted successfully';
        } else {
            $response['error'] = true;
            $response['message'] = 'Service not found';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Failed to delete service: ' . $stmt->error;
    }
    
    $stmt->close();
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
