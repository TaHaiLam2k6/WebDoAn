<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';


    $pass = getenv('DB_PASSWORD');
    if ($pass === false || $pass === '') {
        $pass = getenv('DB_PASS') ?: '';
    }

    $ca = getenv('DB_SSL_CA') ?: '/etc/secrets/ca.pem';

    if ($host === '' || $name === '' || $user === '' || $pass === '') {
        throw new RuntimeException(
            'Thiếu biến môi trường DB_HOST/DB_NAME/DB_USER/DB_PASSWORD'
        );
    }

    if (!file_exists($ca)) {
        throw new RuntimeException(
            'Không tìm thấy CA certificate: ' . $ca
        );
    }

    if (!is_readable($ca)) {
        throw new RuntimeException(
            'CA certificate tồn tại nhưng PHP không đọc được: ' . $ca
        );
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_CA => $ca,
    ];

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        $options
    );

    $pdo->query('SELECT 1');

    return $pdo;
}

$pdo = db();
