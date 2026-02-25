<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$buyer_id = isset($_GET['buyer_id']) ? intval($_GET['buyer_id']) : 0;

if ($buyer_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid buyer ID']);
    exit;
}

try {
    // Get total purchases
    $purchases = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM pet_transactions WHERE buyer_id = ? AND payment_status = 'SUCCESS'");
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $purchases = $result->fetch_assoc()['count'];

    // Get saved pets (wishlist) count
    $wishlist = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM saved_pets WHERE user_id = ?");
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $wishlist = $result->fetch_assoc()['count'];

    // Get reviews count
    $reviews = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result->fetch_assoc()['count'];

    echo json_encode([
        'success' => true,
        'purchases' => intval($purchases),
        'wishlist' => intval($wishlist),
        'reviews' => intval($reviews)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
