<?php
/**
 * CONSOLIDATED TRANSACTION API
 * Handles earnings and transaction operations
 * 
 * Endpoints:
 * - get_earnings: Get earnings data
 * - get_transactions: Get transaction history
 */

header('Content-Type: application/json');
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_earnings':
        getEarnings();
        break;
    
    case 'get_transactions':
        getTransactions();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// ========================================
// FUNCTION: Get earnings data
// ========================================
function getEarnings() {
    global $conn;
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        $earnings = [];
        
        if ($role === 'SELLER') {
            $sql = "SELECT COALESCE(SUM(amount), 0) as total_earnings
                    FROM pet_transactions WHERE seller_id = ? AND payment_status = 'SUCCESS'";
        } elseif ($role === 'SPA_OWNER') {
            $sql = "SELECT COALESCE(SUM(total_amount), 0) as total_earnings
                    FROM spa_bookings WHERE spa_id IN (SELECT spa_id FROM spa_profiles WHERE user_id = ?)
                    AND booking_status = 'COMPLETED'";
        } elseif ($role === 'DOCTOR') {
            $sql = "SELECT COALESCE(SUM(treatment_charge), 0) as total_earnings
                    FROM doctor_appointments WHERE doctor_id = ? AND consultation_status = 'COMPLETED'";
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid role']);
            return;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'earnings' => [
                'total' => floatval($result['total_earnings'])
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Get transactions
// ========================================
function getTransactions() {
    global $conn;
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        $transactions = [];
        
        if ($role === 'SELLER') {
            $sql = "SELECT pt.*, p.species as pet_type, p.breed, p.pet_name,
                           u.full_name as buyer_name, u.phone as buyer_phone
                    FROM pet_transactions pt
                    LEFT JOIN pets p ON pt.pet_id = p.pet_id
                    LEFT JOIN users u ON pt.buyer_id = u.user_id
                    WHERE pt.seller_id = ? ORDER BY pt.transaction_date DESC";
        } elseif ($role === 'BUYER') {
            $sql = "SELECT pt.*, p.species as pet_type, p.breed, p.pet_name,
                           u.full_name as seller_name, u.phone as seller_phone
                    FROM pet_transactions pt
                    LEFT JOIN pets p ON pt.pet_id = p.pet_id
                    LEFT JOIN users u ON pt.seller_id = u.user_id
                    WHERE pt.buyer_id = ? ORDER BY pt.transaction_date DESC";
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid role']);
            return;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        
        echo json_encode(['success' => true, 'transactions' => $transactions]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

?>
