<?php
// get_pets.php - Fetch available pets with images
require_once 'db.php';

header('Content-Type: application/json');

$query = "SELECT p.pet_id, p.seller_id, p.pet_name, p.species, p.breed, p.age, p.gender, 
                 p.color, p.price, p.description, p.availability_status,
                 u.full_name as seller_name, u.city as seller_city,
                 (SELECT image_url FROM pet_images WHERE pet_id = p.pet_id LIMIT 1) as image_url
          FROM pets p
          INNER JOIN users u ON p.seller_id = u.user_id
          WHERE p.availability_status = 'AVAILABLE'
          ORDER BY p.created_at DESC";

$result = $conn->query($query);

if ($result) {
    $pets = array();
    while ($row = $result->fetch_assoc()) {
        $row['price'] = (float)$row['price'];
        $row['age'] = (int)$row['age'];
        $row['status'] = strtolower($row['availability_status']);
        $pets[] = $row;
    }
    echo json_encode(array("status" => "success", "pets" => $pets));
} else {
    echo json_encode(array("status" => "error", "message" => "Failed to fetch pets: " . $conn->error));
}

$conn->close();
?>
