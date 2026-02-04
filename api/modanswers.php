<?php
require __DIR__ . '/require_admin.php';
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Bemenet beolvasása
$data = json_decode(file_get_contents('php://input'), true);
$qid = intval($data['qid'] ?? 0);
$answers = $data['answers'] ?? [];

if ($qid <= 0 || !is_array($answers) || count($answers) < 2) {
    http_response_code(400);
    echo json_encode(["error" => "Érvényes kérdésazonosító és legalább két válasz szükséges."]);
    exit;
}

// Ellenőrzés: van-e már szavazat a kérdésre?
$stmt = $conn->prepare("SELECT COUNT(*) FROM votes WHERE qid = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}
$stmt->bind_param("i", $qid);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    http_response_code(403);
    echo json_encode(["error" => "A kérdés már kapott szavazatot, a válaszok nem módosíthatók."]);
    exit;
}

$conn->begin_transaction();

try {
    // Régi válaszok törlése
    $stmt = $conn->prepare("DELETE FROM answers WHERE qid = ?");
    if (!$stmt) throw new Exception("Választörlési hiba: " . $conn->error);
    $stmt->bind_param("i", $qid);
    $stmt->execute();
    $stmt->close();

    // Új válaszok beszúrása
    $stmt = $conn->prepare("INSERT INTO answers (qid, atext) VALUES (?, ?)");
    if (!$stmt) throw new Exception("Válaszbeszúrási hiba: " . $conn->error);

    foreach ($answers as $atext) {
        $atext = trim($atext);
        if ($atext !== '') {
            $stmt->bind_param("is", $qid, $atext);
            $stmt->execute();
        }
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(["message" => "Válaszok frissítve."]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => "Hiba történt: " . $e->getMessage()]);
}