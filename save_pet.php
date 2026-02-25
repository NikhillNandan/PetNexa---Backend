<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$buyer_id = isset($data['buyer_id']) ? intval($data['buyer_id']) : 0;
$pet_id = isset($data['pet_id']) ? intval($data['pet_id']) : 0;
$action = isset($data['action']) ? $data['action'] : 'save'; // 'save' or 'unsave'

if ($buyer_id <= 0 || $pet_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid buyer or pet ID']);
    exit;
}

try {
    if ($action === 'save') {
        // Save the pet
        $stmt = $conn->prepare("INSERT IGNORE INTO saved_pets (buyer_id, pet_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $buyer_id, $pet_id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pet saved successfully',
            'is_saved' => true
        ]);
        
    } else if ($action === 'unsave') {
        // Unsave the pet
        $stmt = $conn->prepare("DELETE FROM saved_pets WHERE buyer_id = ? AND pet_id = ?");
        $stmt->bind_param("ii", $buyer_id, $pet_id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pet unsaved successfully',
            'is_saved' => false
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
