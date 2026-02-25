<?php
// book_doctor_appointment.php - Book a doctor appointment
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array("status" => "error", "message" => "POST method required"));
    exit;
}

$buyer_id = isset($_POST['buyer_id']) ? (int)$_POST['buyer_id'] : 0;
$doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
$pet_id = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : null;
$service_name = isset($_POST['service_name']) ? $_POST['service_name'] : '';
$appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
$booking_time = isset($_POST['booking_time']) ? $_POST['booking_time'] : '';
$visit_type = isset($_POST['visit_type']) ? $_POST['visit_type'] : 'clinic';
$total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;

// Validate required fields
if ($buyer_id <= 0 || $doctor_id <= 0 || empty($appointment_date) || empty($booking_time)) {
    echo json_encode(array("status" => "error", "message" => "buyer_id, doctor_id, appointment_date, and booking_time are required"));
    exit;
}

// Check doctor exists
$checkDoc = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND role = 'DOCTOR'");
$checkDoc->bind_param("i", $doctor_id);
$checkDoc->execute();
if ($checkDoc->get_result()->num_rows == 0) {
    echo json_encode(array("status" => "error", "message" => "Doctor not found"));
    exit;
}
$checkDoc->close();

// Combine date and time into a single datetime for appointment_date column
// appointment_date from Android: "15 Feb 2026", booking_time: "09:00 AM"
$dateTimeCombined = $appointment_date . ' ' . $booking_time;
$parsedDateTime = date_create_from_format('d M Y h:i A', $dateTimeCombined);
if (!$parsedDateTime) {
    // Try other common formats
    $parsedDateTime = date_create_from_format('d M Y H:i', $dateTimeCombined);
}
if (!$parsedDateTime) {
    // Fallback: try direct parsing
    $parsedDateTime = date_create($dateTimeCombined);
}

if ($parsedDateTime) {
    $formattedDateTime = $parsedDateTime->format('Y-m-d H:i:s');
} else {
    echo json_encode(array("status" => "error", "message" => "Invalid date/time format: " . $dateTimeCombined));
    exit;
}

// Check for conflicting appointment (same doctor, same date+time)
$checkConflict = $conn->prepare("SELECT appointment_id FROM doctor_appointments WHERE doctor_id = ? AND appointment_date = ? AND consultation_status = 'BOOKED'");
$checkConflict->bind_param("is", $doctor_id, $formattedDateTime);
$checkConflict->execute();
if ($checkConflict->get_result()->num_rows > 0) {
    echo json_encode(array("status" => "error", "message" => "This time slot is already booked"));
    exit;
}
$checkConflict->close();

// Insert appointment with all details
if ($pet_id && $pet_id > 0) {
    $stmt = $conn->prepare(
        "INSERT INTO doctor_appointments (pet_id, doctor_id, buyer_id, appointment_date, booking_time, service_name, visit_type, consultation_status, treatment_charge) VALUES (?, ?, ?, ?, ?, ?, ?, 'BOOKED', ?)"
    );
    $stmt->bind_param("iiissssd", $pet_id, $doctor_id, $buyer_id, $formattedDateTime, $booking_time, $service_name, $visit_type, $total_amount);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO doctor_appointments (doctor_id, buyer_id, appointment_date, booking_time, service_name, visit_type, consultation_status, treatment_charge) VALUES (?, ?, ?, ?, ?, ?, 'BOOKED', ?)"
    );
    $stmt->bind_param("iissssd", $doctor_id, $buyer_id, $formattedDateTime, $booking_time, $service_name, $visit_type, $total_amount);
}

if ($stmt->execute()) {
    $appointment_id = $stmt->insert_id;

    // Get doctor name for response
    $docQuery = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $docQuery->bind_param("i", $doctor_id);
    $docQuery->execute();
    $docResult = $docQuery->get_result()->fetch_assoc();

    // Send notification to doctor
    require_once 'send_fcm.php';
    sendFCMNotification($doctor_id, 'New Appointment Booked',
        'A patient has booked ' . $service_name . ' on ' . $booking_time . ', ' . $appointment_date,
        'appointment', $appointment_id);

    echo json_encode(array(
        "status" => "success",
        "message" => "Appointment booked successfully",
        "booking_id" => $appointment_id,
        "doctor_name" => $docResult['full_name'],
        "date" => $appointment_date,
        "time" => $booking_time,
        "visit_type" => $visit_type,
        "service_name" => $service_name,
        "total_amount" => $total_amount,
        "location" => ($visit_type === 'home') ? 'Home Visit' : 'Clinic Visit'
    ));
    $docQuery->close();
} else {
    echo json_encode(array("status" => "error", "message" => "Failed to book appointment: " . $stmt->error));
}

$stmt->close();
$conn->close();
?>
