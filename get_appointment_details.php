<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'db.php';

$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

if ($appointment_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid appointment ID'
    ]);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("
        SELECT 
            da.appointment_id,
            da.appointment_date,
            da.booking_time,
            da.service_name AS booked_services,
            da.visit_type,
            da.consultation_status,
            da.treatment_notes,
            da.treatment_charge,
            p.pet_id,
            p.pet_name,
            p.breed,
            p.age,
            p.species,
            p.gender,
            u.user_id AS owner_id,
            u.full_name AS owner_name,
            u.phone AS owner_phone,
            u.email AS owner_email
        FROM doctor_appointments da
        LEFT JOIN pets p ON da.pet_id = p.pet_id
        INNER JOIN users u ON da.buyer_id = u.user_id
        WHERE da.appointment_id = ?
        LIMIT 1
    ");
    
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo json_encode([
            'success' => false,
            'error' => 'Appointment not found'
        ]);
        exit;
    }
    
    // Format appointment details
    $date = date('M d, Y', strtotime($appointment['appointment_date']));
    
    // Use booking_time column if available, otherwise extract from appointment_date
    $time = !empty($appointment['booking_time']) ? $appointment['booking_time'] : date('h:i A', strtotime($appointment['appointment_date']));
    
    $age_text = $appointment['age'] ? $appointment['age'] . ' months' : 'Unknown';
    
    $total_amount = floatval($appointment['treatment_charge'] ?? 0);
    
    $formatted_appointment = [
        'appointment_id' => intval($appointment['appointment_id']),
        'pet_id' => $appointment['pet_id'] ? intval($appointment['pet_id']) : null,
        'pet_name' => $appointment['pet_name'] ?? 'N/A',
        'breed' => $appointment['breed'] ?: 'Unknown',
        'age' => $age_text,
        'species' => $appointment['species'] ?? 'N/A',
        'gender' => $appointment['gender'] ?? 'N/A',
        'owner_id' => intval($appointment['owner_id']),
        'owner_name' => $appointment['owner_name'],
        'owner_phone' => $appointment['owner_phone'],
        'owner_email' => $appointment['owner_email'],
        'appointment_date' => $date,
        'appointment_time' => $time,
        'service_name' => $appointment['booked_services'] ?? 'General Consultation',
        'visit_type' => $appointment['visit_type'] ?? 'clinic',
        'total_amount' => $total_amount,
        'treatment_notes' => $appointment['treatment_notes'],
        'status' => $appointment['consultation_status']
    ];
    
    echo json_encode([
        'success' => true,
        'appointment' => $formatted_appointment
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
