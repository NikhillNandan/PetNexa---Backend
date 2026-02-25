<?php
/**
 * get_spa_reviews.php - Get reviews for a spa
 * Deploy to: htdocs/petnexa_API/get_spa_reviews.php
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
    
    // Get spa_id
    $spa_query = $conn->prepare("SELECT spa_id FROM spa_profiles WHERE user_id = ?");
    $spa_query->bind_param("i", $user_id);
    $spa_query->execute();
    $spa_result = $spa_query->get_result();
    
    if ($spa_result->num_rows == 0) {
        $response['error'] = true;
        $response['message'] = 'Spa profile not found';
        echo json_encode($response);
        $spa_query->close();
        exit;
    }
    
    $spa_row = $spa_result->fetch_assoc();
    $spa_id = $spa_row['spa_id'];
    $spa_query->close();
    
    // Get reviews
    $reviews_query = $conn->prepare("SELECT sr.review_id, sr.rating, sr.review_text, sr.created_at,
                                             u.full_name as reviewer_name
                                      FROM spa_reviews sr
                                      LEFT JOIN users u ON sr.user_id = u.user_id
                                      WHERE sr.spa_id = ?
                                      ORDER BY sr.created_at DESC");
    $reviews_query->bind_param("i", $spa_id);
    $reviews_query->execute();
    $reviews_result = $reviews_query->get_result();
    
    $reviews = array();
    while ($row = $reviews_result->fetch_assoc()) {
        $reviews[] = array(
            'review_id' => intval($row['review_id']),
            'rating' => intval($row['rating']),
            'review_text' => $row['review_text'],
            'reviewer_name' => $row['reviewer_name'],
            'created_at' => $row['created_at']
        );
    }
    
    $reviews_query->close();
    
    $response['error'] = false;
    $response['message'] = 'Reviews retrieved successfully';
    $response['reviews'] = $reviews;
    $response['count'] = count($reviews);
    
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();
?>
