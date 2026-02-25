<?php
// get_doctors.php - Fetch all doctors with their profiles
require_once 'db.php';

header('Content-Type: application/json');

$query = "SELECT u.user_id, u.full_name, u.email, u.phone, u.profile_image, u.address, u.city, u.is_verified,
                 dp.qualification, dp.specialization, dp.experience, dp.hospital, dp.languages, dp.upi_id,
                 COALESCE(AVG(r.rating), 0) as avg_rating,
                 COUNT(r.review_id) as review_count
          FROM users u
          INNER JOIN doctor_profiles dp ON u.user_id = dp.user_id
          LEFT JOIN reviews r ON u.user_id = r.target_user_id
          WHERE u.role = 'DOCTOR'
          GROUP BY u.user_id
          ORDER BY avg_rating DESC";

$result = $conn->query($query);

if ($result) {
    $doctors = array();
    while ($row = $result->fetch_assoc()) {
        $row['avg_rating'] = round((float)$row['avg_rating'], 1);
        $row['review_count'] = (int)$row['review_count'];
        $row['experience'] = (int)$row['experience'];
        $row['is_verified'] = (bool)$row['is_verified'];
        $doctors[] = $row;
    }
    echo json_encode(array("status" => "success", "doctors" => $doctors));
} else {
    echo json_encode(array("status" => "error", "message" => "Failed to fetch doctors: " . $conn->error));
}

$conn->close();
?>
