<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('html_errors', '0');
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(["error" => "PHP hiba: $errstr ($errfile:$errline)"]);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Csak POST kérés engedélyezett."]);
    exit;
}

session_start();
require __DIR__ . '/../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$pass = $data['pass'] ?? '';

if ($name === '' || $pass === '') {
    http_response_code(400);
    echo json_encode(["error" => "Név és jelszó megadása kötelező."]);
    exit;
}

$stmt = $conn->prepare('SELECT uid, role, pass FROM user WHERE name = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "SELECT hiba: " . $conn->error]);
    exit;
}

$stmt->bind_param('s', $name);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($uid, $role, $hashedPass);

$wasRegistered = false;

if ($stmt->num_rows === 0) {
    $stmt->close();

    $hashed = password_hash($pass, PASSWORD_DEFAULT);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $newRole = 'user';

    $insert = $conn->prepare('INSERT INTO user (name, pass, ip, role) VALUES (?, ?, ?, ?)');
    if (!$insert) {
        http_response_code(500);
        echo json_encode(["error" => "Mentési hiba: " . $conn->error]);
        exit;
    }
    $insert->bind_param('ssss', $name, $hashed, $ip, $newRole);
    $insert->execute();
    $uid = (int)$insert->insert_id;
    $role = $newRole;
    $insert->close();
    $wasRegistered = true;
} else {
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($pass, $hashedPass)) {
        http_response_code(401);
        echo json_encode(["error" => "Hibás jelszó."]);
        exit;
    }
}

$_SESSION['uid'] = (int)$uid;
$_SESSION['name'] = $name;
$_SESSION['role'] = $role;
$_SESSION['user'] = ['uid' => (int)$uid, 'name' => $name, 'role' => $role];

http_response_code($wasRegistered ? 201 : 200);
echo json_encode([
    'message' => $wasRegistered ? 'Sikeres regisztráció és bejelentkezés.' : 'Sikeres bejelentkezés.',
    'role' => $role,
    'registered' => $wasRegistered,
]);

$conn->close();
