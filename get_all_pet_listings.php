<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db.php';

// Optional filters
$species = isset($_GET['species']) ? $_GET['species'] : null;       // Dog, Cat, etc.
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$breed = isset($_GET['breed']) ? $_GET['breed'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;    // Search by name/breed
$user_lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$user_lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$radius_km = 20; // 20km radius filter

try {
    $conn_pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build query using correct table: pets
    // Build distance select if lat/lng provided
    $distance_select = '';
    $having_clause = '';
    if ($user_lat !== null && $user_lng !== null && $user_lat != 0 && $user_lng != 0) {
        $distance_select = ",
            (6371 * acos(
                cos(radians(?)) * cos(radians(u.latitude)) *
                cos(radians(u.longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(u.latitude))
            )) AS distance_km";
        $having_clause = " HAVING distance_km <= $radius_km";
    }

    $query = "
        SELECT 
            p.pet_id,
            p.pet_name,
            p.species,
            p.breed,
            p.age,
            p.gender,
            p.color,
            p.price,
            p.description,
            p.availability_status,
            p.seller_id,
            p.created_at,
            u.full_name AS seller_name,
            u.phone AS seller_phone,
            u.profile_image AS seller_image,
            u.latitude AS seller_lat,
            u.longitude AS seller_lng,
            (SELECT pi.image_url FROM pet_images pi WHERE pi.pet_id = p.pet_id LIMIT 1) AS photo_url
            $distance_select
        FROM pets p
        INNER JOIN users u ON p.seller_id = u.user_id
        WHERE p.availability_status = 'AVAILABLE'
    ";
    
    $params = [];
    
    // Add lat/lng params for distance calculation
    if ($user_lat !== null && $user_lng !== null && $user_lat != 0 && $user_lng != 0) {
        $params[] = $user_lat;
        $params[] = $user_lng;
        $params[] = $user_lat;
    }
    
    // Filter by species (Dog / Cat)
    if ($species && strtolower($species) !== 'all') {
        $query .= " AND LOWER(p.species) = LOWER(?)";
        $params[] = $species;
    }
    
    if ($min_price !== null) {
        $query .= " AND p.price >= ?";
        $params[] = $min_price;
    }
    
    if ($max_price !== null) {
        $query .= " AND p.price <= ?";
        $params[] = $max_price;
    }
    
    if ($breed) {
        $query .= " AND p.breed LIKE ?";
        $params[] = '%' . $breed . '%';
    }
    
    // Search by pet name or breed
    if ($search) {
        $query .= " AND (p.pet_name LIKE ? OR p.breed LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    
    if (!empty($having_clause)) {
        $query .= $having_clause;
    }
    
    if ($user_lat !== null && $user_lng !== null && $user_lat != 0 && $user_lng != 0) {
        $query .= " ORDER BY distance_km ASC";
    } else {
        $query .= " ORDER BY p.created_at DESC";
    }
    
    $stmt = $conn_pdo->prepare($query);
    $stmt->execute($params);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response
    $formatted = [];
    foreach ($pets as $pet) {
        $formatted[] = [
            'pet_id'       => intval($pet['pet_id']),
            'listing_id'   => intval($pet['pet_id']),  // alias for backward compat
            'pet_name'     => $pet['pet_name'],
            'species'      => $pet['species'],
            'breed'        => $pet['breed'],
            'age'          => intval($pet['age']),
            'gender'       => $pet['gender'],
            'color'        => $pet['color'],
            'price'        => floatval($pet['price']),
            'description'  => $pet['description'],
            'status'       => strtolower($pet['availability_status']),
            'seller_id'    => intval($pet['seller_id']),
            'seller_name'  => $pet['seller_name'],
            'seller_phone' => $pet['seller_phone'],
            'seller_image' => $pet['seller_image'],
            'photo_url'    => $pet['photo_url'],
            'image_url'    => $pet['photo_url'],  // alias for backward compat
            'created_at'   => $pet['created_at'],
            'distance_km'  => isset($pet['distance_km']) ? round((float)$pet['distance_km'], 1) : null
        ];
    }
    
    echo json_encode([
        'success'     => true,
        'listings'    => $formatted,
        'pets'        => $formatted,  // alias for backward compat
        'total_count' => count($formatted)
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Database error: ' . $e->getMessage()
    ]);
}
?>
