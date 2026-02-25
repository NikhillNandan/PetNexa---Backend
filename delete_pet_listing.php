<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['listing_id']) || !isset($input['seller_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields'
    ]);
    exit;
}

$listing_id = intval($input['listing_id']);
$seller_id = intval($input['seller_id']);

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
    
    // Start transaction
    $conn->beginTransaction();
    
    // Delete all photos
    $stmt = $conn->prepare("SELECT photo_url FROM pet_photos WHERE listing_id = ?");
    $stmt->execute([$listing_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($photos as $photo) {
        $file_path = '../' . $photo['photo_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM pet_photos WHERE listing_id = ?");
    $stmt->execute([$listing_id]);
    
    // Delete all certificates
    $stmt = $conn->prepare("SELECT certificate_url FROM pet_certificates WHERE listing_id = ?");
    $stmt->execute([$listing_id]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($certificates as $cert) {
        $file_path = '../' . $cert['certificate_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM pet_certificates WHERE listing_id = ?");
    $stmt->execute([$listing_id]);
    
    // Delete listing
    $stmt = $conn->prepare("DELETE FROM pet_listings WHERE listing_id = ? AND seller_id = ?");
    $stmt->execute([$listing_id, $seller_id]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Listing deleted successfully'
    ]);
    
} catch(PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
