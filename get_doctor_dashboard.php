<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'db.php';

// Get doctor_id from request
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

if ($doctor_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid doctor ID'
    ]);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get today's appointments count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM doctor_appointments 
        WHERE doctor_id = ? AND DATE(appointment_date) = CURDATE()
    ");
    $stmt->execute([$doctor_id]);
    $appointments_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total earnings
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(treatment_charge), 0) as total 
        FROM doctor_appointments 
        WHERE doctor_id = ? AND consultation_status = 'COMPLETED'
    ");
    $stmt->execute([$doctor_id]);
    $total_earnings = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get total unique patients
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT buyer_id) as count 
        FROM doctor_appointments 
        WHERE doctor_id = ?
    ");
    $stmt->execute([$doctor_id]);
    $total_patients = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get average rating from reviews
    $avg_rating = 0.0;
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE target_user_id = ?");
    $stmt->execute([$doctor_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $avg_rating = $row['avg_rating'] ? round(floatval($row['avg_rating']), 1) : 0.0;

    // Get experience years from doctor_profiles table
    $experience_years = 0;
    $stmt = $conn->prepare("SELECT experience FROM doctor_profiles WHERE user_id = ?");
    $stmt->execute([$doctor_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $experience_years = intval($row['experience']);
    }
    
    echo json_encode([
        'success' => true,
        'appointments_today' => intval($appointments_today),
        'total_earnings' => floatval($total_earnings),
        'total_patients' => intval($total_patients),
        'avg_rating' => $avg_rating,
        'experience_years' => $experience_years
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
