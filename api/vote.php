<?php
// Csak komoly hibákat jelenítünk meg, hogy ne zavarja a JSON választ
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

header('Content-Type: application/json');

// A require_login.php már hívja a session_start()-ot
require __DIR__ . '/require_login.php';

// A db.php a gyökérben van
require __DIR__ . '/../db.php';

// Bemenet beolvasása
$input = json_decode(file_get_contents('php://input'), true);
$aid = isset($input['aid']) ? intval($input['aid']) : null;
$qid = isset($input['qid']) ? intval($input['qid']) : null;
$uid = $_SESSION['uid'] ?? null;

// Debug log (fejlesztéshez)
file_put_contents(__DIR__ . '/debug.log', json_encode([
  'raw' => file_get_contents('php://input'),
  'decoded' => $input,
  'aid' => $aid,
  'qid' => $qid,
  'uid' => $uid,
  'session' => $_SESSION
]) . PHP_EOL, FILE_APPEND);

// Ellenőrzés: minden mező jelen van (uid lehet 0 is!)
if (!isset($aid, $qid, $uid)) {
  echo json_encode(['error' => 'Hiányzó vagy érvénytelen kérdés-, válasz- vagy felhasználóazonosító.']);
  exit;
}

// Ellenőrzés: válasz valóban a kérdéshez tartozik?
$stmt = $conn->prepare("SELECT COUNT(*) FROM answer WHERE aid = ? AND qid = ?");
$stmt->bind_param("ii", $aid, $qid);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count === 0) {
  echo json_encode(['error' => 'Érvénytelen válasz vagy kérdés.']);
  exit;
}

// Szavazat rögzítése (vtime nélkül)
$stmt = $conn->prepare("INSERT INTO vote (uid, qid, aid) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $uid, $qid, $aid);
if ($stmt->execute()) {
  echo json_encode(['message' => 'Szavazat rögzítve.']);
} else {
  echo json_encode(['error' => 'Nem sikerült rögzíteni a szavazatot.']);
}
$stmt->close();