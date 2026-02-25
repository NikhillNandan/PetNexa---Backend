<?php
/**
 * CONSOLIDATED USER PROFILE API
 * Handles all user

 profile operations
 * 
 * Endpoints:
 * - get: Get profile data
 * - update: Update profile
 */

header('Content-Type: application/json');
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get':
        getProfile();
        break;
    
    case 'update':
        updateProfile();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// ========================================
// FUNCTION: Get profile
// ========================================
function getProfile() {
    global $conn;
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        // Get user data
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        // Get role-specific profile
        $profile = [];
        if ($role === 'SPA_OWNER') {
            $sql = "SELECT * FROM spa_profiles WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $profile = $stmt->get_result()->fetch_assoc();
        } elseif ($role === 'DOCTOR') {
            $sql = "SELECT * FROM doctor_profiles WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $profile = $stmt->get_result()->fetch_assoc();
        } elseif ($role === 'SELLER') {
            $sql = "SELECT * FROM seller_profiles WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $profile = $stmt->get_result()->fetch_assoc();
        }
        
        echo json_encode([
            'success' => true,
            'user' => $user,
            'profile' => $profile
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

//========================================
// FUNCTION: Update profile
// ========================================
function updateProfile() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        //Update users table
        $full_name = isset($data['full_name']) ? $data['full_name'] : '';
        $phone = isset($data['phone']) ? $data['phone'] : '';
        $address = isset($data['address']) ? $data['address'] : '';
        $latitude = isset($data['latitude']) ? floatval($data['latitude']) : 0.0;
        $longitude = isset($data['longitude']) ? floatval($data['longitude']) : 0.0;
        
        $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, latitude = ?, longitude = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssddi", $full_name, $phone, $address, $latitude, $longitude, $user_id);
        $stmt->execute();
        
        // Update role-specific profile
        if ($role === 'DOCTOR') {
            $qualification = isset($data['qualification']) ? $data['qualification'] : '';
            $specialization = isset($data['specialization']) ? $data['specialization'] : '';
            $experience = isset($data['experience']) ? intval($data['experience']) : 0;
            
            $sql = "UPDATE doctor_profiles SET qualification = ?, specialization = ?, experience = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $qualification, $specialization, $experience, $user_id);
            $stmt->execute();
        } elseif ($role === 'SPA_OWNER') {
            $spa_name = isset($data['spa_name']) ? $data['spa_name'] : '';
            $services_offered = isset($data['services_offered']) ? $data['services_offered'] : '';
            
            $sql = "UPDATE spa_profiles SET spa_name = ?, services_offered = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $spa_name, $services_offered, $user_id);
            $stmt->execute();
        } elseif ($role === 'SELLER') {
            $shop_name = isset($data['shop_name']) ? $data['shop_name'] : '';
            
            $sql = "UPDATE seller_profiles SET shop_name = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $shop_name, $user_id);
            $stmt->execute();
        }
        
        echo json_encode(['success' => true, 'message' => 'Profile updated']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

?>
