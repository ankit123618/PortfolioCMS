<?php
$envFile = __DIR__ . '/../.env';
$env = [];
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile) ?: [];
}

$env['DB_HOST'] = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? '127.0.0.1');
$env['DB_NAME'] = getenv('DB_NAME') ?: ($env['DB_NAME'] ?? 'portfolio');
$env['DB_USER'] = getenv('DB_USER') ?: ($env['DB_USER'] ?? 'ankit');
$env['DB_PASS'] = getenv('DB_PASS') ?: ($env['DB_PASS'] ?? '');

$pdo = new PDO(
    "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
    $env['DB_USER'],
    $env['DB_PASS'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
