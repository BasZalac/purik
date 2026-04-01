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

require __DIR__ . '/require_login.php';
require __DIR__ . '/../db.php';

$stmt = $conn->prepare('SELECT qid, qtext FROM question ORDER BY qid DESC');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Végrehajtási hiba: " . $stmt->error]);
    exit;
}

$stmt->bind_result($qid, $qtext);
$baseQuestions = [];
while ($stmt->fetch()) {
    $baseQuestions[] = [
        'qid' => (int)$qid,
        'qtext' => $qtext,
    ];
}
$stmt->close();

$questions = [];
foreach ($baseQuestions as $baseQuestion) {
    $questionId = $baseQuestion['qid'];

    $answers = [];
    $answerStmt = $conn->prepare('SELECT aid, atext FROM answer WHERE qid = ? ORDER BY aid ASC');
    if (!$answerStmt) {
        http_response_code(500);
        echo json_encode(["error" => "Válasz lekérdezési hiba: " . $conn->error]);
        exit;
    }
    $answerStmt->bind_param('i', $questionId);
    if (!$answerStmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Válasz végrehajtási hiba: " . $answerStmt->error]);
        exit;
    }
    $answerStmt->bind_result($aid, $atext);
    while ($answerStmt->fetch()) {
        $answers[] = [
            'aid' => (int)$aid,
            'atext' => $atext,
        ];
    }
    $answerStmt->close();

    $voteCount = 0;
    $voteStmt = $conn->prepare('SELECT COUNT(*) FROM vote WHERE qid = ?');
    if (!$voteStmt) {
        http_response_code(500);
        echo json_encode(["error" => "Szavazat lekérdezési hiba: " . $conn->error]);
        exit;
    }
    $voteStmt->bind_param('i', $questionId);
    if (!$voteStmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Szavazat végrehajtási hiba: " . $voteStmt->error]);
        exit;
    }
    $voteStmt->bind_result($voteCount);
    $voteStmt->fetch();
    $voteStmt->close();

    $questions[] = [
        'qid' => $questionId,
        'qtext' => $baseQuestion['qtext'],
        'answers' => $answers,
        'hasVotes' => ((int)$voteCount > 0),
    ];
}

echo json_encode($questions);
