<?php
header('Content-Type: application/json');
require_once 'db.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$provider_id = isset($data['provider_id']) ? intval($data['provider_id']) : 0;
$provider_type = isset($data['provider_type']) ? $data['provider_type'] : '';
$service_name = isset($data['service_name']) ? trim($data['service_name']) : '';
$description = isset($data['description']) ? trim($data['description']) : '';
$duration = isset($data['duration']) ? trim($data['duration']) : '';
$price = isset($data['price']) ? floatval($data['price']) : 0;

// Validate inputs
if ($provider_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid provider ID']);
    exit;
}

if (!in_array($provider_type, ['doctor', 'spa'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid provider type']);
    exit;
}

if (empty($service_name)) {
    echo json_encode(['success' => false, 'message' => 'Service name is required']);
    exit;
}

if ($price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid price']);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO services (provider_id, provider_type, service_name, description, duration, price) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("issssd", $provider_id, $provider_type, $service_name, $description, $duration, $price);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Service added successfully',
            'service_id' => $conn->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add service'
        ]);
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
