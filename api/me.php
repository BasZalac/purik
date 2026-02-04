<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['uid']) && isset($_SESSION['name'])) {
    echo json_encode([
        "uid" => $_SESSION['uid'],
        "name" => $_SESSION['name'],
        "role" => $_SESSION['role'] ?? 'user' // vagy amit használsz
    ]);
} else {
    http_response_code(401);
    echo json_encode(["error" => "Nincs bejelentkezve"]);
}