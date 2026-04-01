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

set_exception_handler(function(Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Kivétel: " . $e->getMessage()]);
    exit;
});

require __DIR__ . '/require_admin.php';
require __DIR__ . '/../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$qid = intval($data['qid'] ?? 0);
$answers = $data['answers'] ?? [];

if ($qid <= 0 || !is_array($answers) || count($answers) < 2) {
    http_response_code(400);
    echo json_encode(["error" => "Érvényes kérdésazonosító és legalább két válasz szükséges."]);
    exit;
}

$cleanAnswers = [];
foreach ($answers as $answerText) {
    $answerText = trim((string)$answerText);
    if ($answerText !== '') {
        $cleanAnswers[] = $answerText;
    }
}

if (count($cleanAnswers) < 2) {
    http_response_code(400);
    echo json_encode(["error" => "Legalább két nem üres válasz szükséges."]);
    exit;
}

$stmt = $conn->prepare('SELECT COUNT(*) FROM vote WHERE qid = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}
$stmt->bind_param('i', $qid);
$stmt->execute();
$stmt->bind_result($voteCount);
$stmt->fetch();
$stmt->close();

if ((int)$voteCount > 0) {
    http_response_code(403);
    echo json_encode(["error" => "A kérdésre már érkezett szavazat, a válaszok nem módosíthatók."]);
    exit;
}

$conn->begin_transaction();

try {
    $deleteStmt = $conn->prepare('DELETE FROM answer WHERE qid = ?');
    if (!$deleteStmt) {
        throw new Exception('Választörlési hiba: ' . $conn->error);
    }
    $deleteStmt->bind_param('i', $qid);
    $deleteStmt->execute();
    $deleteStmt->close();

    $insertStmt = $conn->prepare('INSERT INTO answer (qid, atext) VALUES (?, ?)');
    if (!$insertStmt) {
        throw new Exception('Válaszbeszúrási hiba: ' . $conn->error);
    }

    foreach ($cleanAnswers as $answerText) {
        $insertStmt->bind_param('is', $qid, $answerText);
        $insertStmt->execute();
    }
    $insertStmt->close();

    $conn->commit();
    echo json_encode(["message" => "Válaszok frissítve."]);
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => "Hiba történt: " . $e->getMessage()]);
}
