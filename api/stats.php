<?php
require 'require_admin.php'; // Csak admin láthatja az összesítést
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Összes kérdés
    $stmt = $pdo->query("SELECT COUNT(*) FROM question");
    $totalQuestions = (int)$stmt->fetchColumn();

    // Összes válasz
    $stmt = $pdo->query("SELECT COUNT(*) FROM answer");
    $totalAnswers = (int)$stmt->fetchColumn();

    // Összes szavazat
    $stmt = $pdo->query("SELECT COUNT(*) FROM vote");
    $totalVotes = (int)$stmt->fetchColumn();

    // Összes felhasználó
    $stmt = $pdo->query("SELECT COUNT(*) FROM user");
    $totalUsers = (int)$stmt->fetchColumn();

    // Aktív szavazók száma
    $stmt = $pdo->query("SELECT COUNT(DISTINCT uid) FROM vote");
    $activeVoters = (int)$stmt->fetchColumn();

    echo json_encode([
        "questions" => $totalQuestions,
        "answers" => $totalAnswers,
        "votes" => $totalVotes,
        "users" => $totalUsers,
        "active_voters" => $activeVoters
    ]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Nem támogatott metódus"]);