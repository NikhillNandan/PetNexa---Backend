<?php
/**
 * get_booking_requests.php - Get all booking requests for spa
 * Deploy to: htdocs/petnexa_API/get_booking_requests.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (isset($_POST['user_id']) ? intval($_POST['user_id']) : 0);
    $status_filter = isset($_GET['status']) ? trim($_GET['status']) : (isset($_POST['status']) ? trim($_POST['status']) : 'all');
    $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
    
    // ── Single booking lookup by booking_id (used by notification deep link) ──
    if ($booking_id > 0) {
        $query = "SELECT sb.booking_id, sb.buyer_id, sb.booking_date, sb.booking_time,
                         COALESCE(NULLIF(sb.booking_status, ''), sb.status, 'BOOKED') as resolved_status,
                         COALESCE(sb.total_amount, ss.price, 0) as total_amount,
                         ss.service_name, ss.duration_minutes, ss.price as service_price,
                         u.full_name as owner_name, u.phone as owner_phone, u.profile_image as owner_image,
                         p.pet_name
                  FROM spa_bookings sb
                  LEFT JOIN spa_services ss ON sb.service_id = ss.service_id
                  LEFT JOIN users u ON sb.buyer_id = u.user_id
                  LEFT JOIN pets p ON sb.pet_id = p.pet_id
                  WHERE sb.booking_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo json_encode(array('error' => true, 'message' => 'Booking not found'));
            $stmt->close();
            $conn->close();
            exit;
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        // Format date
        $raw_date = $row['booking_date'];
        $is_valid_date = ($raw_date && $raw_date !== '0000-00-00 00:00:00' && $raw_date !== '0000-00-00' && $raw_date !== 'null');
        if ($is_valid_date) {
            $timestamp = strtotime($raw_date);
            $formatted_date = ($timestamp && $timestamp > 0) ? date('D, d M Y', $timestamp) : 'Not scheduled';
            $date_time = ($timestamp && $timestamp > 0) ? date('h:i A', $timestamp) : null;
        } else {
            $formatted_date = 'Not scheduled';
            $date_time = null;
        }

        // Format time
        $raw_time = $row['booking_time'];
        if ($raw_time && $raw_time !== '00:00:00' && $raw_time !== 'null') {
            $formatted_time = date('h:i A', strtotime($raw_time));
        } else if ($date_time && $date_time !== '12:00 AM') {
            $formatted_time = $date_time;
        } else {
            $formatted_time = 'N/A';
        }

        $amount = floatval($row['total_amount']);
        if ($amount <= 0) $amount = floatval($row['service_price']);

        echo json_encode(array(
            'error' => false,
            'booking' => array(
                'booking_id' => intval($row['booking_id']),
                'buyer_id' => intval($row['buyer_id']),
                'customer_name' => $row['owner_name'] ?? 'Customer',
                'customer_phone' => $row['owner_phone'] ?? '',
                'customer_image' => $row['owner_image'] ?? '',
                'pet_name' => $row['pet_name'] ?? 'Pet',
                'service_name' => $row['service_name'] ?? 'Service',
                'service_date' => $formatted_date,
                'service_time' => $formatted_time,
                'service_fee' => $amount,
                'duration_minutes' => intval($row['duration_minutes']),
                'status' => $row['resolved_status']
            )
        ));
        $conn->close();
        exit;
    }

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
    
    // Build query - DB has BOTH status and booking_status columns
    // Use COALESCE to read whichever has a value
    // DB also has separate booking_time column (type time)
    $query = "SELECT sb.booking_id, sb.buyer_id, sb.booking_date, sb.booking_time,
                     COALESCE(NULLIF(sb.booking_status, ''), sb.status, 'BOOKED') as resolved_status,
                     COALESCE(sb.total_amount, ss.price, 0) as total_amount,
                     ss.service_name, ss.duration_minutes, ss.price as service_price,
                     u.full_name as owner_name, u.phone as owner_phone, u.profile_image as owner_image,
                     p.pet_name
              FROM spa_bookings sb
              LEFT JOIN spa_services ss ON sb.service_id = ss.service_id
              LEFT JOIN users u ON sb.buyer_id = u.user_id
              LEFT JOIN pets p ON sb.pet_id = p.pet_id
              WHERE sb.spa_id = ?";
    
    if ($status_filter !== 'all') {
        // Filter on both status columns
        $query .= " AND (sb.booking_status = ? OR sb.status = ?)";
    }
    
    $query .= " ORDER BY sb.booking_id DESC";
    
    if ($status_filter !== 'all') {
        $bookings_query = $conn->prepare($query);
        $bookings_query->bind_param("iss", $spa_id, $status_filter, $status_filter);
    } else {
        $bookings_query = $conn->prepare($query);
        $bookings_query->bind_param("i", $spa_id);
    }
    
    $bookings_query->execute();
    $bookings_result = $bookings_query->get_result();
    
    $bookings = array();
    while ($row = $bookings_result->fetch_assoc()) {
        // Handle booking_date (datetime column)
        $raw_date = $row['booking_date'];
        $is_valid_date = ($raw_date && $raw_date !== '0000-00-00 00:00:00' && $raw_date !== '0000-00-00' && $raw_date !== 'null');
        
        if ($is_valid_date) {
            $timestamp = strtotime($raw_date);
            if ($timestamp && $timestamp > 0) {
                $formatted_date = date('D, d M Y', $timestamp);
                // Get time from booking_date datetime if it has time portion
                $date_time = date('h:i A', $timestamp);
            } else {
                $formatted_date = 'Not scheduled';
                $date_time = null;
            }
        } else {
            $formatted_date = 'Not scheduled';
            $date_time = null;
        }
        
        // Handle separate booking_time column (time type, e.g. "10:00:00")
        $raw_time = $row['booking_time'];
        if ($raw_time && $raw_time !== '00:00:00' && $raw_time !== 'null') {
            // Format time from 24h "HH:mm:ss" to 12h "hh:mm AM/PM"
            $time_ts = strtotime($raw_time);
            $formatted_time = date('h:i A', $time_ts);
        } else if ($date_time && $date_time !== '12:00 AM') {
            // Fallback: use time portion from booking_date if not midnight
            $formatted_time = $date_time;
        } else {
            $formatted_time = 'N/A';
        }
        
        // Use service price as fallback for total_amount
        $amount = floatval($row['total_amount']);
        if ($amount <= 0) {
            $amount = floatval($row['service_price']);
        }
        
        $bookings[] = array(
            'booking_id' => intval($row['booking_id']),
            'buyer_id' => intval($row['buyer_id']),
            'booking_date' => $formatted_date,
            'booking_time' => $formatted_time,
            'booking_status' => $row['resolved_status'],
            'total_amount' => $amount,
            'service_name' => $row['service_name'] ?? 'Service',
            'duration_minutes' => intval($row['duration_minutes']),
            'owner_name' => $row['owner_name'] ?? 'Customer',
            'owner_phone' => $row['owner_phone'] ?? '',
            'owner_image' => $row['owner_image'] ?? '',
            'pet_name' => $row['pet_name'] ?? 'Pet'
        );
    }
    
    $bookings_query->close();
    
    $response['error'] = false;
    $response['message'] = 'Booking requests retrieved successfully';
    $response['bookings'] = $bookings;
    $response['count'] = count($bookings);
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();
?>
