<?php
/**
 * get_spa_services.php - Get all services for a spa
 * Deploy to: htdocs/petnexa_API/get_spa_services.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (isset($_POST['user_id']) ? intval($_POST['user_id']) : 0);
    
    if ($user_id <= 0) {
        $response['error'] = true;
        $response['message'] = 'User ID is required';
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
    
    // Get all services
    $services_query = $conn->prepare("SELECT service_id, service_name, description, duration_minutes, price FROM spa_services WHERE spa_id = ? ORDER BY service_name ASC");
    $services_query->bind_param("i", $spa_id);
    $services_query->execute();
    $services_result = $services_query->get_result();
    
    $services = array();
    while ($row = $services_result->fetch_assoc()) {
        $services[] = array(
            'service_id' => intval($row['service_id']),
            'service_name' => $row['service_name'],
            'description' => $row['description'],
            'duration_minutes' => intval($row['duration_minutes']),
            'price' => floatval($row['price'])
        );
    }
    
    $services_query->close();
    
    $response['error'] = false;
    $response['message'] = 'Services retrieved successfully';
    $response['services'] = $services;
    $response['count'] = count($services);
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();
?>
