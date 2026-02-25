<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

require_once 'db.php';

$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

if ($seller_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid seller_id"]);
    exit;
}

// Total earnings (CONFIRMED orders only)
$totalEarnings = 0;
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM pet_transactions WHERE seller_id = $seller_id AND payment_status = 'CONFIRMED'");
if ($result && $row = $result->fetch_assoc()) {
    $totalEarnings = floatval($row['total']);
}

// This month earnings (CONFIRMED)
$monthEarnings = 0;
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM pet_transactions WHERE seller_id = $seller_id AND payment_status = 'CONFIRMED' AND MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE())");
if ($result && $row = $result->fetch_assoc()) {
    $monthEarnings = floatval($row['total']);
}

// This week earnings (CONFIRMED)
$weekEarnings = 0;
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM pet_transactions WHERE seller_id = $seller_id AND payment_status = 'CONFIRMED' AND YEARWEEK(transaction_date, 1) = YEARWEEK(CURDATE(), 1)");
if ($result && $row = $result->fetch_assoc()) {
    $weekEarnings = floatval($row['total']);
}

// Today earnings (CONFIRMED)
$todayEarnings = 0;
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM pet_transactions WHERE seller_id = $seller_id AND payment_status = 'CONFIRMED' AND DATE(transaction_date) = CURDATE()");
if ($result && $row = $result->fetch_assoc()) {
    $todayEarnings = floatval($row['total']);
}

// Pending amount (BOOKED orders)
$pendingAmount = 0;
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM pet_transactions WHERE seller_id = $seller_id AND payment_status = 'BOOKED'");
if ($result && $row = $result->fetch_assoc()) {
    $pendingAmount = floatval($row['total']);
}

// Order statistics
$totalOrders = 0;
$confirmedOrders = 0;
$pendingOrders = 0;
$rejectedOrders = 0;

$result = $conn->query("SELECT payment_status, COUNT(*) as cnt FROM pet_transactions WHERE seller_id = $seller_id GROUP BY payment_status");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtoupper($row['payment_status']);
        $count = intval($row['cnt']);
        $totalOrders += $count;
        if ($status === 'CONFIRMED') $confirmedOrders = $count;
        else if ($status === 'BOOKED') $pendingOrders = $count;
        else if ($status === 'REJECTED') $rejectedOrders = $count;
    }
}

// Recent transactions (last 10)
$transactions = [];
$result = $conn->query("
    SELECT t.transaction_id, t.pet_id, t.buyer_id, t.amount, 
           t.payment_status, t.payment_method, t.transaction_date,
           p.pet_name, p.breed, p.species as pet_type,
           u.full_name as buyer_name
    FROM pet_transactions t
    LEFT JOIN pets p ON t.pet_id = p.pet_id
    LEFT JOIN users u ON t.buyer_id = u.user_id
    WHERE t.seller_id = $seller_id
    ORDER BY t.transaction_date DESC
    LIMIT 10
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['amount'] = floatval($row['amount']);
        $transactions[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "total_earnings" => $totalEarnings,
    "month_earnings" => $monthEarnings,
    "week_earnings" => $weekEarnings,
    "today_earnings" => $todayEarnings,
    "pending_amount" => $pendingAmount,
    "total_orders" => $totalOrders,
    "confirmed_orders" => $confirmedOrders,
    "pending_orders" => $pendingOrders,
    "rejected_orders" => $rejectedOrders,
    "recent_transactions" => $transactions
]);

$conn->close();
?>
