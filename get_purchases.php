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
            p.purchase_id,
            p.purchase_type,
            p.item_name,
            p.amount,
            p.quantity,
            p.status,
            p.payment_method,
            p.purchase_date,
            p.seller_id,
            u.full_name as seller_name,
            (SELECT COUNT(*) FROM reviews r 
             WHERE r.target_user_id = p.seller_id AND r.reviewer_id = p.buyer_id) as has_reviewed,
            (SELECT pp.photo_url FROM pet_photos pp 
             INNER JOIN pet_listings pl ON pp.listing_id = pl.listing_id 
             WHERE pl.listing_id = p.listing_id 
             ORDER BY pp.is_primary DESC, pp.photo_id ASC 
             LIMIT 1) as image_url
        FROM purchases p
        LEFT JOIN users u ON p.seller_id = u.user_id
        WHERE p.buyer_id = ?
        ORDER BY p.purchase_date DESC
    ");
    
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $purchases = [];
    while ($row = $result->fetch_assoc()) {
        $purchases[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'purchases' => $purchases
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
