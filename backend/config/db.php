<?php

header('Content-Type: application/json');

$host = getenv('DB_HOST');
$port = (int)(getenv('DB_PORT') ?: 3306);
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

$ca = '/etc/secrets/ca.pem';

if (!$host || !$port || !$db || !$user || !$pass) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'database' => 'missing environment variables'
    ]);

    exit;
}

if (!is_file($ca) || !is_readable($ca)) {
    http_response_code(500);

    error_log("CA certificate unavailable: {$ca}");

    echo json_encode([
        'success' => false,
        'database' => 'CA certificate unavailable'
    ]);

    exit;
}

try {

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $db
    );

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,

            PDO::MYSQL_ATTR_SSL_CA => $ca,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
        ]
    );

    $pdo->query('SELECT 1');

} catch (PDOException $e) {

    error_log(
        'Database connection failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'database' => 'connection failed'
    ]);

    exit;
}
