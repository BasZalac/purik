<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// JSON hibakezelés
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

// Felhasználó keresése
$stmt = $conn->prepare("SELECT uid, role, pass FROM user WHERE name = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "SELECT hiba: " . $conn->error]);
    exit;
}
$stmt->bind_param("s", $name);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($uid, $role, $hashedPass);

if ($stmt->num_rows > 0 && $stmt->fetch()) {
    if (!password_verify($pass, $hashedPass)) {
        http_response_code(401);
        echo json_encode(["error" => "Hibás jelszó."]);
        exit;
    }
    $stmt->close(); // 🔧 EZ HIÁNYZOTT
} else {
    $stmt->close();

    $role = ($name === 'admin') ? 'admin' : 'user';
    $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO user (name, role, pass) VALUES (?, ?, ?)");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "INSERT hiba: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("sss", $name, $role, $hashedPass);
    $stmt->execute();
    $uid = $stmt->insert_id;
    $stmt->close();
}

// Session beállítása
$_SESSION['uid'] = $uid;
$_SESSION['name'] = $name;
$_SESSION['role'] = $role;
$_SESSION['user'] = ['uid' => $uid, 'name' => $name, 'role' => $role];

echo json_encode(["message" => "Sikeres bejelentkezés.", "role" => $role]);

$conn->close();