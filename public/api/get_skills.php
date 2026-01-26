<?php
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT name FROM skills ORDER BY name ASC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
