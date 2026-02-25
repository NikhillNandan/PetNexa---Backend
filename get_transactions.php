<?php
/**
 * get_transactions.php - Get transaction history for spa
 * Deploy to: htdocs/petnexa_API/get_transactions.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (isset($_POST['user_id']) ? intval($_POST['user_id']) : 0);
    $period = isset($_GET['period']) ? trim($_GET['period']) : (isset($_POST['period']) ? trim($_POST['period']) : 'all');
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : (isset($_POST['limit']) ? intval($_POST['limit']) : 50);
    
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
    
    // Build query based on period
    $query = "SELECT sb.booking_id, sb.booking_date, sb.total_amount, sb.status, sb.pet_name, sb.pet_type,
                     ss.service_name,
                     u.full_name as customer_name
              FROM spa_bookings sb
              LEFT JOIN spa_services ss ON sb.service_id = ss.service_id
              LEFT JOIN users u ON sb.user_id = u.user_id
              WHERE sb.spa_id = ? AND sb.status = 'completed'";
    
    switch ($period) {
        case 'week':
            $query .= " AND YEARWEEK(sb.booking_date) = YEARWEEK(CURDATE())";
            break;
        case 'month':
            $query .= " AND MONTH(sb.booking_date) = MONTH(CURDATE()) AND YEAR(sb.booking_date) = YEAR(CURDATE())";
            break;
        case 'year':
            $query .= " AND YEAR(sb.booking_date) = YEAR(CURDATE())";
            break;
        // 'all' - no additional filter
    }
    
    $query .= " ORDER BY sb.booking_date DESC LIMIT ?";
    
    $transactions_query = $conn->prepare($query);
    $transactions_query->bind_param("ii", $spa_id, $limit);
    $transactions_query->execute();
    $transactions_result = $transactions_query->get_result();
    
    $transactions = array();
    while ($row = $transactions_result->fetch_assoc()) {
        $transactions[] = array(
            'booking_id' => intval($row['booking_id']),
            'customer_name' => $row['customer_name'],
            'pet_name' => $row['pet_name'],
            'pet_type' => $row['pet_type'],
            'service_name' => $row['service_name'],
            'booking_date' => $row['booking_date'],
            'total_amount' => floatval($row['total_amount']),
            'status' => $row['status']
        );
    }
    
    $transactions_query->close();
    
    $response['error'] = false;
    $response['message'] = 'Transactions retrieved successfully';
    $response['transactions'] = $transactions;
    $response['count'] = count($transactions);
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();
?>
