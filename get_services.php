<?php
header('Content-Type: application/json');
require_once 'db.php';

// Get parameters
$provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : 0;
$provider_type = isset($_GET['provider_type']) ? $_GET['provider_type'] : '';

// Validate inputs
if ($provider_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid provider ID'
    ]);
    exit;
}

if (!in_array($provider_type, ['doctor', 'spa'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid provider type. Must be doctor or spa'
    ]);
    exit;
}

try {
    // Fetch services for the provider
    $stmt = $conn->prepare("
        SELECT 
            service_id,
            service_name,
            description,
            duration,
            price,
            is_active
        FROM services 
        WHERE provider_id = ? 
        AND provider_type = ? 
        AND is_active = 1
        ORDER BY service_name ASC
    ");
    
    $stmt->bind_param("is", $provider_id, $provider_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            'service_id' => $row['service_id'],
            'service_name' => $row['service_name'],
            'description' => $row['description'],
            'duration' => $row['duration'],
            'price' => $row['price'],
            'is_active' => $row['is_active']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'services' => $services,
        'count' => count($services)
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching services: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
