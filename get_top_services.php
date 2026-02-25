<?php
/**
 * get_top_services.php - Get top performing services by revenue
 * Deploy to: htdocs/petnexa_API/get_top_services.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (isset($_POST['user_id']) ? intval($_POST['user_id']) : 0);
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : (isset($_POST['limit']) ? intval($_POST['limit']) : 10);
    
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
    
    // Get top services by total revenue
    $query = "SELECT ss.service_id, ss.service_name, 
                     COALESCE(SUM(sb.total_amount), 0) as total_revenue,
                     COUNT(sb.booking_id) as booking_count
              FROM spa_services ss
              LEFT JOIN spa_bookings sb ON ss.service_id = sb.service_id AND sb.status = 'completed'
              WHERE ss.spa_id = ?
              GROUP BY ss.service_id, ss.service_name
              ORDER BY total_revenue DESC
              LIMIT ?";
    
    $top_services_query = $conn->prepare($query);
    $top_services_query->bind_param("ii", $spa_id, $limit);
    $top_services_query->execute();
    $top_services_result = $top_services_query->get_result();
    
    $top_services = array();
    while ($row = $top_services_result->fetch_assoc()) {
        $top_services[] = array(
            'service_id' => intval($row['service_id']),
            'service_name' => $row['service_name'],
            'total_revenue' => floatval($row['total_revenue']),
            'booking_count' => intval($row['booking_count'])
        );
    }
    
    $top_services_query->close();
    
    $response['error'] = false;
    $response['message'] = 'Top services retrieved successfully';
    $response['top_services'] = $top_services;
    $response['count'] = count($top_services);
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();
?>
