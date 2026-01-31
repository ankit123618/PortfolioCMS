<?php
// public/api/get_content.php

declare(strict_types=1);

require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->prepare("
        SELECT
            header,
            tag,
            photo,
            navigation_links,
            about,
            vision,
            email,
            github,
            youtube,
            footer
        FROM site_content
        WHERE id = 1
        LIMIT 1
    ");

    $stmt->execute();
    $content = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$content) {
        echo json_encode(new stdClass()); // {}
        exit;
    }

    echo json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API failure',
        'message' => $e->getMessage()
    ]);
    exit;
}
