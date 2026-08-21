<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Database connection
| Render + Aiven MySQL
|--------------------------------------------------------------------------
*/

function db(): PDO
{
    static $pdo = null;

    // Nếu đã kết nối rồi thì dùng lại connection
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Lấy thông tin từ Render Environment Variables
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');

    /*
    |--------------------------------------------------------------------------
    | CA certificate
    |--------------------------------------------------------------------------
    | docker-entrypoint.sh copy:
    |
    | /etc/secrets/ca.pem
    |
    | thành:
    |
    | /tmp/aiven-ca.pem
    |--------------------------------------------------------------------------
    */

    $ca = '/tmp/aiven-ca.pem';

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra Environment Variables
    |--------------------------------------------------------------------------
    */

    $missing = [];

    if (!$host) {
        $missing[] = 'DB_HOST';
    }

    if (!$port) {
        $missing[] = 'DB_PORT';
    }

    if (!$name) {
        $missing[] = 'DB_NAME';
    }

    if (!$user) {
        $missing[] = 'DB_USER';
    }

    if (!$pass) {
        $missing[] = 'DB_PASS';
    }

    if (!empty($missing)) {
        throw new RuntimeException(
            'Missing database environment variables: ' .
            implode(', ', $missing)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra CA
    |--------------------------------------------------------------------------
    */

    if (!is_file($ca)) {
        throw new RuntimeException(
            'Aiven CA certificate not found: ' . $ca
        );
    }

    if (!is_readable($ca)) {
        throw new RuntimeException(
            'Aiven CA certificate is not readable: ' . $ca
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDO DSN
    |--------------------------------------------------------------------------
    */

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        (int)$port,
        $name
    );

    /*
    |--------------------------------------------------------------------------
    | PDO Options
    |--------------------------------------------------------------------------
    */

    $options = [
        PDO::ATTR_ERRMODE =>
            PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE =>
            PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES =>
            false,

        /*
        |--------------------------------------------------------------------------
        | Aiven SSL
        |--------------------------------------------------------------------------
        */

        PDO::MYSQL_ATTR_SSL_CA =>
            $ca,

        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT =>
            true,
    ];

    /*
    |--------------------------------------------------------------------------
    | Connect to Aiven
    |--------------------------------------------------------------------------
    */

    try {

        $pdo = new PDO(
            $dsn,
            $user,
            $pass,
            $options
        );

        // Test connection
        $pdo->query('SELECT 1');

        return $pdo;

    } catch (PDOException $e) {

        error_log(
            'Database connection failed: ' .
            $e->getMessage()
        );

        throw $e;
    }
}
