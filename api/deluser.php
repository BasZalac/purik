<?php
require 'require_admin.php'; // Csak admin törölhet
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Hiányzó vagy hibás felhasználóazonosító"]);
        exit;
    }

    $uid = (int)$_GET['id'];

    // Létezik-e a felhasználó?
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE id = ?");
    $stmt->execute([$uid]);
    if ($stmt->fetchColumn() === 0) {
        http_response_code(404);
        echo json_encode(["error" => "A felhasználó nem található"]);
        exit;
    }

    // Felhasználó törlése (szavazatok is törlődnek, ha van ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
    $stmt->execute([$uid]);

    http_response_code(200);
    echo json_encode(["message" => "Felhasználó törölve", "uid" => $uid]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Nem támogatott metódus"]);