<?php
/**
 * CONSOLIDATED REVIEW API
 * Handles all review operations
 * 
 * Endpoints:
 * - get: Get reviews (doctor/spa)
 * - submit: Submit a review
 * - get_my_reviews: Get reviews written by a user
 * - get_reviewable_transactions: Get completed transactions awaiting review
 */

header('Content-Type: application/json');
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get':
        getReviews();
        break;
    
    case 'submit':
        submitReview();
        break;
    
    case 'get_my_reviews':
        getMyReviews();
        break;
    
    case 'get_reviewable_transactions':
        getReviewableTransactions();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// ========================================
// FUNCTION: Get reviews
// ========================================
function getReviews() {
    global $conn;
    
    $type = isset($_GET['type']) ? $_GET['type'] : 'doctor';
    $target_id = isset($_GET['target_id']) ? intval($_GET['target_id']) : 0;
    
    if ($target_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid target ID']);
        return;
    }
    
    try {
        if ($type === 'spa') {
            $sql = "SELECT sr.*, u.full_name as reviewer_name, u.profile_image
                    FROM spa_reviews sr
                    JOIN users u ON sr.user_id = u.user_id
                    WHERE sr.spa_id = ?
                    ORDER BY sr.created_at DESC";
        } else {
            $sql = "SELECT r.*, u.full_name as reviewer_name, u.profile_image
                    FROM reviews r
                    JOIN users u ON r.reviewer_id = u.user_id
                    WHERE r.target_user_id = ?
                    ORDER BY r.created_at DESC";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $target_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        
        echo json_encode(['success' => true, 'reviews' => $reviews]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Submit review
// ========================================
function submitReview() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $type = isset($data['type']) ? $data['type'] : 'doctor';
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $target_id = isset($data['target_id']) ? intval($data['target_id']) : 0;
    $rating = isset($data['rating']) ? intval($data['rating']) : 0;
    $comment = isset($data['comment']) ? $data['comment'] : '';
    
    if ($user_id <= 0 || $target_id <= 0 || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    try {
        if ($type === 'spa') {
            $sql = "INSERT INTO spa_reviews (spa_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $target_id, $user_id, $rating, $comment);
        } else {
            $sql = "INSERT INTO reviews (target_user_id, reviewer_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $target_id, $user_id, $rating, $comment);
        }
        
        if ($stmt->execute()) {
            // Notify the reviewed provider
            require_once 'send_fcm.php';
            if ($type === 'spa') {
                // Auto-update spa_profiles rating and total_reviews
                $updateSpa = $conn->prepare(
                    "UPDATE spa_profiles SET 
                        rating = (SELECT COALESCE(AVG(rating), 0) FROM spa_reviews WHERE spa_id = ?),
                        total_reviews = (SELECT COUNT(*) FROM spa_reviews WHERE spa_id = ?)
                     WHERE spa_id = ?"
                );
                $updateSpa->bind_param("iii", $target_id, $target_id, $target_id);
                $updateSpa->execute();
                $updateSpa->close();

                // Get spa owner user_id for notification
                $ownerStmt = $conn->prepare("SELECT user_id FROM spa_profiles WHERE spa_id = ?");
                $ownerStmt->bind_param("i", $target_id);
                $ownerStmt->execute();
                $ownerRow = $ownerStmt->get_result()->fetch_assoc();
                if ($ownerRow) {
                    sendFCMNotification($ownerRow['user_id'], 'New Review!',
                        'You received a ' . $rating . '-star review. Check it out!',
                        'review', 0);
                }
                $ownerStmt->close();
            } else {
                sendFCMNotification($target_id, 'New Review!',
                    'You received a ' . $rating . '-star review. Check it out!',
                    'review', 0);
            }

            echo json_encode(['success' => true, 'message' => 'Review submitted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Get reviews written by a user
// ========================================
function getMyReviews() {
    global $conn;
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        $reviews = [];
        
        // Get doctor reviews by this user
        $sql = "SELECT r.review_id, r.rating, r.comment AS review_text, r.created_at,
                       u.full_name AS target_name, 'DOCTOR' AS review_type
                FROM reviews r
                LEFT JOIN users u ON r.target_user_id = u.user_id
                WHERE r.reviewer_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
            $stmt->close();
        }
        
        // Get spa reviews by this user
        $sql2 = "SELECT sr.review_id, sr.rating, sr.review_text, sr.created_at,
                        sp.spa_name AS target_name, 'SPA' AS review_type
                 FROM spa_reviews sr
                 LEFT JOIN spa_profiles sp ON sr.spa_id = sp.spa_id
                 WHERE sr.user_id = ?
                 ORDER BY sr.created_at DESC";
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2) {
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            while ($row = $result2->fetch_assoc()) {
                $reviews[] = $row;
            }
            $stmt2->close();
        }
        
        // Sort all by date descending
        usort($reviews, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        echo json_encode(['success' => true, 'reviews' => $reviews, 'total' => count($reviews)]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Get completed transactions awaiting review
// ========================================
function getReviewableTransactions() {
    global $conn;
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        $transactions = [];
        
        // Completed spa bookings not yet reviewed
        $sql = "SELECT sb.booking_id, sb.booking_date, sb.total_amount,
                       sp.spa_name AS provider_name, sp.spa_id AS target_id,
                       ss.service_name AS title, 'SPA' AS category
                FROM spa_bookings sb
                INNER JOIN spa_profiles sp ON sb.spa_id = sp.spa_id
                LEFT JOIN spa_services ss ON sb.service_id = ss.service_id
                WHERE sb.buyer_id = ?
                  AND (sb.booking_status = 'COMPLETED' OR sb.status = 'completed')
                  AND NOT EXISTS (
                      SELECT 1 FROM spa_reviews sr
                      WHERE sr.spa_id = sb.spa_id AND sr.user_id = sb.buyer_id
                  )
                ORDER BY sb.booking_date DESC";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row['total_amount'] = '₹' . number_format($row['total_amount']);
                $row['date'] = date('M d, Y', strtotime($row['booking_date']));
                if (empty($row['title'])) $row['title'] = 'Spa Service';
                $transactions[] = $row;
            }
            $stmt->close();
        }
        
        // Completed doctor appointments not yet reviewed
        $sql2 = "SELECT da.appointment_id, da.appointment_date, da.treatment_charge AS total_amount,
                        u.full_name AS provider_name, da.doctor_id AS target_id,
                        da.service_name AS title, 'DOCTOR' AS category
                 FROM doctor_appointments da
                 INNER JOIN users u ON da.doctor_id = u.user_id
                 WHERE da.buyer_id = ?
                   AND da.consultation_status = 'COMPLETED'
                   AND NOT EXISTS (
                       SELECT 1 FROM reviews r
                       WHERE r.target_user_id = da.doctor_id AND r.reviewer_id = da.buyer_id
                   )
                 ORDER BY da.appointment_date DESC";
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2) {
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            while ($row = $result2->fetch_assoc()) {
                $row['total_amount'] = '₹' . number_format($row['total_amount']);
                $row['date'] = date('M d, Y', strtotime($row['appointment_date']));
                if (empty($row['title'])) $row['title'] = 'Consultation';
                $transactions[] = $row;
            }
            $stmt2->close();
        }
        
        // Completed pet purchases not yet reviewed (seller reviews)
        $sql3 = "SELECT pt.transaction_id, pt.transaction_date, pt.amount AS total_amount,
                        u.full_name AS provider_name, pt.seller_id AS target_id,
                        p.pet_name AS title, 'SELLER' AS category
                 FROM pet_transactions pt
                 INNER JOIN users u ON pt.seller_id = u.user_id
                 LEFT JOIN pets p ON pt.pet_id = p.pet_id
                 WHERE pt.buyer_id = ?
                   AND pt.payment_status = 'CONFIRMED'
                   AND NOT EXISTS (
                       SELECT 1 FROM reviews r
                       WHERE r.target_user_id = pt.seller_id AND r.reviewer_id = pt.buyer_id
                   )
                 ORDER BY pt.transaction_date DESC";
        $stmt3 = $conn->prepare($sql3);
        if ($stmt3) {
            $stmt3->bind_param("i", $user_id);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            while ($row = $result3->fetch_assoc()) {
                $row['total_amount'] = '₹' . number_format($row['total_amount']);
                $row['date'] = date('M d, Y', strtotime($row['transaction_date']));
                if (empty($row['title'])) $row['title'] = 'Pet Purchase';
                $transactions[] = $row;
            }
            $stmt3->close();
        }
        
        echo json_encode(['success' => true, 'transactions' => $transactions, 'total' => count($transactions)]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

?>
