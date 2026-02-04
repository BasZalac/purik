<?php
session_start();
header('Content-Type: application/json');

// Hibák megjelenítése fejlesztéshez (opcionális, csak lokálban!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Csak POST metódus engedélyezett
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["error" => "Csak POST kérés engedélyezett."]);
  exit;
}

// Adatbázis kapcsolat
require __DIR__ . '/../db.php';

// JSON input olvasása
$input = json_decode(file_get_contents('php://input'), true);
$qid = $input['qid'] ?? null;

// Érvényesség ellenőrzése
if (!$qid || !is_numeric($qid)) {
  http_response_code(400);
  echo json_encode(["error" => "Hiányzó vagy érvénytelen kérdésazonosító."]);
  exit;
}

// Törlés
$stmt = $conn->prepare("DELETE FROM question WHERE qid = ?");
$stmt->bind_param("i", $qid);

if ($stmt->execute()) {
  echo json_encode(["message" => "Kérdés törölve."]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Nem sikerült törölni a kérdést."]);
}