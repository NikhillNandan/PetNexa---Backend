<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'db.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch($action) {
    case 'add_service':
    case 'add':
        addSpaService();
        break;
    case 'update_service':
    case 'update':
        updateSpaService();
        break;
    case 'delete_service':
    case 'delete':
        deleteSpaService();
        break;
    case 'get_services':
    case 'get':
        getSpaServices();
        break;
    default:
        echo json_encode(['error' => true, 'message' => 'Invalid action. Use: add_service, update_service, delete_service, get_services']);
        exit;
}

function addSpaService() {
    global $host, $dbname, $username, $password;
    
    // Support both form-encoded POST and JSON body
    $service_name = isset($_POST['service_name']) ? $_POST['service_name'] : null;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : null;
    $duration = isset($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : (isset($_POST['duration']) ? intval($_POST['duration']) : null);
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : (isset($_POST['spa_id']) ? intval($_POST['spa_id']) : 0);
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    
    // Fallback to JSON body if POST params not found
    if ($service_name === null) {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $service_name = isset($input['service_name']) ? $input['service_name'] : null;
            $price = isset($input['price']) ? floatval($input['price']) : null;
            $duration = isset($input['duration_minutes']) ? intval($input['duration_minutes']) : (isset($input['duration']) ? intval($input['duration']) : null);
            $user_id = isset($input['user_id']) ? intval($input['user_id']) : (isset($input['spa_id']) ? intval($input['spa_id']) : 0);
            $description = isset($input['description']) ? $input['description'] : '';
        }
    }
    
    if (!$service_name || $price === null || $duration === null || $user_id <= 0) {
        echo json_encode(['error' => true, 'message' => 'Missing required fields: service_name, price, duration_minutes, user_id']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("INSERT INTO spa_services (spa_id, service_name, price, duration_minutes, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $service_name, $price, $duration, $description]);
        
        echo json_encode(['error' => false, 'message' => 'Service added successfully', 'service_id' => $conn->lastInsertId()]);
        
    } catch(PDOException $e) {
        echo json_encode(['error' => true, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateSpaService() {
    global $host, $dbname, $username, $password;
    
    // Support both form-encoded POST and JSON body
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : (isset($_POST['spa_id']) ? intval($_POST['spa_id']) : 0);
    $service_name = isset($_POST['service_name']) ? $_POST['service_name'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $duration = isset($_POST['duration_minutes']) ? $_POST['duration_minutes'] : (isset($_POST['duration']) ? $_POST['duration'] : null);
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    
    // Fallback to JSON body
    if ($service_id <= 0) {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $service_id = isset($input['service_id']) ? intval($input['service_id']) : 0;
            $user_id = isset($input['user_id']) ? intval($input['user_id']) : (isset($input['spa_id']) ? intval($input['spa_id']) : 0);
            $service_name = isset($input['service_name']) ? $input['service_name'] : null;
            $price = isset($input['price']) ? $input['price'] : null;
            $duration = isset($input['duration_minutes']) ? $input['duration_minutes'] : (isset($input['duration']) ? $input['duration'] : null);
            $description = isset($input['description']) ? $input['description'] : '';
        }
    }
    
    if ($service_id <= 0 || $user_id <= 0) {
        echo json_encode(['error' => true, 'message' => 'Missing required fields: service_id, user_id']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("UPDATE spa_services SET service_name = ?, price = ?, duration_minutes = ?, description = ? WHERE service_id = ? AND spa_id = ?");
        $stmt->execute([$service_name, $price, $duration, $description, $service_id, $user_id]);
        
        echo json_encode(['error' => false, 'message' => 'Service updated successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['error' => true, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteSpaService() {
    global $host, $dbname, $username, $password;
    
    // Support both form-encoded POST and JSON body
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : (isset($_POST['spa_id']) ? intval($_POST['spa_id']) : 0);
    
    // Fallback to JSON body
    if ($service_id <= 0) {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $service_id = isset($input['service_id']) ? intval($input['service_id']) : 0;
            $user_id = isset($input['user_id']) ? intval($input['user_id']) : (isset($input['spa_id']) ? intval($input['spa_id']) : 0);
        }
    }
    
    if ($service_id <= 0 || $user_id <= 0) {
        echo json_encode(['error' => true, 'message' => 'Missing required fields: service_id, user_id']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("DELETE FROM spa_services WHERE service_id = ? AND spa_id = ?");
        $stmt->execute([$service_id, $user_id]);
        
        echo json_encode(['error' => false, 'message' => 'Service deleted successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['error' => true, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getSpaServices() {
    global $host, $dbname, $username, $password;
    
    // Support both user_id and spa_id parameter names
    $spa_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (isset($_GET['spa_id']) ? intval($_GET['spa_id']) : 0);
    
    if ($spa_id <= 0) {
        echo json_encode(['error' => true, 'message' => 'Invalid user/spa ID']);
        exit;
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT service_id, service_name, price, duration_minutes, description FROM spa_services WHERE spa_id = ? ORDER BY service_name");
        $stmt->execute([$spa_id]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formatted_services = array_map(function($service) use ($spa_id) {
            return [
                'id' => intval($service['service_id']),
                'service_name' => $service['service_name'],
                'price' => floatval($service['price']),
                'duration_minutes' => intval($service['duration_minutes']),
                'description' => $service['description'],
                'status' => 'Active',
                'spa_owner_id' => $spa_id
            ];
        }, $services);
        
        echo json_encode(['error' => false, 'services' => $formatted_services]);
        
    } catch(PDOException $e) {
        echo json_encode(['error' => true, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
