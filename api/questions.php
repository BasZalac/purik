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

require __DIR__ . '/require_login.php';
require __DIR__ . '/../db.php';

// Lekérdezzük az összes kérdést
$stmt = $conn->prepare("SELECT qid, qtext FROM question ORDER BY qid DESC");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}

$stmt->execute();
$stmt->bind_result($qid, $qtext);

$questions = [];
while ($stmt->fetch()) {
    $questions[] = [
        "qid" => (int)$qid,
        "qtext" => $qtext
    ];
}

$stmt->close();
echo json_encode($questions);