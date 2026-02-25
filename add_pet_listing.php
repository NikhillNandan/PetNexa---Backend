<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['seller_id']) || !isset($input['pet_type']) || !isset($input['breed']) || 
    !isset($input['age']) || !isset($input['gender']) || !isset($input['price']) || 
    !isset($input['description']) || !isset($input['photos']) || !isset($input['vaccination_cert'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields'
    ]);
    exit;
}

$seller_id = intval($input['seller_id']);
$pet_type = $input['pet_type'];
$breed = $input['breed'];
$age = $input['age'];
$gender = $input['gender'];
$price = floatval($input['price']);
$description = $input['description'];
$photos = $input['photos']; // Array of Base64 images
$vaccination_cert = $input['vaccination_cert']; // Base64 certificate
$health_cert = isset($input['health_cert']) ? $input['health_cert'] : null;
$license_cert = isset($input['license_cert']) ? $input['license_cert'] : null;

// Validate minimum 3 photos
if (!is_array($photos) || count($photos) < 3) {
    echo json_encode([
        'success' => false,
        'error' => 'Minimum 3 photos required'
    ]);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $conn->beginTransaction();
    
    // Insert pet listing
    $stmt = $conn->prepare("
        INSERT INTO pet_listings 
        (seller_id, pet_type, breed, age, gender, price, description, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    $stmt->execute([$seller_id, $pet_type, $breed, $age, $gender, $price, $description]);
    $listing_id = $conn->lastInsertId();
    
    // Save photos
    $upload_dir = '../uploads/pet_photos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    foreach ($photos as $index => $base64_image) {
        // Decode Base64
        $image_data = base64_decode($base64_image);
        $filename = 'pet_' . $listing_id . '_' . $index . '_' . time() . '.jpg';
        $filepath = $upload_dir . $filename;
        
        // Save file
        file_put_contents($filepath, $image_data);
        
        // Insert photo record
        $photo_url = 'uploads/pet_photos/' . $filename;
        $is_primary = ($index === 0) ? 1 : 0;
        
        $stmt = $conn->prepare("
            INSERT INTO pet_photos (listing_id, photo_url, is_primary, uploaded_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$listing_id, $photo_url, $is_primary]);
    }
    
    // Save vaccination certificate
    $cert_dir = '../uploads/certificates/';
    if (!file_exists($cert_dir)) {
        mkdir($cert_dir, 0777, true);
    }
    
    $cert_data = base64_decode($vaccination_cert);
    $cert_filename = 'vacc_cert_' . $listing_id . '_' . time() . '.pdf';
    $cert_filepath = $cert_dir . $cert_filename;
    file_put_contents($cert_filepath, $cert_data);
    
    $vacc_cert_url = 'uploads/certificates/' . $cert_filename;
    
    $stmt = $conn->prepare("
        INSERT INTO pet_certificates (listing_id, certificate_type, certificate_url, uploaded_at)
        VALUES (?, 'vaccination', ?, NOW())
    ");
    $stmt->execute([$listing_id, $vacc_cert_url]);
    
    // Save health certificate if provided
    if ($health_cert) {
        $health_data = base64_decode($health_cert);
        $health_filename = 'health_cert_' . $listing_id . '_' . time() . '.pdf';
        $health_filepath = $cert_dir . $health_filename;
        file_put_contents($health_filepath, $health_data);
        
        $health_cert_url = 'uploads/certificates/' . $health_filename;
        
        $stmt = $conn->prepare("
            INSERT INTO pet_certificates (listing_id, certificate_type, certificate_url, uploaded_at)
            VALUES (?, 'health', ?, NOW())
        ");
        $stmt->execute([$listing_id, $health_cert_url]);
    }
    
    // Save license certificate if provided
    if ($license_cert) {
        $license_data = base64_decode($license_cert);
        $license_filename = 'license_cert_' . $listing_id . '_' . time() . '.pdf';
        $license_filepath = $cert_dir . $license_filename;
        file_put_contents($license_filepath, $license_data);
        
        $license_cert_url = 'uploads/certificates/' . $license_filename;
        
        $stmt = $conn->prepare("
            INSERT INTO pet_certificates (listing_id, certificate_type, certificate_url, uploaded_at)
            VALUES (?, 'license', ?, NOW())
        ");
        $stmt->execute([$listing_id, $license_cert_url]);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pet listing created successfully',
        'listing_id' => $listing_id
    ]);
    
} catch(PDOException $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
