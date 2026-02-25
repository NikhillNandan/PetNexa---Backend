<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'db.php';

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

if ($doctor_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid doctor ID'
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
            da.treatment_charge,
            p.pet_name,
            p.species,
            p.breed,
            u.full_name AS owner_name,
            u.phone AS owner_phone
        FROM doctor_appointments da
        LEFT JOIN pets p ON da.pet_id = p.pet_id
        INNER JOIN users u ON da.buyer_id = u.user_id
        WHERE da.doctor_id = ? AND DATE(da.appointment_date) = CURDATE()
        ORDER BY da.appointment_date ASC
    ");
    
    $stmt->execute([$doctor_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format appointments
    $formatted_appointments = [];
    foreach ($appointments as $apt) {
        // Use booking_time column if available, otherwise extract from appointment_date
        $time = !empty($apt['booking_time']) ? $apt['booking_time'] : date('h:i A', strtotime($apt['appointment_date']));
        
        $formatted_appointments[] = [
            'appointment_id' => intval($apt['appointment_id']),
            'pet_name' => $apt['pet_name'] ?? 'N/A',
            'species' => $apt['species'] ?? 'N/A',
            'breed' => $apt['breed'] ?? 'N/A',
            'owner_name' => $apt['owner_name'],
            'owner_phone' => $apt['owner_phone'],
            'time' => $time,
            'service_name' => $apt['booked_services'] ?? 'General Consultation',
            'visit_type' => $apt['visit_type'] ?? 'clinic',
            'treatment_charge' => floatval($apt['treatment_charge'] ?? 0),
            'status' => $apt['consultation_status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'appointments' => $formatted_appointments
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
