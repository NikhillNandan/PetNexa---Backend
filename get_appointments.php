<?php
// get_appointments.php - Fetch all appointments (doctor + spa) for a buyer
require_once 'db.php';

header('Content-Type: application/json');

$buyer_id = isset($_GET['buyer_id']) ? (int)$_GET['buyer_id'] : 0;

if ($buyer_id <= 0) {
    echo json_encode(array("success" => false, "message" => "Valid buyer_id is required"));
    exit;
}

$appointments = array();

// 1. Doctor Appointments
$doctorQuery = "SELECT da.appointment_id, da.doctor_id, da.appointment_date, da.booking_time,
                       da.consultation_status, da.service_name as booked_services,
                       da.visit_type, da.treatment_notes, da.treatment_charge,
                       u.full_name as provider_name, u.phone as provider_phone, u.profile_image as provider_image,
                       dp.specialization, dp.hospital,
                       p.pet_name,
                       (SELECT COUNT(*) FROM reviews r WHERE r.target_user_id = da.doctor_id AND r.reviewer_id = da.buyer_id) as review_count
                FROM doctor_appointments da
                INNER JOIN users u ON da.doctor_id = u.user_id
                LEFT JOIN doctor_profiles dp ON da.doctor_id = dp.user_id
                LEFT JOIN pets p ON da.pet_id = p.pet_id
                WHERE da.buyer_id = ?
                ORDER BY da.appointment_date DESC";

$stmt = $conn->prepare($doctorQuery);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $apptDate = strtotime($row['appointment_date']);

    // Use booking_time if available, otherwise extract from appointment_date
    $displayTime = !empty($row['booking_time']) ? $row['booking_time'] : date("h:i A", $apptDate);

    // Use booked_services if available, otherwise fall back to specialization
    $serviceName = !empty($row['booked_services']) ? $row['booked_services'] : ($row['specialization'] ?? "General Consultation");

    // Location based on visit_type
    $visitType = $row['visit_type'] ?? 'clinic';
    $location = ($visitType === 'home') ? 'Home Visit' : ($row['hospital'] ?? 'Clinic Visit');

    $appointments[] = array(
        "id" => (int)$row['appointment_id'],
        "provider_id" => (int)$row['doctor_id'],
        "type" => "Doctor Consultation",
        "provider_name" => $row['provider_name'],
        "provider_phone" => $row['provider_phone'],
        "provider_image" => $row['provider_image'],
        "service_name" => $serviceName,
        "date" => date("M d, Y", $apptDate),
        "time" => $displayTime,
        "location" => $location,
        "visit_type" => $visitType,
        "fee" => number_format((float)($row['treatment_charge'] ?? 0), 0),
        "status" => $row['consultation_status'],
        "pet_name" => $row['pet_name'],
        "raw_date" => $row['appointment_date'],
        "has_reviewed" => intval($row['review_count']) > 0
    );
}
$stmt->close();

// 2. Spa Bookings
$spaQuery = "SELECT sb.booking_id, sb.spa_id, sb.booking_date, sb.booking_time, sb.booking_status, 
                    sb.payment_status, sb.total_amount,
                    sp.spa_name as provider_name, u.phone as provider_phone, u.profile_image as provider_image,
                    sp.user_id as spa_user_id,
                    ss.service_name, ss.price,
                    p.pet_name,
                    (SELECT COUNT(*) FROM spa_reviews sr2 WHERE sr2.spa_id = sb.spa_id AND sr2.user_id = sb.buyer_id) as review_count
             FROM spa_bookings sb
             INNER JOIN spa_services ss ON sb.service_id = ss.service_id
             INNER JOIN spa_profiles sp ON sb.spa_id = sp.spa_id
             LEFT JOIN users u ON sp.user_id = u.user_id
             LEFT JOIN pets p ON sb.pet_id = p.pet_id
             WHERE sb.buyer_id = ?
             ORDER BY sb.booking_date DESC";

$stmt = $conn->prepare($spaQuery);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Handle zero/null dates gracefully
    $raw_date = $row['booking_date'];
    $is_valid_date = ($raw_date && $raw_date !== '0000-00-00 00:00:00' && $raw_date !== '0000-00-00');
    
    if ($is_valid_date) {
        $bookDate = strtotime($raw_date);
        $formattedDate = date("M d, Y", $bookDate);
        $displayTime = date("h:i A", $bookDate);
    } else {
        $bookDate = 0;
        $formattedDate = 'Not scheduled';
        $displayTime = 'N/A';
    }

    $appointments[] = array(
        "id" => (int)$row['booking_id'],
        "provider_id" => (int)($row['spa_user_id'] ?? 0),
        "spa_id" => (int)($row['spa_id'] ?? 0),
        "type" => "Spa Service",
        "provider_name" => $row['provider_name'] ?? "Spa",
        "provider_phone" => $row['provider_phone'] ?? "",
        "provider_image" => $row['provider_image'],
        "service_name" => $row['service_name'],
        "date" => $formattedDate,
        "time" => $displayTime,
        "location" => "Spa Visit",
        "visit_type" => "spa",
        "fee" => number_format((float)($row['total_amount'] ?? $row['price'] ?? 0), 0),
        "status" => $row['booking_status'],
        "pet_name" => $row['pet_name'],
        "raw_date" => $row['booking_date'],
        "has_reviewed" => intval($row['review_count']) > 0
    );
}
$stmt->close();

// Sort all by date descending
usort($appointments, function($a, $b) {
    return strtotime($b['raw_date']) - strtotime($a['raw_date']);
});

// Split into upcoming and completed (case-insensitive)
$upcoming = array_values(array_filter($appointments, function($a) {
    $s = strtoupper($a['status'] ?? '');
    return in_array($s, ['BOOKED', 'PENDING', 'CONFIRMED', 'ACCEPTED', 'APPROVED']);
}));
$completed = array_values(array_filter($appointments, function($a) {
    $s = strtoupper($a['status'] ?? '');
    return in_array($s, ['COMPLETED', 'DONE', 'CANCELLED', 'DECLINED', 'REJECTED']);
}));

echo json_encode(array(
    "success" => true,
    "upcoming" => $upcoming,
    "completed" => $completed,
    "upcoming_count" => count($upcoming),
    "completed_count" => count($completed)
));

$conn->close();
?>
