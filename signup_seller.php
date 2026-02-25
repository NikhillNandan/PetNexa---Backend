<?php
/**
 * signup_seller.php - Seller registration
 * Deploy this file to: htdocs/petnexa_API/signup_seller.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Support both JSON body and form-encoded POST data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $seller_name = isset($data['full_name']) ? trim($data['full_name']) : (isset($data['seller_name']) ? trim($data['seller_name']) : '');
    $email       = isset($data['email']) ? trim($data['email']) : '';
    $phone       = isset($data['phone']) ? trim($data['phone']) : '';
    $password    = isset($data['password']) ? trim($data['password']) : '';
    $seller_type = isset($data['seller_type']) ? trim($data['seller_type']) : 'INDIVIDUAL';
    $shop_name   = isset($data['shop_name']) ? trim($data['shop_name']) : '';
    $upi_id      = isset($data['upi_id']) ? trim($data['upi_id']) : '';
    $address     = isset($data['address']) ? trim($data['address']) : '';
    $latitude    = isset($data['latitude']) ? floatval($data['latitude']) : 0.0;
    $longitude   = isset($data['longitude']) ? floatval($data['longitude']) : 0.0;

    // Validate required fields
    if (empty($seller_name) || empty($email) || empty($phone) || empty($password)) {
        $response['error'] = true;
        $response['success'] = false;
        $response['message'] = 'Name, email, phone, and password are required';
        echo json_encode($response);
        exit;
    }

    // Validate seller_type
    if (!in_array($seller_type, ['INDIVIDUAL', 'SHOP'])) {
        $seller_type = 'INDIVIDUAL';
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $response['error'] = true;
        $response['success'] = false;
        $response['message'] = 'Email already registered';
        echo json_encode($response);
        $check->close();
        exit;
    }
    $check->close();

    // Store password as plain text
    $password_hash = $password;
    $role = 'SELLER';

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash, role, address, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssdd", $seller_name, $email, $phone, $password_hash, $role, $address, $latitude, $longitude);
        $stmt->execute();
        $user_id = $conn->insert_id;
        $stmt->close();

        // Insert into seller_profiles table
        $stmt2 = $conn->prepare("INSERT INTO seller_profiles (user_id, shop_name, seller_type, upi_id) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $user_id, $shop_name, $seller_type, $upi_id);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();

        $response['error'] = false;
        $response['success'] = true;
        $response['message'] = 'Seller registered successfully';
        $response['user_id'] = $user_id;

    } catch (Exception $e) {
        $conn->rollback();
        $response['error'] = true;
        $response['success'] = false;
        $response['message'] = 'Registration failed: ' . $e->getMessage();
    }

} else {
    $response['error'] = true;
    $response['success'] = false;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
