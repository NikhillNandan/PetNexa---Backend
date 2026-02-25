<?php
/**
 * CONSOLIDATED CHAT MANAGEMENT API
 * Handles all chat messaging operations
 * 
 * Endpoints:
 * - get_conversations: List all user conversations
 * - get_messages: Load message history
 * - send_message: Send text/image/file message
 * - mark_read: Update read receipts
 * - typing_status: Broadcast typing indicator
 */

header('Content-Type: application/json');
require_once 'db.php';

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    // ===== GET CONVERSATIONS =====
    case 'get_conversations':
        getConversations();
        break;
    
    // ===== GET MESSAGES =====
    case 'get_messages':
        getMessages();
        break;
    
    // ===== SEND MESSAGE =====
    case 'send_message':
        sendMessage();
        break;
    
    // ===== MARK MESSAGES READ =====
    case 'mark_read':
        markMessagesRead();
        break;
    
    // ===== UPDATE TYPING STATUS =====
    case 'typing_status':
        updateTypingStatus();
        break;

    // ===== GET TOTAL UNREAD COUNT =====
    case 'get_total_unread':
        getTotalUnread();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// ========================================
// FUNCTION: Get all conversations for a user
// ========================================
function getConversations() {
    global $conn;
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        // Get all unique conversations with last message and unread count
        $sql = "SELECT 
                    CASE 
                        WHEN cm.sender_id = ? THEN cm.receiver_id
                        ELSE cm.sender_id
                    END as other_user_id,
                    u.full_name as other_user_name,
                    u.profile_image as other_user_photo,
                    u.is_online,
                    u.last_seen,
                    (SELECT message_text FROM chat_messages 
                     WHERE (sender_id = ? AND receiver_id = other_user_id) 
                        OR (sender_id = other_user_id AND receiver_id = ?)
                     ORDER BY timestamp DESC LIMIT 1) as last_message,
                    (SELECT timestamp FROM chat_messages 
                     WHERE (sender_id = ? AND receiver_id = other_user_id) 
                        OR (sender_id = other_user_id AND receiver_id = ?)
                     ORDER BY timestamp DESC LIMIT 1) as last_message_time,
                    (SELECT COUNT(*) FROM chat_messages 
                     WHERE receiver_id = ? AND sender_id = other_user_id AND is_read = 0) as unread_count
                FROM chat_messages cm
                JOIN users u ON (
                    CASE 
                        WHEN cm.sender_id = ? THEN cm.receiver_id
                        ELSE cm.sender_id
                    END = u.user_id
                )
                WHERE cm.sender_id = ? OR cm.receiver_id = ?
                GROUP BY other_user_id
                ORDER BY last_message_time DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'conversations' => $conversations
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Get message history
// ========================================
function getMessages() {
    global $conn;
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $other_user_id = isset($_GET['other_user_id']) ? intval($_GET['other_user_id']) : 0;
    $last_message_id = isset($_GET['last_message_id']) ? intval($_GET['last_message_id']) : 0;
    
    if ($user_id <= 0 || $other_user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    try {
        // Get messages between two users
        $sql = "SELECT 
                    message_id,
                    sender_id,
                    receiver_id,
                    message_text,
                    message_type,
                    media_url,
                    file_name,
                    is_read,
                    is_delivered,
                    timestamp
                FROM chat_messages
                WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
                  AND message_id > ?
                ORDER BY timestamp ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiii", $user_id, $other_user_id, $other_user_id, $user_id, $last_message_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Send message
// ========================================
function sendMessage() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sender_id = isset($data['sender_id']) ? intval($data['sender_id']) : 0;
    $receiver_id = isset($data['receiver_id']) ? intval($data['receiver_id']) : 0;
    $message_text = isset($data['message_text']) ? $data['message_text'] : '';
    $message_type = isset($data['message_type']) ? $data['message_type'] : 'text';
    $media_url = isset($data['media_url']) ? $data['media_url'] : null;
    $file_name = isset($data['file_name']) ? $data['file_name'] : null;
    
    if ($sender_id <= 0 || $receiver_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user IDs']);
        return;
    }
    
    if (empty($message_text) && empty($media_url)) {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
        return;
    }
    
    try {
        $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message_text, message_type, media_url, file_name) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss", $sender_id, $receiver_id, $message_text, $message_type, $media_url, $file_name);
        
        if ($stmt->execute()) {
            $message_id = $conn->insert_id;
            
            // Get the inserted message
            $sql = "SELECT * FROM chat_messages WHERE message_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $message_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $message = $result->fetch_assoc();
            
            // Send push notification to receiver
            require_once 'send_fcm.php';
            $senderStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $senderStmt->bind_param("i", $sender_id);
            $senderStmt->execute();
            $senderRow = $senderStmt->get_result()->fetch_assoc();
            $senderName = $senderRow ? $senderRow['full_name'] : 'Someone';
            $notifMsg = ($message_type === 'text') ? $message_text : '📎 Sent a file';
            sendFCMNotification($receiver_id, $senderName, $notifMsg, 'chat', $message_id);
            $senderStmt->close();

            echo json_encode([
                'success' => true,
                'message' => 'Message sent',
                'data' => $message
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Mark messages as read
// ========================================
function markMessagesRead() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $sender_id = isset($data['sender_id']) ? intval($data['sender_id']) : 0;
    
    if ($user_id <= 0 || $sender_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    try {
        // Mark all messages from sender to user as read
        $sql = "UPDATE chat_messages 
                SET is_read = 1 
                WHERE receiver_id = ? AND sender_id = ? AND is_read = 0";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $sender_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Messages marked as read',
                'updated_count' => $stmt->affected_rows
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Update typing status
// ========================================
function updateTypingStatus() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $recipient_id = isset($data['recipient_id']) ? intval($data['recipient_id']) : 0;
    $is_typing = isset($data['is_typing']) ? intval($data['is_typing']) : 0;
    
    if ($user_id <= 0 || $recipient_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    try {
        // Insert or update typing status
        $sql = "INSERT INTO typing_status (user_id, recipient_id, is_typing, updated_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE is_typing = ?, updated_at = NOW()";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $user_id, $recipient_id, $is_typing, $is_typing);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Typing status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update typing status']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Get total unread chat count
// ========================================
function getTotalUnread() {
    global $conn;
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total_unread FROM chat_messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'total_unread' => intval($result['total_unread'])
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

?>
