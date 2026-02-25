<?php
// get_spas.php - Fetch all spa owners with profiles and services
require_once 'db.php';

header('Content-Type: application/json');

$user_lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$user_lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$radius_km = 20;

$distance_select = '';
$having_clause = '';
$order_clause = 'ORDER BY avg_rating DESC';

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

$query = "SELECT u.user_id, u.full_name, u.email, u.phone, u.profile_image, u.address, u.city, u.is_verified,
                 u.latitude, u.longitude,
                 sp.spa_name, sp.services_offered, sp.upi_id,
                 COALESCE(AVG(r.rating), 0) as avg_rating,
                 COUNT(r.review_id) as review_count
                 $distance_select
          FROM users u
          INNER JOIN spa_profiles sp ON u.user_id = sp.user_id
          LEFT JOIN reviews r ON u.user_id = r.target_user_id
          WHERE u.role = 'SPA_OWNER'
          GROUP BY u.user_id
          $having_clause
          $order_clause";

$result = $conn->query($query);

if ($result) {
    $spas = array();
    while ($row = $result->fetch_assoc()) {
        $row['avg_rating'] = round((float)$row['avg_rating'], 1);
        $row['review_count'] = (int)$row['review_count'];
        $row['is_verified'] = (bool)$row['is_verified'];
        $row['distance_km'] = isset($row['distance_km']) ? round((float)$row['distance_km'], 1) : null;

        // Fetch services for this spa - get spa_id first
        $spaUserId = $row['user_id'];
        $spaIdResult = $conn->query("SELECT spa_id FROM spa_profiles WHERE user_id = $spaUserId");
        $services = array();
        if ($spaIdResult && $spaIdRow = $spaIdResult->fetch_assoc()) {
            $spaId = $spaIdRow['spa_id'];
            $serviceQuery = "SELECT service_id, service_name, description, price FROM spa_services WHERE spa_id = $spaId";
            $serviceResult = $conn->query($serviceQuery);
            if ($serviceResult) {
                while ($svc = $serviceResult->fetch_assoc()) {
                    $svc['price'] = (float)$svc['price'];
                    $services[] = $svc;
                }
            }
        }
        $row['services'] = $services;

        $spas[] = $row;
    }
    echo json_encode(array("status" => "success", "spas" => $spas));
} else {
    echo json_encode(array("status" => "error", "message" => "Failed to fetch spas: " . $conn->error));
}

$conn->close();
?>
