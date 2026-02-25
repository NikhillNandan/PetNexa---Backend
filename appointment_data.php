<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'db.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch($action) {
    case 'get_details':
        getAppointmentDetails();
        break;
    case 'get_requests':
        getAppointmentRequests();
        break;
    case 'get_all':
        getAllAppointments();
        break;
    case 'get_today':
        getTodayAppointments();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action. Use: get_details, get_requests, get_all, get_today']);
        exit;
}

function getAppointmentDetails() {
    global $host, $dbname, $username, $password;
    
    $appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
    
    if ($appointment_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid appointment ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT da.appointment_id, da.appointment_date, da.booking_time, da.service_name AS booked_service, da.consultation_status, da.treatment_notes, da.treatment_charge, da.pet_id, p.pet_name, p.breed, p.age, p.species, p.gender, u.user_id AS owner_id, u.full_name AS owner_name, u.phone AS owner_phone, u.email AS owner_email, ds.service_name, ds.price AS base_price FROM doctor_appointments da LEFT JOIN pets p ON da.pet_id = p.pet_id INNER JOIN users u ON da.buyer_id = u.user_id LEFT JOIN doctor_services ds ON ds.doctor_id = da.doctor_id AND ds.service_name = da.service_name WHERE da.appointment_id = ? LIMIT 1");
        
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            echo json_encode(['success' => false, 'error' => 'Appointment not found']);
            exit;
        }
        
        $date = date('M d, Y', strtotime($appointment['appointment_date']));
        $time = $appointment['booking_time'] ?: date('h:i A', strtotime($appointment['appointment_date']));
        $age_text = $appointment['age'] ? $appointment['age'] . ' months' : 'N/A';
        
        $service_name = $appointment['booked_service'] ?: ($appointment['service_name'] ?: 'General Consultation');
        $base_price = $appointment['base_price'] ? floatval($appointment['base_price']) : 0.00;
        $extra_charges = $appointment['treatment_charge'] ? floatval($appointment['treatment_charge']) : 0.00;
        $total_amount = $base_price + $extra_charges;
        
        $formatted_appointment = [
            'appointment_id' => intval($appointment['appointment_id']),
            'pet_id' => intval($appointment['pet_id'] ?: 0),
            'pet_name' => $appointment['pet_name'] ?: 'No Pet Specified',
            'breed' => $appointment['breed'] ?: 'Unknown',
            'age' => $age_text,
            'species' => $appointment['species'] ?: 'Unknown',
            'gender' => $appointment['gender'] ?: 'Unknown',
            'owner_id' => intval($appointment['owner_id']),
            'owner_name' => $appointment['owner_name'],
            'owner_phone' => $appointment['owner_phone'],
            'owner_email' => $appointment['owner_email'],
            'appointment_date' => $date,
            'appointment_time' => $time,
            'service_name' => $service_name,
            'base_price' => $base_price,
            'extra_charges' => $extra_charges,
            'total_amount' => $total_amount,
            'treatment_notes' => $appointment['treatment_notes'],
            'status' => $appointment['consultation_status']
        ];
        
        echo json_encode(['success' => true, 'appointment' => $formatted_appointment]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getAppointmentRequests() {
    global $host, $dbname, $username, $password;
    
    $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
    
    if ($doctor_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid doctor ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT da.appointment_id, da.appointment_date, da.booking_time, da.service_name AS booked_service, da.consultation_status, da.treatment_charge, p.pet_name, p.breed, p.age, p.species, u.full_name AS owner_name, u.phone AS owner_phone, ds.service_name, ds.price AS service_price FROM doctor_appointments da LEFT JOIN pets p ON da.pet_id = p.pet_id INNER JOIN users u ON da.buyer_id = u.user_id LEFT JOIN doctor_services ds ON ds.doctor_id = da.doctor_id AND ds.service_name = da.service_name WHERE da.doctor_id = ? AND da.consultation_status = 'BOOKED' ORDER BY da.appointment_date ASC");
        
        $stmt->execute([$doctor_id]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formatted_appointments = array_map(function($apt) {
            $age_text = $apt['age'] ? $apt['age'] . ' months' : 'N/A';
            return [
                'appointment_id' => intval($apt['appointment_id']),
                'pet_name' => $apt['pet_name'] ?: 'No Pet Specified',
                'breed' => $apt['breed'] ?: 'Unknown',
                'age' => $age_text,
                'species' => $apt['species'] ?: 'Unknown',
                'owner_name' => $apt['owner_name'],
                'owner_phone' => $apt['owner_phone'],
                'appointment_date' => date('M d, Y', strtotime($apt['appointment_date'])),
                'appointment_time' => $apt['booking_time'] ?: date('h:i A', strtotime($apt['appointment_date'])),
                'service_name' => $apt['booked_service'] ?: ($apt['service_name'] ?: 'General Consultation'),
                'service_price' => floatval($apt['service_price'] ?: 0),
                'status' => $apt['consultation_status']
            ];
        }, $appointments);
        
        echo json_encode(['success' => true, 'appointments' => $formatted_appointments, 'total_count' => count($formatted_appointments)]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getTodayAppointments() {
    global $host, $dbname, $username, $password;
    
    $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
    
    if ($doctor_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid doctor ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT da.appointment_id, da.appointment_date, da.booking_time, da.service_name, da.consultation_status, p.pet_name, p.breed, p.species, u.full_name AS owner_name, u.phone AS owner_phone FROM doctor_appointments da LEFT JOIN pets p ON da.pet_id = p.pet_id INNER JOIN users u ON da.buyer_id = u.user_id WHERE da.doctor_id = ? AND DATE(da.appointment_date) = CURDATE() ORDER BY da.appointment_date ASC");
        
        $stmt->execute([$doctor_id]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formatted_appointments = array_map(function($apt) {
            return [
                'appointment_id' => intval($apt['appointment_id']),
                'pet_name' => $apt['pet_name'] ?: 'No Pet Specified',
                'breed' => $apt['breed'] ?: 'Unknown',
                'species' => $apt['species'] ?: 'Unknown',
                'owner_name' => $apt['owner_name'],
                'owner_phone' => $apt['owner_phone'],
                'appointment_date' => date('M d, Y', strtotime($apt['appointment_date'])),
                'appointment_time' => $apt['booking_time'] ?: date('h:i A', strtotime($apt['appointment_date'])),
                'service_name' => $apt['service_name'] ?: 'General Consultation',
                'status' => $apt['consultation_status']
            ];
        }, $appointments);
        
        echo json_encode(['success' => true, 'appointments' => $formatted_appointments, 'total_count' => count($formatted_appointments)]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getAllAppointments() {
    global $host, $dbname, $username, $password;
    
    $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
    
    if ($doctor_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid doctor ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT da.appointment_id, da.appointment_date, da.booking_time, da.service_name AS booked_service, da.consultation_status, da.treatment_charge, p.pet_name, p.breed, p.age, p.species, u.full_name AS owner_name, u.phone AS owner_phone, ds.service_name, ds.price AS service_price FROM doctor_appointments da LEFT JOIN pets p ON da.pet_id = p.pet_id INNER JOIN users u ON da.buyer_id = u.user_id LEFT JOIN doctor_services ds ON ds.doctor_id = da.doctor_id AND ds.service_name = da.service_name WHERE da.doctor_id = ? ORDER BY da.appointment_date DESC");
        
        $stmt->execute([$doctor_id]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formatted_appointments = array_map(function($apt) {
            $age_text = $apt['age'] ? $apt['age'] . ' months' : 'N/A';
            return [
                'appointment_id' => intval($apt['appointment_id']),
                'pet_name' => $apt['pet_name'] ?: 'No Pet Specified',
                'breed' => $apt['breed'] ?: 'Unknown',
                'age' => $age_text,
                'species' => $apt['species'] ?: 'Unknown',
                'owner_name' => $apt['owner_name'],
                'owner_phone' => $apt['owner_phone'],
                'appointment_date' => date('M d, Y', strtotime($apt['appointment_date'])),
                'appointment_time' => $apt['booking_time'] ?: date('h:i A', strtotime($apt['appointment_date'])),
                'service_name' => $apt['booked_service'] ?: ($apt['service_name'] ?: 'General Consultation'),
                'service_price' => floatval($apt['service_price'] ?: 0),
                'status' => $apt['consultation_status']
            ];
        }, $appointments);
        
        echo json_encode(['success' => true, 'appointments' => $formatted_appointments, 'total_count' => count($formatted_appointments)]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
