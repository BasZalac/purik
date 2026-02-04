<?php
file_put_contents(__DIR__ . '/../debug.log', ">> MODQUESTION.PHP ELINDULT\n", FILE_APPEND);
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('html_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode(["error" => "Fatális PHP hiba: {$error['message']} ({$error['file']}:{$error['line']})"]);
        exit;
    }
});

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

require __DIR__ . '/require_login.php';
require __DIR__ . '/../db.php';

$raw = file_get_contents('php://input');
file_put_contents(__DIR__ . '/../debug.log', ">> RAW POST: $raw\n", FILE_APPEND);

$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Érvénytelen JSON formátum."]);
    exit;
}

$qid = intval($data['qid'] ?? 0);
$qtext = trim($data['qtext'] ?? '');

file_put_contents(__DIR__ . '/../debug.log', ">> DEKÓDOLT: qid=$qid | qtext=$qtext\n", FILE_APPEND);

if ($qid <= 0 || $qtext === '') {
    http_response_code(400);
    echo json_encode(["error" => "Hiányzó vagy érvénytelen adatok."]);
    exit;
}

$stmt = $conn->prepare("UPDATE question SET qtext = ? WHERE qid = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}
$stmt->bind_param("si", $qtext, $qid);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["message" => "Kérdés frissítve."]);
} else {
    echo json_encode(["message" => "Nem történt változás."]);
}
$stmt->close();