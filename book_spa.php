<?php
// book_spa.php - Book a spa service
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array("status" => "error", "message" => "POST method required"));
    exit;
}

$buyer_id = isset($_POST['buyer_id']) ? (int)$_POST['buyer_id'] : 0;
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$pet_id = isset($_POST['pet_id']) && $_POST['pet_id'] !== '' ? (int)$_POST['pet_id'] : null;
$booking_date = isset($_POST['booking_date']) ? $_POST['booking_date'] : '';

// Validate required fields (pet_id is optional for spa bookings)
if ($buyer_id <= 0 || $service_id <= 0 || empty($booking_date)) {
    echo json_encode(array("status" => "error", "message" => "buyer_id, service_id, and booking_date are required"));
    exit;
}

// Check service exists and get details
$checkSvc = $conn->prepare("SELECT ss.service_id, ss.service_name, ss.price, ss.duration_minutes, ss.spa_id as spa_user_id, sp.spa_id, sp.spa_name 
                            FROM spa_services ss 
                            LEFT JOIN spa_profiles sp ON ss.spa_id = sp.user_id 
                            WHERE ss.service_id = ?");
$checkSvc->bind_param("i", $service_id);
$checkSvc->execute();
$svcResult = $checkSvc->get_result();

if ($svcResult->num_rows == 0) {
    echo json_encode(array("status" => "error", "message" => "Service not found"));
    exit;
}
$service = $svcResult->fetch_assoc();
$checkSvc->close();

// Use spa_profiles.spa_id (PK) for the booking FK
$spa_profile_id = (int)$service['spa_id'];
$total_amount = floatval($service['price']);

// Extract time from the booking_date datetime string (format: "2026-02-18 14:00:00")
$booking_time_val = null;
$timestamp = strtotime($booking_date);
if ($timestamp) {
    $booking_time_val = date('H:i:s', $timestamp);
}

// Insert booking - store in ALL relevant columns for compatibility
// DB has: booking_date (datetime), booking_time (time), status, booking_status, total_amount
$stmt = $conn->prepare("INSERT INTO spa_bookings (pet_id, service_id, spa_id, buyer_id, booking_date, booking_time, status, booking_status, payment_status, total_amount) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'BOOKED', 'SUCCESS', ?)");
$stmt->bind_param("iiiissd", $pet_id, $service_id, $spa_profile_id, $buyer_id, $booking_date, $booking_time_val, $total_amount);

if ($stmt->execute()) {
    $booking_id = $stmt->insert_id;
    $bookDate = strtotime($booking_date);

    // Send notification to spa owner
    require_once 'send_fcm.php';
    $spaOwnerId = (int)$service['spa_user_id'];
    sendFCMNotification($spaOwnerId, 'New Spa Booking!',
        'New booking for ' . $service['service_name'] . ' on ' . date("D d M, h:i A", $bookDate),
        'booking', $booking_id);

    echo json_encode(array(
        "status" => "success",
        "message" => "Spa booking confirmed",
        "booking_id" => $booking_id,
        "spa_name" => $service['spa_name'],
        "service_name" => $service['service_name'],
        "date" => date("D d M", $bookDate),
        "time" => date("h:i A", $bookDate),
        "location" => "Spa Visit",
        "amount" => "Rs." . number_format($total_amount, 0)
    ));
} else {
    echo json_encode(array("status" => "error", "message" => "Failed to book spa: " . $stmt->error));
}

$stmt->close();
$conn->close();
?>