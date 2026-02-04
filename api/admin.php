<?php
file_put_contents(__DIR__ . '/admin_debug.log', "admin.php elindult\n", FILE_APPEND);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(["error" => "PHP hiba: $errstr ($errfile:$errline)"]);
    exit;
});

require __DIR__ . '/./require_admin.php'; // Csak admin használhatja
require __DIR__ . '/../db.php';

// Bemenet beolvasása
$data = json_decode(file_get_contents('php://input'), true);
$qtext = trim($data['qtext'] ?? '');
$answers = $data['answers'] ?? [];

if ($qtext === '' || !is_array($answers) || count($answers) < 2) {
    http_response_code(400);
    echo json_encode(["error" => "Legalább egy kérdés és két válasz szükséges."]);
    exit;
}

$conn->begin_transaction();

try {
    // Kérdés beszúrása (created_by nélkül)
    $stmt = $conn->prepare("INSERT INTO question (qtext) VALUES (?)");
    $stmt->bind_param("s", $qtext);
    $stmt->execute();
    $qid = $stmt->insert_id;

    // Válaszok beszúrása
    $stmt = $conn->prepare("INSERT INTO answer (qid, atext) VALUES (?, ?)");
    foreach ($answers as $atext) {
        $atext = trim($atext);
        if ($atext !== '') {
            $stmt->bind_param("is", $qid, $atext);
            $stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(["message" => "Kérdés és válaszok elmentve.", "qid" => $qid]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => "Hiba történt: " . $e->getMessage()]);
}