<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id']) || !is_numeric($data['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Hiányzó vagy hibás felhasználóazonosító"]);
        exit;
    }

    $uid = (int)$data['id'];
    $newName = isset($data['name']) ? trim($data['name']) : null;
    $newPass = isset($data['pass']) ? trim($data['pass']) : null;

    if ($newName === null && $newPass === null) {
        http_response_code(400);
        echo json_encode(["error" => "Név vagy jelszó megadása szükséges"]);
        exit;
    }

    // Felhasználó létezik?
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(404);
        echo json_encode(["error" => "A felhasználó nem található"]);
        exit;
    }

    // Ha név módosul, ellenőrizzük, hogy nem foglalt-e
    if ($newName && $newName !== $user['name']) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE name = ?");
        $stmt->execute([$newName]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(["error" => "Ez a név már foglalt"]);
            exit;
        }
    }

    // Módosítás összeállítása
    $fields = [];
    $params = [];

    if ($newName) {
        $fields[] = "name = ?";
        $params[] = $newName;
    }

    if ($newPass) {
        $fields[] = "pass = ?";
        $params[] = password_hash($newPass, PASSWORD_DEFAULT);
    }

    $params[] = $uid;
    $sql = "UPDATE user SET " . implode(", ", $fields) . " WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    http_response_code(200);
    echo json_encode([
        "message" => "Felhasználó módosítva",
        "id" => $uid,
        "name" => $newName ?? $user['name']
    ]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Nem támogatott metódus"]);