<?php
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

require __DIR__ . '/require_admin.php';
require __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

// Kérdések lekérdezése szerzővel
$qstmt = $conn->prepare("
    SELECT q.qid, q.qtext, u.name AS author
    FROM questions q
    LEFT JOIN users u ON q.created_by = u.uid
    ORDER BY q.qid DESC
");
if (!$qstmt) {
    http_response_code(500);
    echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
    exit;
}
$qstmt->execute();
$qresult = $qstmt->get_result();

$questions = [];

while ($q = $qresult->fetch_assoc()) {
    $qid = (int)$q['qid'];

    // Válaszok lekérdezése
    $astmt = $conn->prepare("SELECT aid, atext FROM answers WHERE qid = ?");
    if (!$astmt) {
        http_response_code(500);
        echo json_encode(["error" => "Válaszlekérdezési hiba: " . $conn->error]);
        exit;
    }
    $astmt->bind_param("i", $qid);
    $astmt->execute();
    $aresult = $astmt->get_result();

    $answers = [];
    while ($a = $aresult->fetch_assoc()) {
        $answers[] = [
            "aid" => (int)$a['aid'],
            "atext" => $a['atext']
        ];
    }
    $astmt->close();

    $questions[] = [
        "qid" => $qid,
        "qtext" => $q['qtext'],
        "author" => $q['author'] ?? null,
        "answers" => $answers
    ];
}

$qstmt->close();
echo json_encode($questions);