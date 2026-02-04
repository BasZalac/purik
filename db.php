<?php
$host = 'localhost';
$user = 'user';
$pass = '0';
$dbname = 'vote';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Adatbázis kapcsolat sikertelen: " . $conn->connect_error]);
    exit;
}
