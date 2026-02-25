<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db.php';

// Support both listing_id and pet_id param names
$pet_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
if ($pet_id <= 0) {
    $pet_id = isset($_GET['pet_id']) ? intval($_GET['pet_id']) : 0;
}

if ($pet_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid pet/listing ID'
    ]);
    exit;
}

try {
    $conn_pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get pet details from correct table: pets
    $stmt = $conn_pdo->prepare("
        SELECT 
            p.pet_id,
            p.seller_id,
            p.pet_name,
            p.species,
            p.breed,
            p.age,
            p.gender,
            p.color,
            p.price,
            p.description,
            p.availability_status,
            p.created_at,
            u.full_name AS seller_name,
            u.phone AS seller_phone,
            u.email AS seller_email,
            u.profile_image AS seller_image
        FROM pets p
        INNER JOIN users u ON p.seller_id = u.user_id
        WHERE p.pet_id = ?
    ");
    
    $stmt->execute([$pet_id]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pet) {
        echo json_encode([
            'success' => false,
            'error' => 'Pet not found'
        ]);
        exit;
    }
    
    // Get all photos from correct table: pet_images
    $stmt = $conn_pdo->prepare("
        SELECT image_id, image_url
        FROM pet_images
        WHERE pet_id = ?
        ORDER BY image_id ASC
    ");
    $stmt->execute([$pet_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Primary image URL
    $primary_image = count($images) > 0 ? $images[0]['image_url'] : null;
    
    // Get certificates
    $stmt = $conn_pdo->prepare("
        SELECT certificate_id, certificate_type, certificate_file, issued_date
        FROM certificates
        WHERE pet_id = ?
        ORDER BY certificate_type ASC
    ");
    $stmt->execute([$pet_id]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Determine vaccinated status from certificates
    $has_vaccination_cert = false;
    foreach ($certificates as $cert) {
        $cert_type = strtolower($cert['certificate_type'] ?? '');
        if (strpos($cert_type, 'vaccin') !== false || strpos($cert_type, 'health') !== false) {
            $has_vaccination_cert = true;
            break;
        }
    }

    $formatted = [
        'pet_id'       => intval($pet['pet_id']),
        'listing_id'   => intval($pet['pet_id']),   // backward compat
        'seller_id'    => intval($pet['seller_id']),
        'seller_name'  => $pet['seller_name'],
        'seller_phone' => $pet['seller_phone'],
        'seller_email' => $pet['seller_email'],
        'seller_image' => $pet['seller_image'],
        'pet_name'     => $pet['pet_name'],
        'species'      => $pet['species'],
        'breed'        => $pet['breed'],
        'age'          => intval($pet['age']),
        'gender'       => $pet['gender'],
        'color'        => $pet['color'] ?: '',
        'price'        => floatval($pet['price']),
        'description'  => $pet['description'],
        'status'       => strtolower($pet['availability_status']),
        'vaccinated'   => $has_vaccination_cert,
        'microchipped' => false,
        'weight'       => '',
        'seller_verified' => false,
        'photo_url'    => $primary_image,
        'image_url'    => $primary_image,   // alias
        'images'       => $images,
        'certificates' => $certificates,
        'created_at'   => $pet['created_at']
    ];
    
    echo json_encode([
        'success' => true,
        'listing' => $formatted
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
