<?php

header('Content-Type: application/json');

require_once __DIR__ . '/config/db.php';

try {
    $stmt = $pdo->query("SELECT VERSION() AS version");

    $row = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'database' => 'connected',
        'mysql_version' => $row['version']
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'database' => 'query failed'
    ]);
}
