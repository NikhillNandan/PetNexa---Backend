<?php
header('Content-Type: application/json');
require_once 'db.php';

$buyer_id = isset($_GET['buyer_id']) ? intval($_GET['buyer_id']) : 0;

if ($buyer_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid buyer ID']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            p.pet_id,
            p.seller_id,
            p.pet_name,
            p.species,
            p.breed,
            p.age,
            p.price,
            p.gender,
            p.description,
            p.availability_status,
            u.full_name as seller_name,
            u.city as seller_city,
            (SELECT image_url FROM pet_images WHERE pet_id = p.pet_id LIMIT 1) as image_url,
            sp.saved_at
        FROM saved_pets sp
        JOIN pets p ON sp.pet_id = p.pet_id
        LEFT JOIN users u ON p.seller_id = u.user_id
        WHERE sp.buyer_id = ?
        ORDER BY sp.saved_at DESC
    ");
    
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pets = [];
    while ($row = $result->fetch_assoc()) {
        $row['status'] = strtolower($row['availability_status']);
        $row['price'] = (float)$row['price'];
        $row['age'] = (int)$row['age'];
        $pets[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'pets' => $pets
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
