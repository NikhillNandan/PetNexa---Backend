<?php
/**
 * complete_booking.php - Mark a booking as completed
 * Deploy to: htdocs/petnexa_API/complete_booking.php
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
    
    // Update BOTH status columns - only allow completing accepted/confirmed bookings
    $stmt = $conn->prepare("UPDATE spa_bookings SET booking_status = 'COMPLETED', status = 'completed' WHERE booking_id = ? AND spa_id = ? AND (booking_status IN ('CONFIRMED', 'BOOKED') OR status IN ('accepted', 'pending', 'booked'))");
    $stmt->bind_param("ii", $booking_id, $spa_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['error'] = false;
            $response['message'] = 'Booking marked as completed';

            // Notify the buyer
            require_once 'send_fcm.php';
            $buyerStmt = $conn->prepare("SELECT buyer_id FROM spa_bookings WHERE booking_id = ?");
            $buyerStmt->bind_param("i", $booking_id);
            $buyerStmt->execute();
            $buyerRow = $buyerStmt->get_result()->fetch_assoc();
            if ($buyerRow) {
                sendFCMNotification($buyerRow['buyer_id'], 'Spa Session Complete',
                    'Your spa session has been marked as completed. Please leave a review!',
                    'booking', $booking_id);
            }
            $buyerStmt->close();
        } else {
            $response['error'] = true;
            $response['message'] = 'Booking not found or already completed';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Failed to complete booking: ' . $stmt->error;
    }
    
    $stmt->close();
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
