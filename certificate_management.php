<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Suppress PHP warnings from polluting JSON output
error_reporting(0);
ini_set('display_errors', 0);

require_once 'db.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch($action) {
    case 'upload':
        uploadCertificate();
        break;
    case 'issue':
        issueCertificate();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action. Use: upload, issue']);
        exit;
}

function uploadCertificate() {
    global $host, $dbname, $username, $password;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['appointment_id']) || !isset($input['certificate_data'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $appointment_id = intval($input['appointment_id']);
    $certificate_data = $input['certificate_data'];
    $certificate_type = isset($input['certificate_type']) ? $input['certificate_type'] : 'HEALTH';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Look up pet_id and doctor_id from the appointment
        $aptStmt = $conn->prepare("SELECT pet_id, doctor_id FROM doctor_appointments WHERE appointment_id = ?");
        $aptStmt->execute([$appointment_id]);
        $apt = $aptStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$apt) {
            echo json_encode(['success' => false, 'error' => 'Appointment not found']);
            exit;
        }
        
        $pet_id = intval($apt['pet_id']);
        $doctor_id = intval($apt['doctor_id']);
        
        // Save certificate file
        $upload_dir = '../uploads/certificates/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $cert_data = base64_decode($certificate_data);
        $cert_filename = strtolower($certificate_type) . '_cert_' . $appointment_id . '_' . time() . '.pdf';
        file_put_contents($upload_dir . $cert_filename, $cert_data);
        
        $cert_url = 'uploads/certificates/' . $cert_filename;
        
        // Map certificate type to DB enum
        $db_cert_type = 'HEALTH';
        if (stripos($certificate_type, 'vaccination') !== false || stripos($certificate_type, 'rabies') !== false) {
            $db_cert_type = 'VACCINATION';
        } elseif (stripos($certificate_type, 'license') !== false || stripos($certificate_type, 'travel') !== false) {
            $db_cert_type = 'LICENSE';
        }
        
        // Insert into certificates table
        $stmt = $conn->prepare("INSERT INTO certificates (pet_id, issued_by, certificate_type, certificate_file, issued_date, notes) VALUES (?, ?, ?, ?, CURDATE(), ?)");
        $stmt->execute([$pet_id, $doctor_id, $db_cert_type, $cert_url, 'Appointment ID: ' . $appointment_id]);
        
        echo json_encode(['success' => true, 'message' => 'Certificate uploaded successfully', 'certificate_url' => $cert_url]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function issueCertificate() {
    global $host, $dbname, $username, $password;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['appointment_id']) || !isset($input['certificate_details'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $appointment_id = intval($input['appointment_id']);
    $certificate_details = $input['certificate_details'];
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Look up pet_id and doctor_id from the appointment
        $aptStmt = $conn->prepare("SELECT pet_id, doctor_id FROM doctor_appointments WHERE appointment_id = ?");
        $aptStmt->execute([$appointment_id]);
        $apt = $aptStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$apt) {
            echo json_encode(['success' => false, 'error' => 'Appointment not found']);
            exit;
        }
        
        $pet_id = intval($apt['pet_id']);
        $doctor_id = intval($apt['doctor_id']);
        
        // Parse certificate type from details string
        $db_cert_type = 'HEALTH';
        if (preg_match('/Type:\s*(.+)/i', $certificate_details, $matches)) {
            $type_str = trim($matches[1]);
            if (stripos($type_str, 'vaccination') !== false || stripos($type_str, 'rabies') !== false) {
                $db_cert_type = 'VACCINATION';
            } elseif (stripos($type_str, 'license') !== false || stripos($type_str, 'travel') !== false) {
                $db_cert_type = 'LICENSE';
            }
        }
        
        // Insert into certificates table
        $stmt = $conn->prepare("INSERT INTO certificates (pet_id, issued_by, certificate_type, issued_date, notes) VALUES (?, ?, ?, CURDATE(), ?)");
        $stmt->execute([$pet_id, $doctor_id, $db_cert_type, $certificate_details]);
        
        $certificate_id = $conn->lastInsertId();
        
        // Mark appointment as COMPLETED
        $updateStmt = $conn->prepare("UPDATE doctor_appointments SET consultation_status = 'COMPLETED' WHERE appointment_id = ?");
        $updateStmt->execute([$appointment_id]);
        
        // Notify the buyer about certificate
        @require_once 'send_fcm.php';
        $buyerStmt = $conn->prepare("SELECT buyer_id FROM doctor_appointments WHERE appointment_id = ?");
        $buyerStmt->execute([$appointment_id]);
        $buyerRow = $buyerStmt->fetch(PDO::FETCH_ASSOC);
        if ($buyerRow && function_exists('sendFCMNotification')) {
            sendFCMNotification($buyerRow['buyer_id'], 'Certificate Issued',
                'A health certificate has been issued for your pet. You can download it now.',
                'certificate', $certificate_id);
        }

        echo json_encode(['success' => true, 'message' => 'Certificate issued successfully', 'certificate_id' => $certificate_id]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>

