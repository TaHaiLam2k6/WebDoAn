<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    // Nạp hàm db()
    require_once __DIR__ . '/config/db.php';

    // Lấy PDO connection
    $pdo = db();

    // Test database
    $stmt = $pdo->query('SELECT 1 AS test');

    $result = $stmt->fetch();

    // Lấy phiên bản MySQL
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();

    echo json_encode([
        'success' => true,
        'database' => 'connected',
        'mysql_version' => $version,
        'test' => $result['test'] ?? null
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    error_log(
        'Health check database error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'database' => 'connection failed',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
