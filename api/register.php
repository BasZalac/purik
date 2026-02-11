<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(["error" => "PHP hiba: $errstr ($errfile:$errline)"]);
    exit;
});

require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Nem támogatott metódus"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$pass = trim($data['pass'] ?? '');

if ($name === '' || $pass === '') {
    http_response_code(400);
    echo json_encode(["error" => "Név és jelszó megadása kötelező"]);
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE name = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}
$stmt->bind_param("s", $name);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    http_response_code(409);
    echo json_encode(["error" => "Ez a felhasználónév már foglalt"]);
    exit;
}

$hashed = password_hash($pass, PASSWORD_DEFAULT);
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$role = 'user';

$stmt = $conn->prepare("INSERT INTO user (name, pass, ip, role) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Mentési hiba: " . $conn->error]);
    exit;
}
$stmt->bind_param("ssss", $name, $hashed, $ip, $role);
$stmt->execute();
$uid = $stmt->insert_id;
$stmt->close();

http_response_code(201);
echo json_encode([
    "message" => "Felhasználó létrehozva",
    "id" => $uid,
    "name" => $name,
    "ip" => $ip,
    "role" => $role
]);
