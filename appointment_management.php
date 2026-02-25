<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch($action) {
    case 'update_charges':
        updateAppointmentCharges();
        break;
    case 'update_status':
        updateAppointmentStatus();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action. Use: update_charges, update_status']);
        exit;
}

function updateAppointmentCharges() {
    global $host, $dbname, $username, $password;
    
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
    $treatment_charge = isset($_POST['treatment_charge']) ? floatval($_POST['treatment_charge']) : 0;
    
    if ($appointment_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid appointment ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("UPDATE doctor_appointments SET treatment_charge = ? WHERE appointment_id = ?");
        $stmt->execute([$treatment_charge, $appointment_id]);
        
        echo json_encode(['success' => true, 'message' => 'Treatment charges updated successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateAppointmentStatus() {
    global $host, $dbname, $username, $password;
    
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $treatment_notes = isset($_POST['treatment_notes']) ? $_POST['treatment_notes'] : '';
    
    if ($appointment_id <= 0 || empty($status)) {
        echo json_encode(['success' => false, 'error' => 'Invalid appointment ID or status']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("UPDATE doctor_appointments SET consultation_status = ?, treatment_notes = ? WHERE appointment_id = ?");
        $stmt->execute([$status, $treatment_notes, $appointment_id]);
        
        // Notify the buyer about status change
        require_once 'send_fcm.php';
        $buyerStmt = $conn->prepare("SELECT buyer_id FROM doctor_appointments WHERE appointment_id = ?");
        $buyerStmt->execute([$appointment_id]);
        $buyerRow = $buyerStmt->fetch(PDO::FETCH_ASSOC);
        if ($buyerRow) {
            $statusMsg = 'Your appointment status has been updated to: ' . $status;
            sendFCMNotification($buyerRow['buyer_id'], 'Appointment Update',
                $statusMsg, 'appointment', $appointment_id);
        }

        echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
