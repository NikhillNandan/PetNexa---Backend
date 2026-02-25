<?php
require_once 'db.php';

echo "=== CERTIFICATES IN DB ===\n";
$result = $conn->query("SELECT * FROM certificates ORDER BY certificate_id DESC LIMIT 10");
if ($result->num_rows == 0) {
    echo "NO CERTIFICATES FOUND IN DATABASE!\n";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['certificate_id'] . 
             " | Pet ID: " . $row['pet_id'] . 
             " | Type: " . $row['certificate_type'] . 
             " | File: " . $row['certificate_file'] . 
             " | Issued: " . $row['issued_date'] .
             " | Created: " . $row['created_at'] . "\n";
    }
}

echo "\n=== PETS WITH STATUS ===\n";
$result = $conn->query("SELECT pet_id, pet_name, breed, species, availability_status FROM pets ORDER BY pet_id DESC LIMIT 10");
if ($result->num_rows == 0) {
    echo "NO PETS FOUND!\n";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['pet_id'] . 
             " | Name: " . $row['pet_name'] . 
             " | Breed: " . $row['breed'] .
             " | Species: " . $row['species'] .
             " | Status: " . $row['availability_status'] . "\n";
    }
}

echo "\n=== CHECK: certificate_file column allows NULL? ===\n";
$result = $conn->query("SHOW COLUMNS FROM certificates WHERE Field = 'certificate_file'");
$row = $result->fetch_assoc();
echo "Null: " . $row['Null'] . " | Default: " . ($row['Default'] ?? 'none') . "\n";

echo "\n=== CHECK: issued_by column allows NULL? ===\n";
$result = $conn->query("SHOW COLUMNS FROM certificates WHERE Field = 'issued_by'");
$row = $result->fetch_assoc();
echo "Null: " . $row['Null'] . " | Default: " . ($row['Default'] ?? 'none') . "\n";
?>
