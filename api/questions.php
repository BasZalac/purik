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

require __DIR__ . '/require_login.php';
require __DIR__ . '/../db.php';

$stmt = $conn->prepare('SELECT qid, qtext FROM question ORDER BY qid DESC');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}

$stmt->execute();
$stmt->bind_result($qid, $qtext);

$answerStmt = $conn->prepare('SELECT aid, atext FROM answer WHERE qid = ? ORDER BY aid ASC');
$voteStmt = $conn->prepare('SELECT COUNT(*) FROM vote WHERE qid = ?');

if (!$answerStmt || !$voteStmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}

$questions = [];
while ($stmt->fetch()) {
    $questionId = (int)$qid;

    $answerStmt->bind_param('i', $questionId);
    $answerStmt->execute();
    $answerResult = $answerStmt->get_result();

    $answers = [];
    while ($answer = $answerResult->fetch_assoc()) {
        $answers[] = [
            'aid' => (int)$answer['aid'],
            'atext' => $answer['atext'],
        ];
    }

    $voteStmt->bind_param('i', $questionId);
    $voteStmt->execute();
    $voteStmt->bind_result($voteCount);
    $voteStmt->fetch();
    $voteStmt->free_result();

    $questions[] = [
        'qid' => $questionId,
        'qtext' => $qtext,
        'answers' => $answers,
        'hasVotes' => ((int)$voteCount > 0),
    ];
}

$answerStmt->close();
$voteStmt->close();
$stmt->close();

echo json_encode($questions);
