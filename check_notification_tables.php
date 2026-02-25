<?php
header('Content-Type: text/plain');
require_once 'db.php';

// Show all tables
$result = $conn->query("SHOW TABLES");
echo "=== ALL TABLES IN petnexa_db ===\n";
while ($row = $result->fetch_row()) {
    echo $row[0] . "\n";
}

// Check notifications table
echo "\n=== NOTIFICATIONS TABLE ===\n";
$result = $conn->query("DESCRIBE notifications");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ") " . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . " " . $row['Default'] . "\n";
    }
} else {
    echo "Table does not exist\n";
}

// Check fcm_tokens table
echo "\n=== FCM_TOKENS TABLE ===\n";
$result = $conn->query("DESCRIBE fcm_tokens");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ") " . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . " " . $row['Default'] . "\n";
    }
} else {
    echo "Table does not exist\n";
}
?>
