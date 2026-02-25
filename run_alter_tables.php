<?php
header('Content-Type: text/plain');
require_once 'db.php';

function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

echo "=== CURRENT NOTIFICATIONS SCHEMA ===\n";
$result = $conn->query("DESCRIBE notifications");
while ($row = $result->fetch_assoc()) {
    echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Add reference_id if missing
if (!columnExists($conn, 'notifications', 'reference_id')) {
    $conn->query("ALTER TABLE notifications ADD COLUMN reference_id INT DEFAULT NULL AFTER type");
    echo "\n+ Added reference_id\n";
} else {
    echo "\n= reference_id exists\n";
}

echo "\n=== CURRENT USERS COLUMNS ===\n";
$result = $conn->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Add fcm_token if missing
if (!columnExists($conn, 'users', 'fcm_token')) {
    $conn->query("ALTER TABLE users ADD COLUMN fcm_token TEXT NULL AFTER last_seen");
    echo "\n+ Added fcm_token to users\n";
} else {
    echo "\n= fcm_token exists in users\n";
}

echo "\nDone!\n";
?>
