<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

require __DIR__ . '/require_admin.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["error" => "Csak POST kérés engedélyezett."]);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$qid = $input['qid'] ?? null;

if (!$qid || !is_numeric($qid)) {
  http_response_code(400);
  echo json_encode(["error" => "Hiányzó vagy érvénytelen kérdésazonosító."]);
  exit;
}

$stmt = $conn->prepare("DELETE FROM question WHERE qid = ?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["error" => "Lekérdezési hiba: " . $conn->error]);
  exit;
}
$stmt->bind_param("i", $qid);

if ($stmt->execute()) {
  echo json_encode(["message" => "Kérdés törölve."]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Nem sikerült törölni a kérdést."]);
}
$stmt->close();
