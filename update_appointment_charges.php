<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

// Get POST data
$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$treatment_charge = isset($_POST['treatment_charge']) ? floatval($_POST['treatment_charge']) : null;
$doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;

if ($appointment_id <= 0 || $doctor_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid appointment ID or doctor ID'
    ]);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update treatment charge
    $stmt = $conn->prepare("
        UPDATE doctor_appointments 
        SET treatment_charge = ? 
        WHERE appointment_id = ? AND doctor_id = ?
    ");
    
    $stmt->execute([$treatment_charge, $appointment_id, $doctor_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Charges updated successfully',
            'treatment_charge' => $treatment_charge
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
