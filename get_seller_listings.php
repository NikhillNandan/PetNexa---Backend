<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db.php';

$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

if ($seller_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid seller ID'
    ]);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all listings for this seller
    $stmt = $conn->prepare("
        SELECT 
            pl.listing_id,
            pl.pet_type,
            pl.breed,
            pl.age,
            pl.gender,
            pl.price,
            pl.description,
            pl.status,
            pl.created_at,
            pl.updated_at,
            COUNT(DISTINCT pp.photo_id) as photo_count,
            COUNT(DISTINCT pc.certificate_id) as certificate_count
        FROM pet_listings pl
        LEFT JOIN pet_photos pp ON pl.listing_id = pp.listing_id
        LEFT JOIN pet_certificates pc ON pl.listing_id = pc.listing_id
        WHERE pl.seller_id = ?
        GROUP BY pl.listing_id
        ORDER BY pl.created_at DESC
    ");
    
    $stmt->execute([$seller_id]);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get photos and certificates for each listing
    $formatted_listings = [];
    foreach ($listings as $listing) {
        $listing_id = $listing['listing_id'];
        
        // Get primary photo
        $stmt = $conn->prepare("
            SELECT photo_url 
            FROM pet_photos 
            WHERE listing_id = ? 
            ORDER BY is_primary DESC, photo_id ASC 
            LIMIT 1
        ");
        $stmt->execute([$listing_id]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $formatted_listings[] = [
            'listing_id' => intval($listing['listing_id']),
            'pet_type' => $listing['pet_type'],
            'breed' => $listing['breed'],
            'age' => $listing['age'],
            'gender' => $listing['gender'],
            'price' => floatval($listing['price']),
            'description' => $listing['description'],
            'status' => $listing['status'],
            'photo_url' => $photo ? $photo['photo_url'] : null,
            'photo_count' => intval($listing['photo_count']),
            'certificate_count' => intval($listing['certificate_count']),
            'created_at' => $listing['created_at'],
            'updated_at' => $listing['updated_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'listings' => $formatted_listings,
        'total_count' => count($formatted_listings)
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
