<?php
header('Content-Type: application/json');

// Suppress PHP warnings/notices from polluting JSON output
error_reporting(0);
ini_set('display_errors', 0);

require_once 'db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_notifications':
        getNotifications();
        break;
    case 'get_unread_count':
        getUnreadCount();
        break;
    case 'mark_read':
        markRead();
        break;
    case 'mark_all_read':
        markAllRead();
        break;
    case 'register_token':
        registerToken();
        break;
    case 'delete_notification':
        deleteNotification();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getNotifications() {
    global $conn;
    $userId = intval($_GET['user_id'] ?? 0);
    $page = intval($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;

    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user_id']);
        return;
    }

    // Get notifications
    $stmt = $conn->prepare("SELECT notification_id, user_id, title, message, type, reference_id, data, is_read, created_at
                            FROM notifications WHERE user_id = ? AND type != 'chat' ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $userId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();

    // Get unread count
    $stmt2 = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0 AND type != 'chat'");
    $stmt2->bind_param("i", $userId);
    $stmt2->execute();
    $countResult = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    // Get user role
    $stmt3 = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt3->bind_param("i", $userId);
    $stmt3->execute();
    $userResult = $stmt3->get_result()->fetch_assoc();
    $stmt3->close();

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => intval($countResult['count']),
        'role' => $userResult['role'] ?? 'BUYER',
        'page' => $page
    ]);
}

function getUnreadCount() {
    global $conn;
    $userId = intval($_GET['user_id'] ?? 0);

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0 AND type != 'chat'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode(['success' => true, 'unread_count' => intval($result['count'])]);
}

function markRead() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $notificationId = intval($data['notification_id'] ?? 0);

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
}

function markAllRead() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = intval($data['user_id'] ?? 0);

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    echo json_encode(['success' => true, 'marked' => $affected]);
}

function registerToken() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = intval($data['user_id'] ?? 0);
    $token = $data['fcm_token'] ?? '';

    if ($userId <= 0 || empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid user_id or token']);
        return;
    }

    $stmt = $conn->prepare("UPDATE users SET fcm_token = ? WHERE user_id = ?");
    $stmt->bind_param("si", $token, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Token registered']);
}

function deleteNotification() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $notificationId = intval($data['notification_id'] ?? 0);

    if ($notificationId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification_id']);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'Notification deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Notification not found or already deleted']);
    }
}
?>
