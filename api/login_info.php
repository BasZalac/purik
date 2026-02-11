<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require __DIR__ . '/../db.php';

$users = [];
$result = $conn->query("SELECT name FROM user ORDER BY uid DESC LIMIT 8");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row['name'];
    }
}

echo json_encode([
    'title' => 'Szavazó Rendszer',
    'subtitle' => 'Belépés az adatbázisban tárolt felhasználókkal',
    'knownUsers' => $users,
]);
