<?php
/**
 * update_spa_profile.php - Update spa profile details
 * Deploy to: htdocs/petnexa_API/update_spa_profile.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $spa_name = isset($_POST['spa_name']) ? trim($_POST['spa_name']) : '';
    $owner_name = isset($_POST['owner_name']) ? trim($_POST['owner_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $services_offered = isset($_POST['services_offered']) ? trim($_POST['services_offered']) : '';
    $upi_id = isset($_POST['upi_id']) ? trim($_POST['upi_id']) : '';
    
    if ($user_id <= 0 || empty($spa_name)) {
        $response['error'] = true;
        $response['message'] = 'Required fields are missing (user_id and spa_name required)';
        echo json_encode($response);
        exit;
    }
    
    $conn->begin_transaction();
    
    try {
        // Update users table only if fields are provided
        if (!empty($owner_name) || !empty($email) || !empty($phone) || !empty($address)) {
            $update_parts = array();
            $types = "";
            $params = array();
            
            if (!empty($owner_name)) {
                $update_parts[] = "full_name = ?";
                $types .= "s";
                $params[] = $owner_name;
            }
            if (!empty($email)) {
                $update_parts[] = "email = ?";
                $types .= "s";
                $params[] = $email;
            }
            if (!empty($phone)) {
                $update_parts[] = "phone = ?";
                $types .= "s";
                $params[] = $phone;
            }
            if (!empty($address)) {
                $update_parts[] = "address = ?";
                $types .= "s";
                $params[] = $address;
            }
            
            if (!empty($update_parts)) {
                $types .= "i";
                $params[] = $user_id;
                
                $sql = "UPDATE users SET " . implode(", ", $update_parts) . " WHERE user_id = ?";
                $user_stmt = $conn->prepare($sql);
                $user_stmt->bind_param($types, ...$params);
                $user_stmt->execute();
                $user_stmt->close();
            }
        }
        
        // Update spa_profiles table
        $spa_stmt = $conn->prepare("UPDATE spa_profiles SET spa_name = ?, services_offered = ?, upi_id = ? WHERE user_id = ?");
        $spa_stmt->bind_param("sssi", $spa_name, $services_offered, $upi_id, $user_id);
        $spa_stmt->execute();
        $spa_stmt->close();
        
        $conn->commit();
        
        $response['error'] = false;
        $response['message'] = 'Profile updated successfully';
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['error'] = true;
        $response['message'] = 'Failed to update profile: ' . $e->getMessage();
    }
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
$conn->close();
?>
