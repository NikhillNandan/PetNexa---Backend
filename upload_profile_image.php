<?php
/**
 * upload_profile_image.php - Upload/update user profile image
 * Accepts base64 image + user_id + role, saves to uploads/profiles/, updates DB
 * Deploy to: htdocs/petnexa_API/upload_profile_image.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Accept both JSON body and POST form data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $role = isset($data['role']) ? strtolower(trim($data['role'])) : '';
    $profile_image_base64 = isset($data['profile_image']) ? $data['profile_image'] : '';

    // Validate
    if ($user_id <= 0) {
        $response['error'] = true;
        $response['message'] = 'Invalid user_id';
        echo json_encode($response);
        exit;
    }

    if (empty($profile_image_base64)) {
        $response['error'] = true;
        $response['message'] = 'No image data provided';
        echo json_encode($response);
        exit;
    }

    // Map role to folder name
    $role_folders = array(
        'buyer' => 'buyers',
        'seller' => 'sellers',
        'doctor' => 'doctors',
        'spa_owner' => 'spa_owners'
    );

    $folder = isset($role_folders[$role]) ? $role_folders[$role] : 'buyers';
    $upload_dir = __DIR__ . '/uploads/profiles/' . $folder . '/';

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Strip data URI prefix if present (e.g., "data:image/jpeg;base64,")
    $base64_clean = $profile_image_base64;
    if (strpos($base64_clean, ',') !== false) {
        $base64_clean = explode(',', $base64_clean)[1];
    }

    // Decode base64
    $image_data = base64_decode($base64_clean);
    if ($image_data === false) {
        $response['error'] = true;
        $response['message'] = 'Invalid base64 image data';
        echo json_encode($response);
        exit;
    }

    // Save file
    $filename = 'user_' . $user_id . '_' . time() . '.jpg';
    $filepath = $upload_dir . $filename;

    if (file_put_contents($filepath, $image_data) === false) {
        $response['error'] = true;
        $response['message'] = 'Failed to save image file';
        echo json_encode($response);
        exit;
    }

    // Relative URL path for DB storage
    $image_url = 'uploads/profiles/' . $folder . '/' . $filename;

    // Update DB
    try {
        // Delete old profile image file if exists
        $old_stmt = $conn->prepare("SELECT profile_image FROM users WHERE user_id = ?");
        $old_stmt->bind_param("i", $user_id);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result()->fetch_assoc();
        $old_stmt->close();

        if ($old_result && !empty($old_result['profile_image'])) {
            $old_file = __DIR__ . '/' . $old_result['profile_image'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        // Update profile_image in users table
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
        $stmt->bind_param("si", $image_url, $user_id);
        $stmt->execute();
        $stmt->close();

        $response['error'] = false;
        $response['message'] = 'Profile image uploaded successfully';
        $response['image_url'] = $image_url;

    } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
