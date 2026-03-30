<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$env = parse_ini_file(__DIR__ . '/../.env');
// var_dump($env);


$pdo = new PDO(
"mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
$env['DB_USER'],
$env['DB_PASS'],
[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);