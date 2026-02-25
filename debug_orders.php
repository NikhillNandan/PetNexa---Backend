<?php
header('Content-Type: application/json');
require_once 'db.php';

$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

// Show all orders for this seller with raw data
$result = $conn->query("SELECT * FROM pet_transactions WHERE seller_id = $seller_id OR $seller_id = 0 ORDER BY transaction_id DESC LIMIT 10");

$rows = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

// Also show table columns
$columns = [];
$colResult = $conn->query("SHOW COLUMNS FROM pet_transactions");
if ($colResult) {
    while ($col = $colResult->fetch_assoc()) {
        $columns[] = $col['Field'] . ' (' . $col['Type'] . ')';
    }
}

echo json_encode([
    'columns' => $columns,
    'row_count' => count($rows),
    'rows' => $rows
], JSON_PRETTY_PRINT);
?>
