<?php
require __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

echo json_encode(
  $pdo->query("SELECT about, vision, email, github, youtube FROM site_content WHERE id=1")->fetch()
);
