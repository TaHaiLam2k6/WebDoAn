<?php

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/config/db.php';

    $pdo = db();

    $version = $pdo
        ->query('SELECT VERSION() AS version')
        ->fetch();

    $tables = $pdo
        ->query('SHOW TABLES')
        ->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'database' => getenv('DB_NAME'),
        'mysql_version' => $version['version'],
        'tables' => $tables
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    http_response_code(500);

    error_log($e->getMessage());

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
