<?php
// cancel_appointment.php - Cancel a doctor appointment or spa booking
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array("status" => "error", "message" => "POST method required"));
    exit;
}

$type = isset($_POST['type']) ? strtolower($_POST['type']) : '';       // "doctor" or "spa"
$appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
$buyer_id = isset($_POST['buyer_id']) ? (int)$_POST['buyer_id'] : 0;

if (empty($type) || $appointment_id <= 0 || $buyer_id <= 0) {
    echo json_encode(array("status" => "error", "message" => "type, appointment_id, and buyer_id are required"));
    exit;
}

if ($type === "doctor") {
    // Cancel doctor appointment
    $stmt = $conn->prepare("UPDATE doctor_appointments SET consultation_status = 'CANCELLED' WHERE appointment_id = ? AND buyer_id = ? AND consultation_status NOT IN ('Cancelled','CANCELLED')");
    $stmt->bind_param("ii", $appointment_id, $buyer_id);
} else if ($type === "spa") {
    // Cancel spa booking
    $stmt = $conn->prepare("UPDATE spa_bookings SET booking_status = 'CANCELLED' WHERE booking_id = ? AND buyer_id = ? AND booking_status NOT IN ('Cancelled','CANCELLED')");
    $stmt->bind_param("ii", $appointment_id, $buyer_id);
} else {
    echo json_encode(array("success" => false, "status" => "error", "message" => "Invalid type. Must be 'doctor' or 'spa'"));
    exit;
}

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Notify the relevant provider about cancellation
        require_once 'send_fcm.php';
        if ($type === "doctor") {
            $providerStmt = $conn->prepare("SELECT doctor_id FROM doctor_appointments WHERE appointment_id = ?");
            $providerStmt->bind_param("i", $appointment_id);
            $providerStmt->execute();
            $providerRow = $providerStmt->get_result()->fetch_assoc();
            if ($providerRow) {
                sendFCMNotification($providerRow['doctor_id'], 'Appointment Cancelled',
                    'A patient has cancelled their appointment.',
                    'appointment', $appointment_id);
            }
            $providerStmt->close();
        } else if ($type === "spa") {
            // Get spa owner user_id from the booking
            $spaStmt = $conn->prepare("SELECT sp.user_id FROM spa_bookings sb JOIN spa_profiles sp ON sb.spa_id = sp.spa_id WHERE sb.booking_id = ?");
            $spaStmt->bind_param("i", $appointment_id);
            $spaStmt->execute();
            $spaRow = $spaStmt->get_result()->fetch_assoc();
            if ($spaRow) {
                sendFCMNotification($spaRow['user_id'], 'Booking Cancelled',
                    'A customer has cancelled their spa booking.',
                    'booking', $appointment_id);
            }
            $spaStmt->close();
        }

        echo json_encode(array("success" => true, "status" => "success", "message" => "Appointment cancelled successfully"));
    } else {
        echo json_encode(array("success" => false, "status" => "error", "message" => "Appointment not found or already cancelled"));
    }
} else {
    echo json_encode(array("success" => false, "status" => "error", "message" => "Failed to cancel: " . $stmt->error));
}

$stmt->close();
$conn->close();
?>
