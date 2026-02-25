<?php
require_once 'db.php';

echo "=== PETS TABLE ===\n";
$result = $conn->query("DESCRIBE pets");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== USERS TABLE ===\n";
$result = $conn->query("DESCRIBE users");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== PET_TRANSACTIONS TABLE ===\n";
$result = $conn->query("DESCRIBE pet_transactions");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== PET_IMAGES TABLE ===\n";
$result = $conn->query("DESCRIBE pet_images");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== CERTIFICATES TABLE ===\n";
$result = $conn->query("DESCRIBE certificates");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
