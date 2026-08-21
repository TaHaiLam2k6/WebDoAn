<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/config/db.php';
    $version = db()->query('SELECT VERSION() AS version')->fetch()['version'];

    echo json_encode([
        'success' => true,
        'service' => 'PhoneShop PHP API',
        'database' => 'connected',
        'mysql_version' => $version
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'database' => 'connection failed'
    ]);
}
