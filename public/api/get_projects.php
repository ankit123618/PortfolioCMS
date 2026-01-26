<?php
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT id, title, description, image FROM projects ORDER BY id DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
