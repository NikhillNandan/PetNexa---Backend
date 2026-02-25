<?php
/**
 * spa_owner_login.php - Login endpoint for spa owners
 * Deploy to: htdocs/petnexa_API/spa_owner_login.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($email) || empty($password)) {
        $response['error'] = true;
        $response['message'] = 'Email and password are required';
        echo json_encode($response);
        exit;
    }
    
    // Get user with SPA_OWNER role
    $stmt = $conn->prepare("SELECT u.user_id, u.full_name, u.email, u.phone, u.password_hash, u.address,
                                   sp.spa_id, sp.spa_name, sp.services_offered
                            FROM users u
                            INNER JOIN spa_profiles sp ON u.user_id = sp.user_id
                            WHERE u.email = ? AND u.role = 'SPA_OWNER'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $response['error'] = true;
        $response['message'] = 'Invalid email or password';
        echo json_encode($response);
        $stmt->close();
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password (plain text comparison)
    if ($password !== $user['password_hash']) {
        $response['error'] = true;
        $response['message'] = 'Invalid email or password';
        echo json_encode($response);
        exit;
    }
    
    // Login successful
    $response['error'] = false;
    $response['message'] = 'Login successful';
    $response['user'] = array(
        'user_id' => intval($user['user_id']),
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'address' => $user['address'],
        'spa_id' => intval($user['spa_id']),
        'spa_name' => $user['spa_name'],
        'services_offered' => $user['services_offered']
    );
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
