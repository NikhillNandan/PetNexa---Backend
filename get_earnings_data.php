<?php
/**
 * get_earnings_data.php - Get earnings data with period filter
 * Deploy to: htdocs/petnexa_API/get_earnings_data.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (isset($_POST['user_id']) ? intval($_POST['user_id']) : 0);
    $period = isset($_GET['period']) ? trim($_GET['period']) : (isset($_POST['period']) ? trim($_POST['period']) : 'month');
    
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
    
    // Calculate total earnings
    $total_earnings_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND booking_status = 'COMPLETED'");
    $total_earnings_query->bind_param("i", $spa_id);
    $total_earnings_query->execute();
    $total_earnings = $total_earnings_query->get_result()->fetch_assoc()['total'];
    $total_earnings_query->close();
    
    // Calculate pending amount (accepted but not completed)
    $pending_amount_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND booking_status = 'BOOKED'");
    $pending_amount_query->bind_param("i", $spa_id);
    $pending_amount_query->execute();
    $pending_amount = $pending_amount_query->get_result()->fetch_assoc()['total'];
    $pending_amount_query->close();
    
    // Get period-specific earnings based on filter
    $period_earnings = 0;
    
    switch ($period) {
        case 'week':
            $period_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND YEARWEEK(booking_date) = YEARWEEK(CURDATE()) AND booking_status = 'COMPLETED'");
            break;
        case 'year':
            $period_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND YEAR(booking_date) = YEAR(CURDATE()) AND booking_status = 'COMPLETED'");
            break;
        case 'month':
        default:
            $period_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE()) AND booking_status = 'COMPLETED'");
            break;
    }
    
    $period_query->bind_param("i", $spa_id);
    $period_query->execute();
    $period_earnings = $period_query->get_result()->fetch_assoc()['total'];
    $period_query->close();
    
    // Get today, week, month earnings
    $today_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND DATE(booking_date) = CURDATE() AND booking_status = 'COMPLETED'");
    $today_query->bind_param("i", $spa_id);
    $today_query->execute();
    $today_earnings = $today_query->get_result()->fetch_assoc()['total'];
    $today_query->close();
    
    $week_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND YEARWEEK(booking_date) = YEARWEEK(CURDATE()) AND booking_status = 'COMPLETED'");
    $week_query->bind_param("i", $spa_id);
    $week_query->execute();
    $week_earnings = $week_query->get_result()->fetch_assoc()['total'];
    $week_query->close();
    
    $month_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE()) AND booking_status = 'COMPLETED'");
    $month_query->bind_param("i", $spa_id);
    $month_query->execute();
    $month_earnings = $month_query->get_result()->fetch_assoc()['total'];
    $month_query->close();
    
    $response['error'] = false;
    $response['message'] = 'Earnings data retrieved successfully';
    $response['earnings'] = array(
        'total_earnings' => floatval($total_earnings),
        'pending_amount' => floatval($pending_amount),
        'period_earnings' => floatval($period_earnings),
        'today_earnings' => floatval($today_earnings),
        'week_earnings' => floatval($week_earnings),
        'month_earnings' => floatval($month_earnings),
        'period' => $period
    );
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();
?>
