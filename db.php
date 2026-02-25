<?php
$host = "localhost";
$user = "root";
$username = "root"; // alias for PDO-based files
$password = "";
$database = "petnexa_db";
$dbname = "petnexa_db"; // alias for PDO-based files

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => true, "message" => "Connection failed: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");
?>