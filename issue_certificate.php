<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

// Get POST data
$pet_id = isset($_POST['pet_id']) ? intval($_POST['pet_id']) : 0;
$doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
$certificate_type = isset($_POST['certificate_type']) ? $_POST['certificate_type'] : '';
$certificate_file = isset($_POST['certificate_file']) ? $_POST['certificate_file'] : '';
$issued_date = isset($_POST['issued_date']) ? $_POST['issued_date'] : date('Y-m-d');
$validity_period = isset($_POST['validity_period']) ? $_POST['validity_period'] : null;
$notes = isset($_POST['notes']) ? $_POST['notes'] : '';

if ($pet_id <= 0 || $doctor_id <= 0 || empty($certificate_type) || empty($certificate_file)) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields'
    ]);
    exit;
}

// Calculate expiry date from validity period if provided
$expiry_date = null;
if ($validity_period) {
    // Parse validity period (e.g., "1 year", "6 months")
    if (preg_match('/(\d+)\s*(year|month)/i', $validity_period, $matches)) {
        $amount = intval($matches[1]);
        $unit = strtolower($matches[2]);
        
        $expiry_date = date('Y-m-d', strtotime($issued_date . ' +' . $amount . ' ' . $unit . 's'));
    }
}

// Map certificate type (handle custom "Others" type)
$db_cert_type = 'HEALTH'; // default
if (stripos($certificate_type, 'vaccination') !== false || stripos($certificate_type, 'rabies') !== false) {
    $db_cert_type = 'VACCINATION';
} elseif (stripos($certificate_type, 'health') !== false) {
    $db_cert_type = 'HEALTH';
} elseif (stripos($certificate_type, 'travel') !== false || stripos($certificate_type, 'export') !== false || stripos($certificate_type, 'license') !== false) {
    $db_cert_type = 'LICENSE';
}

// Store custom type in notes if it's "Others"
if (stripos($certificate_type, 'others') !== false || !in_array($db_cert_type, ['VACCINATION', 'HEALTH', 'LICENSE'])) {
    $notes = "Certificate Type: " . $certificate_type . "\n\n" . $notes;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Insert certificate
    $stmt = $conn->prepare("
        INSERT INTO certificates 
        (pet_id, issued_by, certificate_type, certificate_file, issued_date, expiry_date, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $pet_id,
        $doctor_id,
        $db_cert_type,
        $certificate_file,
        $issued_date,
        $expiry_date,
        $notes
    ]);
    
    $certificate_id = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'certificate_id' => $certificate_id,
        'message' => 'Certificate issued successfully'
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
