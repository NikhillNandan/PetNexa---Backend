<?php
/**
 * CONSOLIDATED DOCTOR API  
 * Handles all doctor-related operations
 * 
 * Endpoints:
 * - get_list: Get list of all doctors
 * - get_dashboard: Get doctor dashboard stats
 */

header('Content-Type: application/json');
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_list':
        getDoctorList();
        break;
    
    case 'get_dashboard':
        getDoctorDashboard();
        break;
    
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

// ========================================
// FUNCTION: Get doctor list
// ========================================
function getDoctorList() {
    global $conn;
    
    $user_lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $user_lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
    $radius_km = 20;
    
    $distance_select = '';
    $having_clause = '';
    $order_clause = 'ORDER BY avg_rating DESC, u.full_name ASC';
    
    if ($user_lat !== null && $user_lng !== null && $user_lat != 0 && $user_lng != 0) {
        $lat = $conn->real_escape_string($user_lat);
        $lng = $conn->real_escape_string($user_lng);
        $distance_select = ",
            (6371 * acos(
                cos(radians($lat)) * cos(radians(u.latitude)) *
                cos(radians(u.longitude) - radians($lng)) +
                sin(radians($lat)) * sin(radians(u.latitude))
            )) AS distance_km";
        $having_clause = "HAVING distance_km <= $radius_km";
        $order_clause = 'ORDER BY distance_km ASC';
    }
    
    try {
        $sql = "SELECT u.user_id, u.full_name, u.profile_image, u.phone,
                       u.address, u.city, u.state, u.is_verified,
                       u.latitude, u.longitude,
                       dp.specialization, dp.qualification, dp.experience, 
                       dp.hospital, dp.languages,
                       COALESCE(AVG(r.rating), 0) as avg_rating,
                       COUNT(r.review_id) as review_count
                       $distance_select
                FROM users u
                LEFT JOIN doctor_profiles dp ON u.user_id = dp.user_id
                LEFT JOIN reviews r ON u.user_id = r.target_user_id
                WHERE u.role = 'DOCTOR'
                GROUP BY u.user_id
                $having_clause
                $order_clause";
        
        $result = $conn->query($sql);
        $doctors = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get the cheapest service price as consultation_fee
            $feeStmt = $conn->prepare("SELECT MIN(price) as min_price FROM doctor_services WHERE doctor_id = ?");
            $feeStmt->bind_param("i", $row['user_id']);
            $feeStmt->execute();
            $feeResult = $feeStmt->get_result()->fetch_assoc();
            $consultation_fee = $feeResult['min_price'] ? (int)$feeResult['min_price'] : 500;
            $feeStmt->close();

            $doctors[] = [
                'user_id' => (int)$row['user_id'],
                'full_name' => $row['full_name'],
                'profile_image' => $row['profile_image'],
                'phone' => $row['phone'],
                'address' => $row['address'],
                'city' => $row['city'] ?? '',
                'state' => $row['state'] ?? '',
                'is_verified' => (bool)$row['is_verified'],
                'specialization' => $row['specialization'] ?? 'General Vet',
                'qualification' => $row['qualification'] ?? '',
                'experience' => (int)($row['experience'] ?? 0),
                'hospital' => $row['hospital'] ?? '',
                'languages' => $row['languages'] ?? '',
                'avg_rating' => round((float)$row['avg_rating'], 1),
                'review_count' => (int)$row['review_count'],
                'consultation_fee' => $consultation_fee,
                'distance_km' => isset($row['distance_km']) ? round((float)$row['distance_km'], 1) : null,
                'about' => 'Experienced veterinarian specializing in ' . ($row['specialization'] ?? 'general pet care') . '. ' . (int)($row['experience'] ?? 0) . ' years of practice at ' . ($row['hospital'] ?? 'clinic') . '.'
            ];
        }
        
        echo json_encode(['status' => 'success', 'doctors' => $doctors]);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// ========================================
// FUNCTION: Get doctor dashboard stats
// ========================================
function getDoctorDashboard() {
    global $conn;
    
    $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
    
    if ($doctor_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid doctor ID']);
        return;
    }
    
    try {
        // Total appointments
        $sql = "SELECT COUNT(*) as total_appointments FROM doctor_appointments WHERE doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $total_appointments = $stmt->get_result()->fetch_assoc()['total_appointments'];
        
        // Completed appointments
        $sql = "SELECT COUNT(*) as completed_count FROM doctor_appointments 
                WHERE doctor_id = ? AND consultation_status = 'COMPLETED'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $completed_appointments = $stmt->get_result()->fetch_assoc()['completed_count'];
        
        // Pending appointments
        $sql = "SELECT COUNT(*) as pending_count FROM doctor_appointments 
                WHERE doctor_id = ? AND consultation_status = 'BOOKED'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $pending_appointments = $stmt->get_result()->fetch_assoc()['pending_count'];
        
        // Total earnings
        $sql = "SELECT COALESCE(SUM(treatment_charge), 0) as total_earnings 
                FROM doctor_appointments WHERE doctor_id = ? AND consultation_status = 'COMPLETED'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $total_earnings = $stmt->get_result()->fetch_assoc()['total_earnings'];
        
        echo json_encode([
            'status' => 'success',
            'dashboard' => [
                'total_appointments' => (int)$total_appointments,
                'completed_appointments' => (int)$completed_appointments,
                'pending_appointments' => (int)$pending_appointments,
                'total_earnings' => floatval($total_earnings)
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

?>
