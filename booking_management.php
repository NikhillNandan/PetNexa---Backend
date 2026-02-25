<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch($action) {
    case 'accept':
        acceptBooking();
        break;
    case 'decline':
        declineBooking();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action. Use: accept, decline']);
        exit;
}

function acceptBooking() {
    global $host, $dbname, $username, $password;
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("UPDATE spa_bookings SET status = 'confirmed', confirmed_at = NOW() WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
        // Notify the buyer
        require_once 'send_fcm.php';
        $buyerStmt = $conn->prepare("SELECT buyer_id FROM spa_bookings WHERE booking_id = ?");
        $buyerStmt->execute([$booking_id]);
        $buyerRow = $buyerStmt->fetch(PDO::FETCH_ASSOC);
        if ($buyerRow) {
            sendFCMNotification($buyerRow['buyer_id'], 'Booking Confirmed!',
                'Your spa booking has been confirmed!',
                'booking', $booking_id);
        }

        echo json_encode(['success' => true, 'message' => 'Booking accepted successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function declineBooking() {
    global $host, $dbname, $username, $password;
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $reason = isset($_POST['reason']) ? $_POST['reason'] : 'No reason provided';
    
    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("UPDATE spa_bookings SET status = 'cancelled', cancellation_reason = ? WHERE booking_id = ?");
        $stmt->execute([$reason, $booking_id]);
        
        // Notify the buyer
        require_once 'send_fcm.php';
        $buyerStmt = $conn->prepare("SELECT buyer_id FROM spa_bookings WHERE booking_id = ?");
        $buyerStmt->execute([$booking_id]);
        $buyerRow = $buyerStmt->fetch(PDO::FETCH_ASSOC);
        if ($buyerRow) {
            sendFCMNotification($buyerRow['buyer_id'], 'Booking Cancelled',
                'Your spa booking has been cancelled.',
                'booking', $booking_id);
        }

        echo json_encode(['success' => true, 'message' => 'Booking declined successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
