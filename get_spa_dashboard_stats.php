<?php
/**
 * get_spa_dashboard_stats.php - Get dashboard statistics for spa owner
 * Deploy to: htdocs/petnexa_API/get_spa_dashboard_stats.php
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
    
    // Get spa_id from spa_profiles
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
    
    // Get today's bookings count
    $today_bookings_query = $conn->prepare("SELECT COUNT(*) as count FROM spa_bookings WHERE spa_id = ? AND DATE(booking_date) = CURDATE()");
    $today_bookings_query->bind_param("i", $spa_id);
    $today_bookings_query->execute();
    $today_bookings = $today_bookings_query->get_result()->fetch_assoc()['count'];
    $today_bookings_query->close();
    
    // Get total earnings (completed bookings)
    $total_earnings_query = $conn->prepare("SELECT COALESCE(SUM(sb.total_amount), 0) as total FROM spa_bookings sb WHERE sb.spa_id = ? AND sb.booking_status = 'COMPLETED'");
    $total_earnings_query->bind_param("i", $spa_id);
    $total_earnings_query->execute();
    $total_earnings = $total_earnings_query->get_result()->fetch_assoc()['total'];
    $total_earnings_query->close();
    
    // Get services count
    $services_query = $conn->prepare("SELECT COUNT(*) as count FROM spa_services WHERE spa_id = ?");
    $services_query->bind_param("i", $spa_id);
    $services_query->execute();
    $services_count = $services_query->get_result()->fetch_assoc()['count'];
    $services_query->close();
    
    // Get today's earnings
    $today_earnings_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND DATE(booking_date) = CURDATE() AND booking_status = 'COMPLETED'");
    $today_earnings_query->bind_param("i", $spa_id);
    $today_earnings_query->execute();
    $today_earnings = $today_earnings_query->get_result()->fetch_assoc()['total'];
    $today_earnings_query->close();
    
    // Get this week's earnings
    $week_earnings_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND YEARWEEK(booking_date) = YEARWEEK(CURDATE()) AND booking_status = 'COMPLETED'");
    $week_earnings_query->bind_param("i", $spa_id);
    $week_earnings_query->execute();
    $week_earnings = $week_earnings_query->get_result()->fetch_assoc()['total'];
    $week_earnings_query->close();
    
    // Get this month's earnings
    $month_earnings_query = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM spa_bookings WHERE spa_id = ? AND MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE()) AND booking_status = 'COMPLETED'");
    $month_earnings_query->bind_param("i", $spa_id);
    $month_earnings_query->execute();
    $month_earnings = $month_earnings_query->get_result()->fetch_assoc()['total'];
    $month_earnings_query->close();
    
    // Get pending bookings count
    $pending_bookings_query = $conn->prepare("SELECT COUNT(*) as count FROM spa_bookings WHERE spa_id = ? AND booking_status = 'BOOKED'");
    $pending_bookings_query->bind_param("i", $spa_id);
    $pending_bookings_query->execute();
    $pending_bookings_count = $pending_bookings_query->get_result()->fetch_assoc()['count'];
    $pending_bookings_query->close();
    
    $response['error'] = false;
    $response['message'] = 'Dashboard stats retrieved successfully';
    $response['stats'] = array(
        'today_bookings' => intval($today_bookings),
        'total_earnings' => floatval($total_earnings),
        'services_count' => intval($services_count),
        'today_earnings' => floatval($today_earnings),
        'week_earnings' => floatval($week_earnings),
        'month_earnings' => floatval($month_earnings),
        'pending_bookings' => intval($pending_bookings_count)
    );
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();
?>
