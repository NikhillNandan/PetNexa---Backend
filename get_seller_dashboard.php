<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
require_once 'db.php';

// Get parameters
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

if ($seller_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid seller ID']);
    exit;
}

try {
    // Get active listings count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM pets WHERE seller_id = ? AND availability_status = 'AVAILABLE'");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $active_listings = $result->fetch_assoc()['count'];
    
    // Get average rating from reviews
    $avg_rating = 0.0;
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE target_user_id = ?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $avg_rating = $row['avg_rating'] ? round(floatval($row['avg_rating']), 1) : 0.0;
    
    // Get total sold pets
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM pets WHERE seller_id = ? AND availability_status = 'SOLD'");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_sales = $result->fetch_assoc()['count'];
    
    // Get total earnings from pet_transactions
    $total_earnings = 0;
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM pet_transactions WHERE seller_id = ? AND payment_status = 'SUCCESS'");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_earnings = $row['total'] ? floatval($row['total']) : 0;
    
    // Get this month's earnings
    $this_month_earnings = 0;
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM pet_transactions WHERE seller_id = ? AND payment_status = 'SUCCESS' AND MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND YEAR(transaction_date) = YEAR(CURRENT_DATE())");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $this_month_earnings = $row['total'] ? floatval($row['total']) : 0;
    
    // Get total reviews count
    $total_reviews = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE target_user_id = ?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_reviews = $result->fetch_assoc()['count'];
    $stmt->close();
    
    // Prepare response
    $response = [
        'success' => true,
        'active_listings' => intval($active_listings),
        'avg_rating' => $avg_rating,
        'total_sales' => intval($total_sales),
        'total_earnings' => $total_earnings,
        'this_month_earnings' => $this_month_earnings,
        'total_reviews' => intval($total_reviews)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
