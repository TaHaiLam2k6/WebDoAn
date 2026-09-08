<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$caFile = '/etc/secrets/ca.pem';

try {

    if (!$host || !$db || !$user || !$pass) {
        throw new Exception('Thiếu biến môi trường database.');
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 10
    ];

    if (file_exists($caFile) && is_readable($caFile)) {

        $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;

    } else {

        error_log("CA certificate không tồn tại hoặc không có quyền đọc: " . $caFile);

    }

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (Throwable $e) {

    error_log("Database connection failed: " . $e->getMessage());

    $pdo = null;
}
