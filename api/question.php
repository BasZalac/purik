<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('html_errors', '0');
error_reporting(E_ALL);

// Fatális hibák elkapása
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

// Kötelező belépés és adatbázis
require __DIR__ . '/require_login.php';
require __DIR__ . '/../db.php';

// Bemenet
$qid = isset($_GET['qid']) ? intval($_GET['qid']) : 0;

if ($qid <= 0) {
    $res = $conn->query("SELECT qid, qtext FROM question ORDER BY qid DESC LIMIT 1");
    if (!$res || $res->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Nincs elérhető kérdés."]);
        exit;
    }
    $row = $res->fetch_assoc();
    $qid = (int)$row['qid'];
    $qtext = $row['qtext'];
} else {
    // 🔧 Itt a fix: qid is bekerül a lekérdezésbe
    $stmt = $conn->prepare("SELECT qid, qtext FROM question WHERE qid = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $qid);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Végrehajtási hiba: " . $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        http_response_code(404);
        echo json_encode(["error" => "A kérdés nem található."]);
        exit;
    }

    $qid = (int)$row['qid'];
    $qtext = $row['qtext'];
}

// Válaszok lekérdezése
$stmt = $conn->prepare("SELECT aid, atext FROM answer WHERE qid = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}
$stmt->bind_param("i", $qid);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Végrehajtási hiba: " . $stmt->error]);
    exit;
}
$result = $stmt->get_result();
if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Eredményhiba: " . $stmt->error]);
    exit;
}

$answers = [];
while ($row = $result->fetch_assoc()) {
    $answers[] = [
        "aid" => (int)$row['aid'],
        "atext" => $row['atext']
    ];
}
$stmt->close();

echo json_encode([
    "qid" => $qid,
    "qtext" => $qtext,
    "answers" => $answers
]);