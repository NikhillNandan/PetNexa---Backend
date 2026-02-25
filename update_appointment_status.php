<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

// Get POST data
$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;

if ($appointment_id <= 0 || $doctor_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid appointment ID or doctor ID'
    ]);
    exit;
}

// Validate status
$valid_statuses = ['BOOKED', 'COMPLETED', 'CANCELLED'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid status. Must be BOOKED, COMPLETED, or CANCELLED'
    ]);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update appointment status
    $stmt = $conn->prepare("
        UPDATE doctor_appointments 
        SET consultation_status = ? 
        WHERE appointment_id = ? AND doctor_id = ?
    ");
    
    $stmt->execute([$status, $appointment_id, $doctor_id]);
    
    if ($stmt->rowCount() > 0) {
        $message = $status === 'COMPLETED' ? 'Appointment accepted successfully' : 
                   ($status === 'CANCELLED' ? 'Appointment declined successfully' : 'Status updated successfully');
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'status' => $status
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Appointment not found or unauthorized'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
