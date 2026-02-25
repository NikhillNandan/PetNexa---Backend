<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['listing_id']) || !isset($input['seller_id']) || !isset($input['pet_type']) || 
    !isset($input['breed']) || !isset($input['age']) || !isset($input['gender']) || 
    !isset($input['price']) || !isset($input['description'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields'
    ]);
    exit;
}

$listing_id = intval($input['listing_id']);
$seller_id = intval($input['seller_id']);
$pet_type = $input['pet_type'];
$breed = $input['breed'];
$age = $input['age'];
$gender = $input['gender'];
$price = floatval($input['price']);
$description = $input['description'];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT seller_id FROM pet_listings WHERE listing_id = ?");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$listing || intval($listing['seller_id']) !== $seller_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized or listing not found'
        ]);
        exit;
    }
    
    // Update pet listing
    $stmt = $conn->prepare("
        UPDATE pet_listings 
        SET pet_type = ?, breed = ?, age = ?, gender = ?, price = ?, description = ?, updated_at = NOW()
        WHERE listing_id = ? AND seller_id = ?
    ");
    
    $stmt->execute([$pet_type, $breed, $age, $gender, $price, $description, $listing_id, $seller_id]);
    
    // Handle new photos if provided
    if (isset($input['new_photos']) && is_array($input['new_photos']) && count($input['new_photos']) > 0) {
        $photos = $input['new_photos'];
        $upload_dir = '../uploads/pet_photos/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        foreach ($photos as $index => $base64_image) {
            $image_data = base64_decode($base64_image);
            $filename = 'pet_' . $listing_id . '_update_' . $index . '_' . time() . '.jpg';
            $filepath = $upload_dir . $filename;
            
            file_put_contents($filepath, $image_data);
            
            $photo_url = 'uploads/pet_photos/' . $filename;
            
            $stmt = $conn->prepare("
                INSERT INTO pet_photos (listing_id, photo_url, is_primary, uploaded_at)
                VALUES (?, ?, 0, NOW())
            ");
            $stmt->execute([$listing_id, $photo_url]);
        }
    }
    
    // Handle deleted photos if provided
    if (isset($input['deleted_photo_ids']) && is_array($input['deleted_photo_ids'])) {
        foreach ($input['deleted_photo_ids'] as $photo_id) {
            // Get photo path before deleting
            $stmt = $conn->prepare("SELECT photo_url FROM pet_photos WHERE photo_id = ? AND listing_id = ?");
            $stmt->execute([$photo_id, $listing_id]);
            $photo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($photo) {
                // Delete file
                $file_path = '../' . $photo['photo_url'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                // Delete record
                $stmt = $conn->prepare("DELETE FROM pet_photos WHERE photo_id = ? AND listing_id = ?");
                $stmt->execute([$photo_id, $listing_id]);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Pet listing updated successfully'
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
