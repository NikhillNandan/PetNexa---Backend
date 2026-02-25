<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

// Check if file was uploaded
if (!isset($_FILES['certificate_file'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No file uploaded'
    ]);
    exit;
}

$file = $_FILES['certificate_file'];
$pet_id = isset($_POST['pet_id']) ? intval($_POST['pet_id']) : 0;

if ($pet_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid pet ID'
    ]);
    exit;
}

// Validate file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid file type. Only JPG, PNG, and PDF allowed'
    ]);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode([
        'success' => false,
        'error' => 'File too large. Maximum size is 5MB'
    ]);
    exit;
}

try {
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/certificates/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'cert_' . $pet_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode([
            'success' => true,
            'file_path' => 'uploads/certificates/' . $filename,
            'filename' => $filename
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to upload file'
        ]);
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Upload error: ' . $e->getMessage()
    ]);
}
?>
