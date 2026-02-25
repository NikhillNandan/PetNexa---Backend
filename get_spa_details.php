<?php
/**
 * get_spa_details.php - Get complete spa details for buyer view
 * Combines profile, services, and reviews into one response.
 * Deploy to: htdocs/petnexa_API/get_spa_details.php
 *
 * DB Schema:
 *   spa_profiles: spa_id, user_id, spa_name, services_offered, upi_id, rating, total_reviews
 *   spa_services: service_id, spa_id, service_name, description, duration_minutes, duration, price, created_at
 *   spa_reviews:  review_id, spa_id, user_id, rating(int1), review_text, created_at
 *   users:        user_id, full_name, email, phone, profile_image, address, city, ...
 */

header('Content-Type: application/json');
error_reporting(0); // Prevent HTML error output breaking JSON
require_once 'db.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'user_id is required'));
    exit;
}

// 1) Get spa profile + user info
$profile_query = $conn->prepare(
    "SELECT sp.spa_id, sp.spa_name, sp.services_offered, sp.upi_id, sp.rating, sp.total_reviews,
            u.full_name AS owner_name, u.email, u.phone, u.address, u.city, u.profile_image
     FROM spa_profiles sp
     INNER JOIN users u ON sp.user_id = u.user_id
     WHERE sp.user_id = ?"
);
if (!$profile_query) {
    echo json_encode(array('success' => false, 'message' => 'Query error: ' . $conn->error));
    exit;
}
$profile_query->bind_param("i", $user_id);
$profile_query->execute();
$profile_result = $profile_query->get_result();

if ($profile_result->num_rows == 0) {
    echo json_encode(array('success' => false, 'message' => 'Spa not found'));
    $profile_query->close();
    $conn->close();
    exit;
}

$profile = $profile_result->fetch_assoc();
$spa_id = intval($profile['spa_id']);
$profile_query->close();

// 2) Get services from spa_services (keyed by spa_id)
$services = array();
$min_price = PHP_INT_MAX;
$svc_query = $conn->prepare(
    "SELECT service_id, service_name, description, duration_minutes, duration, price
     FROM spa_services WHERE spa_id = ? ORDER BY price ASC"
);
if ($svc_query) {
    $svc_query->bind_param("i", $spa_id);
    $svc_query->execute();
    $svc_result = $svc_query->get_result();
    while ($row = $svc_result->fetch_assoc()) {
        $row['price'] = floatval($row['price']);
        $row['duration_minutes'] = intval($row['duration_minutes']);
        if ($row['price'] < $min_price) $min_price = $row['price'];
        $services[] = $row;
    }
    $svc_query->close();
}

// 3) Get reviews from spa_reviews (keyed by spa_id, joined with users for reviewer name)
$reviews = array();
$avg_rating = 0;
$review_count = 0;

$rev_query = $conn->prepare(
    "SELECT sr.review_id, sr.rating, sr.review_text, sr.created_at,
            u.full_name AS reviewer_name
     FROM spa_reviews sr
     LEFT JOIN users u ON sr.user_id = u.user_id
     WHERE sr.spa_id = ?
     ORDER BY sr.created_at DESC
     LIMIT 10"
);
if ($rev_query) {
    $rev_query->bind_param("i", $spa_id);
    $rev_query->execute();
    $rev_result = $rev_query->get_result();
    while ($row = $rev_result->fetch_assoc()) {
        $row['rating'] = intval($row['rating']);
        $reviews[] = $row;
    }
    $rev_query->close();
}

// Get average rating and count from spa_reviews
$stats_query = $conn->prepare(
    "SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as review_count
     FROM spa_reviews WHERE spa_id = ?"
);
if ($stats_query) {
    $stats_query->bind_param("i", $spa_id);
    $stats_query->execute();
    $stats_result = $stats_query->get_result();
    $stats = $stats_result->fetch_assoc();
    $avg_rating = round(floatval($stats['avg_rating']), 1);
    $review_count = intval($stats['review_count']);
    $stats_query->close();
}

// If no spa_reviews, fall back to general reviews table
if ($review_count == 0) {
    $gen_query = $conn->prepare(
        "SELECT r.review_id, r.rating, r.comment AS review_text, r.created_at,
                u.full_name AS reviewer_name
         FROM reviews r
         LEFT JOIN users u ON r.reviewer_id = u.user_id
         WHERE r.target_user_id = ?
         ORDER BY r.created_at DESC
         LIMIT 10"
    );
    if ($gen_query) {
        $gen_query->bind_param("i", $user_id);
        $gen_query->execute();
        $gen_result = $gen_query->get_result();
        while ($row = $gen_result->fetch_assoc()) {
            $row['rating'] = intval($row['rating']);
            $reviews[] = $row;
        }
        $gen_query->close();
    }

    $gen_stats = $conn->prepare(
        "SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as review_count
         FROM reviews WHERE target_user_id = ?"
    );
    if ($gen_stats) {
        $gen_stats->bind_param("i", $user_id);
        $gen_stats->execute();
        $gs_result = $gen_stats->get_result();
        $gs = $gs_result->fetch_assoc();
        $avg_rating = round(floatval($gs['avg_rating']), 1);
        $review_count = intval($gs['review_count']);
        $gen_stats->close();
    }
}

// Build specialties from services
$specialties = array();
foreach ($services as $svc) {
    $specialties[] = $svc['service_name'];
}

// Build response
echo json_encode(array(
    'success' => true,
    'spa' => array(
        'spa_id'           => $spa_id,
        'spa_name'         => $profile['spa_name'] ?: $profile['owner_name'],
        'subtitle'         => $profile['services_offered'] ?: 'Pet Grooming',
        'about'            => 'Welcome to ' . ($profile['spa_name'] ?: $profile['owner_name']) . '. We offer professional pet grooming and spa services.',
        'experience_years' => 0,
        'opening_hours'    => '9:00 AM',
        'closing_hours'    => '8:00 PM',
        'rating'           => $avg_rating,
        'review_count'     => $review_count,
        'owner_name'       => $profile['owner_name'],
        'email'            => $profile['email'],
        'phone'            => $profile['phone'],
        'address'          => $profile['address'] ?? '',
        'city'             => $profile['city'] ?? '',
        'profile_image'    => $profile['profile_image'],
        'min_price'        => ($min_price < PHP_INT_MAX) ? intval($min_price) : 0,
        'specialties'      => array_slice($specialties, 0, 6)
    ),
    'services' => $services,
    'reviews'  => $reviews
));

$conn->close();
?>
