<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'get_seller_listings':
        getSellerListings();
        break;
    case 'get_all_listings':
        getAllPetListings();
        break;
    case 'get_details':
        getListingDetails();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action. Use: get_seller_listings, get_all_listings, get_details']);
        exit;
}

function getSellerListings() {
    global $host, $dbname, $username, $password;
    
    $seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;
    
    if ($seller_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid seller ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("
            SELECT p.pet_id as listing_id, p.species as pet_type, p.breed, 
                   CAST(p.age AS CHAR) as age, p.gender, p.price, p.description, 
                   p.availability_status as status, p.created_at, p.created_at as updated_at,
                   COUNT(DISTINCT pi.image_id) as photo_count, 
                   COUNT(DISTINCT c.certificate_id) as certificate_count
            FROM pets p 
            LEFT JOIN pet_images pi ON p.pet_id = pi.pet_id 
            LEFT JOIN certificates c ON p.pet_id = c.pet_id
            WHERE p.seller_id = ? 
            GROUP BY p.pet_id 
            ORDER BY p.created_at DESC
        ");
        
        $stmt->execute([$seller_id]);
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formatted_listings = [];
        foreach ($listings as $listing) {
            $pet_id = $listing['listing_id'];
            
            // Get first photo
            $stmt = $conn->prepare("SELECT image_url FROM pet_images WHERE pet_id = ? ORDER BY image_id ASC LIMIT 1");
            $stmt->execute([$pet_id]);
            $photo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $formatted_listings[] = [
                'listing_id' => intval($listing['listing_id']),
                'pet_type' => $listing['pet_type'] ?? 'Unknown',
                'breed' => $listing['breed'] ?? 'Unknown',
                'age' => $listing['age'] ?? '0',
                'gender' => $listing['gender'] ?? 'MALE',
                'price' => floatval($listing['price']),
                'description' => $listing['description'] ?? '',
                'status' => strtolower($listing['status'] ?? 'available'),
                'photo_url' => $photo ? $photo['image_url'] : null,
                'photo_count' => intval($listing['photo_count']),
                'certificate_count' => intval($listing['certificate_count']),
                'created_at' => $listing['created_at'] ?? '',
                'updated_at' => $listing['updated_at'] ?? ''
            ];
        }
        
        echo json_encode(['success' => true, 'listings' => $formatted_listings, 'total_count' => count($formatted_listings)]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getAllPetListings() {
    global $host, $dbname, $username, $password;
    
    $pet_type = isset($_GET['pet_type']) ? $_GET['pet_type'] : null;
    $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
    $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
    $breed = isset($_GET['breed']) ? $_GET['breed'] : null;
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $query = "SELECT p.pet_id as listing_id, p.species as pet_type, p.breed, 
                         CAST(p.age AS CHAR) as age, p.gender, p.price, p.description, 
                         p.availability_status as status, p.created_at,
                         u.full_name as seller_name, u.phone as seller_phone 
                  FROM pets p 
                  INNER JOIN users u ON p.seller_id = u.user_id 
                  WHERE p.availability_status = 'AVAILABLE'";
        
        $params = [];
        
        if ($pet_type) {
            $query .= " AND p.species = ?";
            $params[] = $pet_type;
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
        
        $query .= " ORDER BY p.created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formatted_listings = [];
        foreach ($listings as $listing) {
            $pet_id = $listing['listing_id'];
            
            $stmt = $conn->prepare("SELECT image_url FROM pet_images WHERE pet_id = ? ORDER BY image_id ASC LIMIT 1");
            $stmt->execute([$pet_id]);
            $photo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $formatted_listings[] = [
                'listing_id' => intval($listing['listing_id']),
                'pet_type' => $listing['pet_type'],
                'breed' => $listing['breed'],
                'age' => $listing['age'],
                'gender' => $listing['gender'],
                'price' => floatval($listing['price']),
                'description' => $listing['description'],
                'status' => strtolower($listing['status']),
                'seller_name' => $listing['seller_name'],
                'seller_phone' => $listing['seller_phone'],
                'photo_url' => $photo ? $photo['image_url'] : null,
                'created_at' => $listing['created_at']
            ];
        }
        
        echo json_encode(['success' => true, 'listings' => $formatted_listings, 'total_count' => count($formatted_listings)]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getListingDetails() {
    global $host, $dbname, $username, $password;
    
    $listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
    
    if ($listing_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid listing ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("
            SELECT p.pet_id as listing_id, p.seller_id, p.species as pet_type, p.breed, 
                   CAST(p.age AS CHAR) as age, p.gender, p.price, p.description, 
                   p.availability_status as status, p.created_at, p.created_at as updated_at,
                   u.full_name as seller_name, u.phone as seller_phone, u.email as seller_email 
            FROM pets p 
            INNER JOIN users u ON p.seller_id = u.user_id 
            WHERE p.pet_id = ?
        ");
        
        $stmt->execute([$listing_id]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$listing) {
            echo json_encode(['success' => false, 'error' => 'Listing not found']);
            exit;
        }
        
        // Get photos
        $stmt = $conn->prepare("SELECT image_id as photo_id, image_url as photo_url, 0 as is_primary FROM pet_images WHERE pet_id = ? ORDER BY image_id ASC");
        $stmt->execute([$listing_id]);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get certificates
        $stmt = $conn->prepare("SELECT certificate_id, certificate_type, certificate_file as certificate_url, issued_date as uploaded_at FROM certificates WHERE pet_id = ? ORDER BY certificate_type");
        $stmt->execute([$listing_id]);
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get buyer/transaction info if sold or reserved
        $buyer_info = null;
        $stmt = $conn->prepare("
            SELECT pt.transaction_id, pt.amount as transaction_amount, pt.payment_method, 
                   pt.payment_status, pt.transaction_date,
                   u.full_name as buyer_name, u.phone as buyer_phone, u.email as buyer_email
            FROM pet_transactions pt
            INNER JOIN users u ON pt.buyer_id = u.user_id
            WHERE pt.pet_id = ?
            ORDER BY pt.transaction_date DESC LIMIT 1
        ");
        $stmt->execute([$listing_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($transaction) {
            $buyer_info = [
                'buyer_name' => $transaction['buyer_name'],
                'buyer_phone' => $transaction['buyer_phone'],
                'buyer_email' => $transaction['buyer_email'],
                'transaction_amount' => floatval($transaction['transaction_amount']),
                'payment_method' => $transaction['payment_method'],
                'payment_status' => $transaction['payment_status'],
                'transaction_date' => $transaction['transaction_date']
            ];
        }
        
        $formatted_listing = [
            'listing_id' => intval($listing['listing_id']),
            'seller_id' => intval($listing['seller_id']),
            'seller_name' => $listing['seller_name'],
            'seller_phone' => $listing['seller_phone'],
            'seller_email' => $listing['seller_email'],
            'pet_type' => $listing['pet_type'],
            'breed' => $listing['breed'],
            'age' => $listing['age'],
            'gender' => $listing['gender'],
            'price' => floatval($listing['price']),
            'description' => $listing['description'],
            'status' => strtolower($listing['status']),
            'photos' => $photos,
            'certificates' => $certificates,
            'buyer_info' => $buyer_info,
            'created_at' => $listing['created_at'],
            'updated_at' => $listing['updated_at']
        ];
        
        echo json_encode(['success' => true, 'listing' => $formatted_listing]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
