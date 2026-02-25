<?php
/**
 * CHAT MEDIA UPLOAD API
 * Handles photo and file uploads for chat
 * Saves file, inserts message into chat_messages, returns full message data
 */

// Suppress PHP warnings/notices from polluting JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$sender_id = isset($data['sender_id']) ? intval($data['sender_id']) : 0;
$receiver_id = isset($data['receiver_id']) ? intval($data['receiver_id']) : 0;
$message_type = isset($data['message_type']) ? $data['message_type'] : 'image';
$file_name = isset($data['file_name']) ? $data['file_name'] : '';
$mime_type = isset($data['mime_type']) ? $data['mime_type'] : 'application/octet-stream';
$file_data = isset($data['file_data']) ? $data['file_data'] : '';
$message_text = isset($data['message_text']) ? $data['message_text'] : '';

if ($sender_id <= 0 || $receiver_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user IDs']);
    exit;
}

if (empty($file_data)) {
    echo json_encode(['success' => false, 'message' => 'No file data provided']);
    exit;
}

try {
    // Create upload directory if not exists
    $upload_dir = "uploads/chat/" . $sender_id . "/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Decode base64 data
    $decoded_data = base64_decode($file_data);
    if ($decoded_data === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid base64 data']);
        exit;
    }

    // Generate unique filename
    if ($message_type === 'image') {
        $ext = 'jpg';
        if (strpos($mime_type, 'png') !== false) $ext = 'png';
        elseif (strpos($mime_type, 'gif') !== false) $ext = 'gif';
        elseif (strpos($mime_type, 'webp') !== false) $ext = 'webp';
        $saved_file_name = 'img_' . time() . '_' . uniqid() . '.' . $ext;
    } else {
        // Keep original filename but add uniqueness
        $pathinfo = pathinfo($file_name);
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '_', $pathinfo['filename']);
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : 'bin';
        $saved_file_name = $base . '_' . time() . '.' . $ext;
    }

    $file_path = $upload_dir . $saved_file_name;

    // Save file
    if (!file_put_contents($file_path, $decoded_data)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit;
    }

    // Compress image if it's a JPEG and GD is available
    if ($message_type === 'image' && ($ext === 'jpg' || $ext === 'jpeg') && function_exists('imagecreatefromstring')) {
        $image = @imagecreatefromstring($decoded_data);
        if ($image !== false) {
            @imagejpeg($image, $file_path, 75);
            @imagedestroy($image);
        }
    }

    // Insert message into chat_messages table
    $media_url = $file_path;
    
    $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message_text, message_type, media_url, file_name) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $sender_id, $receiver_id, $message_text, $message_type, $media_url, $file_name);
    
    if ($stmt->execute()) {
        $message_id = $conn->insert_id;
        
        // Get the full message
        $sql2 = "SELECT * FROM chat_messages WHERE message_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $message_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $message = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Media sent',
            'data' => $message
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save message: ' . $stmt->error]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
