<?php
require 'require_login.php'; // Csak bejelentkezett felhasználó használhatja
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Válaszok listázása
    if (isset($_GET['qid'])) {
        $stmt = $pdo->prepare("SELECT * FROM answer WHERE qid = ? ORDER BY created_at DESC");
        $stmt->execute([$_GET['qid']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        $stmt = $pdo->query("SELECT * FROM answer ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Új válasz létrehozása
    $data = json_decode(file_get_contents('php://input'), true);
    $qid = isset($data['qid']) ? (int)$data['qid'] : 0;
    $atext = isset($data['atext']) ? trim($data['atext']) : '';

    if ($qid <= 0 || $atext === '') {
        http_response_code(400);
        echo json_encode(["error" => "qid és atext kötelező"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO answer (qid, atext) VALUES (?, ?)");
    $stmt->execute([$qid, $atext]);

    echo json_encode([
        "message" => "Válasz létrehozva",
        "aid" => $pdo->lastInsertId()
    ]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Nem támogatott metódus"]);