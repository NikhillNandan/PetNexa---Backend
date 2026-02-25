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
            da.treatment_charge,
            p.pet_name,
            p.breed,
            p.age,
            p.species,
            u.full_name AS owner_name,
            u.phone AS owner_phone
        FROM doctor_appointments da
        LEFT JOIN pets p ON da.pet_id = p.pet_id
        INNER JOIN users u ON da.buyer_id = u.user_id
        WHERE da.doctor_id = ? AND da.consultation_status = 'BOOKED'
        ORDER BY da.appointment_date ASC
    ");
    
    $stmt->execute([$doctor_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format requests
    $formatted_requests = [];
    foreach ($requests as $req) {
        $date = date('M d, Y', strtotime($req['appointment_date']));
        
        // Use booking_time column if available, otherwise extract from appointment_date
        $time = !empty($req['booking_time']) ? $req['booking_time'] : date('h:i A', strtotime($req['appointment_date']));
        
        // Determine age format
        $age_text = $req['age'] ? $req['age'] . ' months' : 'Unknown';
        
        $formatted_requests[] = [
            'appointment_id' => intval($req['appointment_id']),
            'pet_name' => $req['pet_name'] ?? 'N/A',
            'breed' => $req['breed'] ?: 'Unknown',
            'age' => $age_text,
            'species' => $req['species'] ?? 'N/A',
            'owner_name' => $req['owner_name'],
            'owner_phone' => $req['owner_phone'],
            'appointment_date' => $date,
            'appointment_time' => $time,
            'service_name' => $req['booked_services'] ?? 'General Consultation',
            'visit_type' => $req['visit_type'] ?? 'clinic',
            'service_price' => floatval($req['treatment_charge'] ?? 0),
            'status' => 'pending'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'requests' => $formatted_requests
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
