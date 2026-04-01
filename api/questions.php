<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('html_errors', '0');
error_reporting(E_ALL);

ob_start();

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        echo json_encode(["error" => "Fatális hiba: {$error['message']} ({$error['file']}:{$error['line']})"]);
    }
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo json_encode(["error" => "PHP hiba: $errstr ($errfile:$errline)"]);
    exit;
});

set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo json_encode(["error" => "Kivétel: " . $e->getMessage()]);
    exit;
});

function pickFirstExistingTable($conn, array $candidates): ?string {
    foreach ($candidates as $tableName) {
        $escaped = $conn->real_escape_string($tableName);
        $result = $conn->query("SHOW TABLES LIKE '{$escaped}'");
        if ($result && $result->num_rows > 0) {
            return $tableName;
        }
    }
    return null;
}

require __DIR__ . '/require_login.php';
require __DIR__ . '/../db.php';

$answerTable = pickFirstExistingTable($conn, ['answer', 'answers']);
$voteTable = pickFirstExistingTable($conn, ['vote', 'votes']);

if ($answerTable === null || $voteTable === null) {
    http_response_code(500);
    echo json_encode(["error" => "Hiányzó adatbázis tábla (answer/answers vagy vote/votes)."]);
    exit;
}

$questionsResult = $conn->query('SELECT qid, qtext FROM question ORDER BY qid DESC');
if (!$questionsResult) {
    http_response_code(500);
    echo json_encode(["error" => "Kérdés lekérdezési hiba: " . $conn->error]);
    exit;
}

$questions = [];
while ($question = $questionsResult->fetch_assoc()) {
    $questionId = (int)$question['qid'];

    $answers = [];
    $answersResult = $conn->query("SELECT aid, atext FROM {$answerTable} WHERE qid = {$questionId} ORDER BY aid ASC");
    if (!$answersResult) {
        http_response_code(500);
        echo json_encode(["error" => "Válasz lekérdezési hiba: " . $conn->error]);
        exit;
    }
    while ($answer = $answersResult->fetch_assoc()) {
        $answers[] = [
            'aid' => (int)$answer['aid'],
            'atext' => $answer['atext'],
        ];
    }

    $voteCount = 0;
    $voteResult = $conn->query("SELECT COUNT(*) AS cnt FROM {$voteTable} WHERE qid = {$questionId}");
    if (!$voteResult) {
        http_response_code(500);
        echo json_encode(["error" => "Szavazat lekérdezési hiba: " . $conn->error]);
        exit;
    }
    $voteRow = $voteResult->fetch_assoc();
    if ($voteRow) {
        $voteCount = (int)$voteRow['cnt'];
    }

    $questions[] = [
        'qid' => $questionId,
        'qtext' => $question['qtext'],
        'answers' => $answers,
        'hasVotes' => ($voteCount > 0),
    ];
}

echo json_encode($questions);
