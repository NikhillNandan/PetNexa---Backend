<?php
/**
 * get_spa_profile.php - Get spa profile details
 * Deploy to: htdocs/petnexa_API/get_spa_profile.php
 */

header('Content-Type: application/json');
require_once 'db.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (isset($_POST['user_id']) ? intval($_POST['user_id']) : 0);
    
    if ($user_id <= 0) {
        $response['error'] = true;
        $response['message'] = 'User ID is required';
        echo json_encode($response);
        exit;
    }
    
    // Get spa profile with user details
    $query = "SELECT sp.spa_id, sp.spa_name, sp.services_offered, sp.upi_id, sp.rating, sp.total_reviews,
                     u.full_name as owner_name, u.email, u.phone, u.address, u.profile_image
              FROM spa_profiles sp
              INNER JOIN users u ON sp.user_id = u.user_id
              WHERE sp.user_id = ?";
    
    $profile_query = $conn->prepare($query);
    $profile_query->bind_param("i", $user_id);
    $profile_query->execute();
    $profile_result = $profile_query->get_result();
    
    if ($profile_result->num_rows == 0) {
        // Auto-create spa profile from users table if missing
        $user_query = $conn->prepare("SELECT full_name, email, phone, address, profile_image FROM users WHERE user_id = ? AND role = 'SPA_OWNER'");
        $user_query->bind_param("i", $user_id);
        $user_query->execute();
        $user_result = $user_query->get_result();
        if ($user_result->num_rows == 0) {
            $response['error'] = true;
            $response['message'] = 'Spa profile not found';
            echo json_encode($response);
            $user_query->close();
            $profile_query->close();
            exit;
        }
        $user_data = $user_result->fetch_assoc();
        $user_query->close();
        // Insert missing spa_profiles row
        $insert = $conn->prepare("INSERT INTO spa_profiles (user_id, spa_name) VALUES (?, ?)");
        $spa_name = $user_data['full_name'] . "'s Spa";
        $insert->bind_param("is", $user_id, $spa_name);
        $insert->execute();
        $insert->close();
        // Re-query to get full profile
        $profile_query2 = $conn->prepare($query);
        $profile_query2->bind_param("i", $user_id);
        $profile_query2->execute();
        $profile_result = $profile_query2->get_result();
        $profile_query->close();
        $profile_query = $profile_query2;
    }
    
    $profile = $profile_result->fetch_assoc();
    $profile_query->close();
    
    // Get total bookings count
    $total_bookings = 0;
    $spa_id = intval($profile['spa_id']);
    $bookings_stmt = $conn->prepare("SELECT COUNT(*) as count FROM spa_bookings WHERE spa_id = ?");
    $bookings_stmt->bind_param("i", $spa_id);
    $bookings_stmt->execute();
    $bookings_result = $bookings_stmt->get_result();
    $total_bookings = $bookings_result->fetch_assoc()['count'];
    $bookings_stmt->close();
    
    // Get total services count
    $total_services = 0;
    $services_stmt = $conn->prepare("SELECT COUNT(*) as count FROM spa_services WHERE spa_id = ?");
    $services_stmt->bind_param("i", $spa_id);
    $services_stmt->execute();
    $services_result = $services_stmt->get_result();
    $total_services = $services_result->fetch_assoc()['count'];
    $services_stmt->close();
    
    $response['error'] = false;
    $response['message'] = 'Profile retrieved successfully';
    $response['profile'] = array(
        'spa_id' => intval($profile['spa_id']),
        'spa_name' => $profile['spa_name'],
        'services_offered' => $profile['services_offered'],
        'upi_id' => $profile['upi_id'],
        'rating' => floatval($profile['rating']),
        'total_reviews' => intval($profile['total_reviews']),
        'total_bookings' => intval($total_bookings),
        'total_services' => intval($total_services),
        'owner_name' => $profile['owner_name'],
        'email' => $profile['email'],
        'phone' => $profile['phone'],
        'address' => $profile['address'],
        'profile_image' => $profile['profile_image'] ?? ''
    );
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();
?>
