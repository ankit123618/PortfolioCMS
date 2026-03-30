<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$slug = $_GET['slug'] ?? 'home';

$stmt = $pdo->prepare("SELECT schema FROM pages WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode([]);
    exit;
}

echo $row['schema'];
exit;
