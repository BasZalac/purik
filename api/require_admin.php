<?php
require __DIR__ . '/require_login.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Admin jogosultság szükséges."]);
    exit;
}