<?php
/**
 * add_spa_service.php - Add a new service to spa
 * Deploy to: htdocs/petnexa_API/add_spa_service.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $duration_minutes = isset($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : 0;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    
    if ($user_id <= 0 || empty($service_name) || $price <= 0) {
        $response['error'] = true;
        $response['message'] = 'User ID, service name, and price are required';
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
    
    // Insert service
    $stmt = $conn->prepare("INSERT INTO spa_services (spa_id, service_name, description, duration_minutes, price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issid", $spa_id, $service_name, $description, $duration_minutes, $price);
    
    if ($stmt->execute()) {
        $service_id = $conn->insert_id;
        $response['error'] = false;
        $response['message'] = 'Service added successfully';
        $response['service_id'] = $service_id;
    } else {
        $response['error'] = true;
        $response['message'] = 'Failed to add service: ' . $stmt->error;
    }
    
    $stmt->close();
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
