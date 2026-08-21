<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {

    require_once __DIR__ . '/config/db.php';

    $pdo = db();

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'tables' => $tables
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
