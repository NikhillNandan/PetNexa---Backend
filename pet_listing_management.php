<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'db.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch($action) {
    case 'add':
        addPetListing();
        break;
    case 'update':
        updatePetListing();
        break;
    case 'delete':
        deletePetListing();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action. Use: add, update, delete']);
        exit;
}

function addPetListing() {
    global $host, $dbname, $username, $password;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['seller_id']) || !isset($input['pet_type']) || !isset($input['breed']) || 
        !isset($input['age']) || !isset($input['gender']) || !isset($input['price']) || 
        !isset($input['description']) || !isset($input['photos']) || !isset($input['vaccination_cert'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $seller_id = intval($input['seller_id']);
    $pet_type = $input['pet_type'];
    $breed = $input['breed'];
    $age = $input['age'];
    $gender = strtoupper($input['gender']);
    $price = floatval($input['price']);
    $description = $input['description'];
    $photos = $input['photos'];
    $vaccination_cert = $input['vaccination_cert'];
    $health_cert = isset($input['health_cert']) ? $input['health_cert'] : null;
    $license_cert = isset($input['license_cert']) ? $input['license_cert'] : null;
    $pet_name = isset($input['pet_name']) ? $input['pet_name'] : $breed;
    $color = isset($input['color']) ? $input['color'] : '';
    
    if (!is_array($photos) || count($photos) < 3) {
        echo json_encode(['success' => false, 'error' => 'Minimum 3 photos required']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("INSERT INTO pets (seller_id, pet_name, species, breed, age, gender, color, price, description, availability_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'AVAILABLE', NOW())");
        $stmt->execute([$seller_id, $pet_name, $pet_type, $breed, intval($age), $gender, $color, $price, $description]);
        $pet_id = $conn->lastInsertId();
        
        $upload_dir = 'uploads/pets/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        foreach ($photos as $index => $base64_image) {
            $image_data = base64_decode($base64_image);
            $filename = 'pet_' . $pet_id . '_' . $index . '_' . time() . '.jpg';
            file_put_contents($upload_dir . $filename, $image_data);
            
            $image_url = 'uploads/pets/' . $filename;
            
            $stmt = $conn->prepare("INSERT INTO pet_images (pet_id, image_url) VALUES (?, ?)");
            $stmt->execute([$pet_id, $image_url]);
        }
        
        $cert_dir = 'uploads/certificates/';
        if (!file_exists($cert_dir)) mkdir($cert_dir, 0777, true);
        
        $cert_data = base64_decode($vaccination_cert);
        $cert_filename = 'vacc_cert_' . $pet_id . '_' . time() . '.pdf';
        file_put_contents($cert_dir . $cert_filename, $cert_data);
        
        $stmt = $conn->prepare("INSERT INTO certificates (pet_id, certificate_type, certificate_file, issued_date, created_at) VALUES (?, 'VACCINATION', ?, CURDATE(), NOW())");
        $stmt->execute([$pet_id, 'uploads/certificates/' . $cert_filename]);
        
        if ($health_cert) {
            $health_data = base64_decode($health_cert);
            $health_filename = 'health_cert_' . $pet_id . '_' . time() . '.pdf';
            file_put_contents($cert_dir . $health_filename, $health_data);
            $stmt = $conn->prepare("INSERT INTO certificates (pet_id, certificate_type, certificate_file, issued_date, created_at) VALUES (?, 'HEALTH', ?, CURDATE(), NOW())");
            $stmt->execute([$pet_id, 'uploads/certificates/' . $health_filename]);
        }
        
        if ($license_cert) {
            $license_data = base64_decode($license_cert);
            $license_filename = 'license_cert_' . $pet_id . '_' . time() . '.pdf';
            file_put_contents($cert_dir . $license_filename, $license_data);
            $stmt = $conn->prepare("INSERT INTO certificates (pet_id, certificate_type, certificate_file, issued_date, created_at) VALUES (?, 'LICENSE', ?, CURDATE(), NOW())");
            $stmt->execute([$pet_id, 'uploads/certificates/' . $license_filename]);
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Pet listing created successfully', 'listing_id' => intval($pet_id)]);
        
    } catch(PDOException $e) {
        if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function updatePetListing() {
    global $host, $dbname, $username, $password;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['listing_id']) || !isset($input['seller_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $pet_id = intval($input['listing_id']);
    $seller_id = intval($input['seller_id']);
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT seller_id FROM pets WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$listing || intval($listing['seller_id']) !== $seller_id) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized or listing not found']);
            exit;
        }
        
        $conn->beginTransaction();
        
        // Update basic pet details
        $gender = isset($input['gender']) ? strtoupper($input['gender']) : null;
        
        $stmt = $conn->prepare("UPDATE pets SET species = ?, breed = ?, age = ?, gender = ?, price = ?, description = ? WHERE pet_id = ? AND seller_id = ?");
        $stmt->execute([
            $input['pet_type'] ?? null, 
            $input['breed'] ?? null, 
            intval($input['age'] ?? 0), 
            $gender, 
            floatval($input['price'] ?? 0), 
            $input['description'] ?? '', 
            $pet_id, 
            $seller_id
        ]);
        
        // Handle deleted photos
        if (isset($input['deleted_photo_ids']) && is_array($input['deleted_photo_ids'])) {
            foreach ($input['deleted_photo_ids'] as $photo_id) {
                // Get file path before deleting record
                $stmt = $conn->prepare("SELECT image_url FROM pet_images WHERE image_id = ? AND pet_id = ?");
                $stmt->execute([intval($photo_id), $pet_id]);
                $photo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($photo && !empty($photo['image_url'])) {
                    $file_path = $photo['image_url'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                $stmt = $conn->prepare("DELETE FROM pet_images WHERE image_id = ? AND pet_id = ?");
                $stmt->execute([intval($photo_id), $pet_id]);
            }
        }
        
        // Handle new photos
        if (isset($input['new_photos']) && is_array($input['new_photos'])) {
            $upload_dir = 'uploads/pets/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            
            foreach ($input['new_photos'] as $index => $base64_image) {
                $image_data = base64_decode($base64_image);
                $filename = 'pet_' . $pet_id . '_' . $index . '_' . time() . '.jpg';
                file_put_contents($upload_dir . $filename, $image_data);
                
                $image_url = 'uploads/pets/' . $filename;
                
                $stmt = $conn->prepare("INSERT INTO pet_images (pet_id, image_url) VALUES (?, ?)");
                $stmt->execute([$pet_id, $image_url]);
            }
        }
        
        // Handle certificate uploads (new or replacement)
        $cert_dir = 'uploads/certificates/';
        if (!file_exists($cert_dir)) mkdir($cert_dir, 0777, true);
        
        $cert_types = [
            'vaccination_cert' => 'VACCINATION',
            'health_cert'      => 'HEALTH',
            'license_cert'     => 'LICENSE'
        ];
        
        foreach ($cert_types as $input_key => $db_type) {
            if (isset($input[$input_key]) && !empty($input[$input_key])) {
                // Delete old certificate file if exists
                $stmt = $conn->prepare("SELECT certificate_id, certificate_file FROM certificates WHERE pet_id = ? AND certificate_type = ?");
                $stmt->execute([$pet_id, $db_type]);
                $existing_cert = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing_cert) {
                    // Remove old file
                    $old_path = $existing_cert['certificate_file'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                    // Delete old record
                    $stmt = $conn->prepare("DELETE FROM certificates WHERE certificate_id = ?");
                    $stmt->execute([$existing_cert['certificate_id']]);
                }
                
                // Save new certificate file
                $cert_data = base64_decode($input[$input_key]);
                $prefix = strtolower($db_type);
                $cert_filename = $prefix . '_cert_' . $pet_id . '_' . time() . '.pdf';
                file_put_contents($cert_dir . $cert_filename, $cert_data);
                
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO certificates (pet_id, certificate_type, certificate_file, issued_date, created_at) VALUES (?, ?, ?, CURDATE(), NOW())");
                $stmt->execute([$pet_id, $db_type, 'uploads/certificates/' . $cert_filename]);
            }
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Pet listing updated successfully']);
        
    } catch(PDOException $e) {
        if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function deletePetListing() {
    global $host, $dbname, $username, $password;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['listing_id']) || !isset($input['seller_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $pet_id = intval($input['listing_id']);
    $seller_id = intval($input['seller_id']);
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT seller_id FROM pets WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$listing || intval($listing['seller_id']) !== $seller_id) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized or listing not found']);
            exit;
        }
        
        $conn->beginTransaction();
        
        // Delete images from filesystem
        $stmt = $conn->prepare("SELECT image_url FROM pet_images WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($photos as $photo) {
            $file_path = $photo['image_url'];
            if (file_exists($file_path)) unlink($file_path);
        }
        
        // Delete image records
        $stmt = $conn->prepare("DELETE FROM pet_images WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        
        // Delete certificate files
        $stmt = $conn->prepare("SELECT certificate_file FROM certificates WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($certificates as $cert) {
            $file_path = $cert['certificate_file'];
            if (file_exists($file_path)) unlink($file_path);
        }
        
        // Delete certificate records
        $stmt = $conn->prepare("DELETE FROM certificates WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        
        // Delete the pet listing
        $stmt = $conn->prepare("DELETE FROM pets WHERE pet_id = ? AND seller_id = ?");
        $stmt->execute([$pet_id, $seller_id]);
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Listing deleted successfully']);
        
    } catch(PDOException $e) {
        if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
