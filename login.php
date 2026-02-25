<?php
/**
 * login.php - Unified login for all roles
 * Deploy this file to: htdocs/petnexa_API/login.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate inputs
    if (empty($email) || empty($password)) {
        $response['error'] = true;
        $response['message'] = 'Email and password are required';
        echo json_encode($response);
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id, full_name, email, phone, password_hash, role, profile_image, address, city, state, pincode, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password (plain text comparison)
        if ($password === $user['password_hash']) {
            $response['error'] = false;
            $response['message'] = 'Login successful';
            $response['user'] = array(
                'user_id' => (int)$user['user_id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role'],
                'profile_image' => $user['profile_image'],
                'address' => $user['address'],
                'city' => $user['city'],
                'state' => $user['state'],
                'pincode' => $user['pincode'],
                'is_verified' => (int)$user['is_verified']
            );
        } else {
            $response['error'] = true;
            $response['message'] = 'Invalid password';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'No account found with this email';
    }

    $stmt->close();

} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
