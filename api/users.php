<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

if($_SERVER['REQUEST_METHOD'] === 'GET'){
	$stmt = $pdo->query("SELECT id, name FROM user ORDER BY name ASC");
	echo json_encode($stmt->fetchAll());
	exit;
}

http_response_code(405);
echo json_encode(["error" => "Nem támogatott metódus"]);
