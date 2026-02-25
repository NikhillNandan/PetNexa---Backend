<?php
/**
 * accept_booking.php - Accept a booking request
 * Deploy to: htdocs/petnexa_API/accept_booking.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if ($user_id <= 0 || $booking_id <= 0) {
        $response['error'] = true;
        $response['message'] = 'User ID and booking ID are required';
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
    
    // Update BOTH status columns for compatibility
    // Check either status column for pending/booked state
    $stmt = $conn->prepare("UPDATE spa_bookings SET booking_status = 'CONFIRMED', status = 'accepted' WHERE booking_id = ? AND spa_id = ? AND (booking_status IN ('BOOKED', '') OR status IN ('pending', 'booked'))");
    $stmt->bind_param("ii", $booking_id, $spa_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['error'] = false;
            $response['message'] = 'Booking accepted successfully';

            // Notify the buyer
            require_once 'send_fcm.php';
            $buyerStmt = $conn->prepare("SELECT buyer_id FROM spa_bookings WHERE booking_id = ?");
            $buyerStmt->bind_param("i", $booking_id);
            $buyerStmt->execute();
            $buyerRow = $buyerStmt->get_result()->fetch_assoc();
            if ($buyerRow) {
                sendFCMNotification($buyerRow['buyer_id'], 'Booking Accepted!',
                    'Your spa booking has been accepted. Get ready for your appointment!',
                    'booking', $booking_id);
            }
            $buyerStmt->close();
        } else {
            $response['error'] = true;
            $response['message'] = 'Booking not found or already processed';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Failed to accept booking: ' . $stmt->error;
    }
    
    $stmt->close();
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
