<?php
/**
 * CONSOLIDATED AUTHENTICATION API
 * Handles all user authentication operations
 * 
 * Endpoints:
 * - login: User login
 * - signup: User registration (all roles)
 */

header('Content-Type: application/json');
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        login();
        break;
    
    case 'signup':
        signup();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// ========================================
// FUNCTION: Login
// ========================================
function login() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = isset($data['email']) ? $data['email'] : '';
    $password = isset($data['password']) ? $data['password'] : '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password required']);
        return;
    }
    
    try {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Simple password check (in production, use password_hash/password_verify)
            if ($user['password_hash'] === $password) {
                // Get role-specific profile data
                $profile_data = getRoleProfile($user['user_id'], $user['role']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => array_merge($user, $profile_data)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Signup
// ========================================
function signup() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $role = isset($_GET['role']) ? strtoupper($_GET['role']) : 'BUYER';
    
    $full_name = isset($data['full_name']) ? $data['full_name'] : '';
    $email = isset($data['email']) ? $data['email'] : '';
    $phone = isset($data['phone']) ? $data['phone'] : '';
    $password = isset($data['password']) ? $data['password'] : '';
    $address = isset($data['address']) ? $data['address'] : '';
    $latitude = isset($data['latitude']) ? floatval($data['latitude']) : 0.0;
    $longitude = isset($data['longitude']) ? floatval($data['longitude']) : 0.0;
    
    if (empty($full_name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
        return;
    }
    
    try {
        
        // Check if email exists
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            return;
        }
        
        // Insert user with lat/lng
        $sql = "INSERT INTO users (full_name, email, phone, password_hash, role, address, latitude, longitude) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssdd", $full_name, $email, $phone, $password, $role, $address, $latitude, $longitude);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Create role-specific profile
            createRoleProfile($user_id, $role, $data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $user_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// HELPER: Get role-specific profile
// ========================================
function getRoleProfile($user_id, $role) {
    global $conn;
    $profile = [];
    
    switch ($role) {
        case 'BUYER':
            $sql = "SELECT * FROM buyer_profiles WHERE user_id = ?";
            break;
        case 'SELLER':
            $sql = "SELECT * FROM seller_profiles WHERE user_id = ?";
            break;
        case 'DOCTOR':
            $sql = "SELECT * FROM doctor_profiles WHERE user_id = ?";
            break;
        case 'SPA_OWNER':
            $sql = "SELECT * FROM spa_profiles WHERE user_id = ?";
            break;
        default:
            return $profile;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $profile = $result->fetch_assoc();
    }
    
    return $profile;
}

// ========================================
// HELPER: Create role-specific profile
// ========================================
function createRoleProfile($user_id, $role, $data) {
    global $conn;
    
    switch ($role) {
        case 'BUYER':
            $upi_id = isset($data['upi_id']) ? $data['upi_id'] : '';
            $sql = "INSERT INTO buyer_profiles (user_id, upi_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $upi_id);
            $stmt->execute();
            break;
            
        case 'SELLER':
            $shop_name = isset($data['shop_name']) ? $data['shop_name'] : '';
            $seller_type = isset($data['seller_type']) ? $data['seller_type'] : 'INDIVIDUAL';
            $upi_id = isset($data['upi_id']) ? $data['upi_id'] : '';
            $sql = "INSERT INTO seller_profiles (user_id, shop_name, seller_type, upi_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $user_id, $shop_name, $seller_type, $upi_id);
            $stmt->execute();
            break;
            
        case 'DOCTOR':
            $qualification = isset($data['qualification']) ? $data['qualification'] : '';
            $specialization = isset($data['specialization']) ? $data['specialization'] : '';
            $upi_id = isset($data['upi_id']) ? $data['upi_id'] : '';
            $sql = "INSERT INTO doctor_profiles (user_id, qualification, specialization, upi_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $user_id, $qualification, $specialization, $upi_id);
            $stmt->execute();
            break;
            
        case 'SPA_OWNER':
            $spa_name = isset($data['spa_name']) ? $data['spa_name'] : '';
            $upi_id = isset($data['upi_id']) ? $data['upi_id'] : '';
            $services_offered = isset($data['services_offered']) ? $data['services_offered'] : '';
            $sql = "INSERT INTO spa_profiles (user_id, spa_name, upi_id, services_offered) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $user_id, $spa_name, $upi_id, $services_offered);
            $stmt->execute();
            break;
    }
}

?>
