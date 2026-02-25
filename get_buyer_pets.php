<?php
// get_buyer_pets.php - Fetch pets bought by a buyer via transactions
require_once 'db.php';

header('Content-Type: application/json');

$buyer_id = isset($_GET['buyer_id']) ? (int)$_GET['buyer_id'] : 0;

if ($buyer_id <= 0) {
    echo json_encode(array("success" => false, "message" => "Valid buyer_id is required"));
    exit;
}

try {
    $stmt = $conn->prepare("SELECT DISTINCT p.pet_id, p.pet_name, p.species, p.breed, 
                            CAST(p.age AS CHAR) as age, p.gender, p.price,
                            (SELECT pi.image_url FROM pet_images pi WHERE pi.pet_id = p.pet_id LIMIT 1) as image_url
                            FROM pet_transactions pt
                            INNER JOIN pets p ON pt.pet_id = p.pet_id
                            WHERE pt.buyer_id = ? AND pt.payment_status IN ('SUCCESS', 'CONFIRMED')
                            ORDER BY p.pet_name ASC");
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pets = [];
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }
    $stmt->close();

    echo json_encode(array(
        "success" => true,
        "pets" => $pets,
        "count" => count($pets)
    ));

} catch (Exception $e) {
    echo json_encode(array("success" => false, "message" => "Error: " . $e->getMessage()));
}

$conn->close();
?>
