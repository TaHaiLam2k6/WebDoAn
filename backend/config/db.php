<?php

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| Aiven MySQL Configuration
|--------------------------------------------------------------------------
| Các giá trị được lấy từ Environment Variables trên Render.
|
| DB_HOST = hostname Aiven
| DB_PORT = port Aiven
| DB_NAME = tên database
| DB_USER = avnadmin
| DB_PASS = password Aiven
|--------------------------------------------------------------------------
*/

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

/*
|--------------------------------------------------------------------------
| CA Certificate
|--------------------------------------------------------------------------
| docker-entrypoint.sh sẽ copy:
|
| /etc/secrets/ca.pem
|
| thành:
|
| /tmp/aiven-ca.pem
|
| để PHP/www-data có thể đọc certificate.
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

if (!$db) {
    $missing[] = 'DB_NAME';
}

if (!$user) {
    $missing[] = 'DB_USER';
}

if (!$pass) {
    $missing[] = 'DB_PASS';
}

if (!empty($missing)) {

    error_log(
        'Missing database environment variables: ' .
        implode(', ', $missing)
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'database' => 'missing environment variables'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Kiểm tra CA Certificate
|--------------------------------------------------------------------------
*/

if (!file_exists($ca)) {

    error_log(
        'Aiven CA certificate does not exist: ' . $ca
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'database' => 'CA certificate not found'
    ]);

    exit;
}

if (!is_readable($ca)) {

    error_log(
        'Aiven CA certificate is not readable: ' . $ca
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'database' => 'CA certificate not readable'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Kiểm tra CA Certificate có hợp lệ không
|--------------------------------------------------------------------------
*/

$certificate = @openssl_x509_parse(
    file_get_contents($ca)
);

if ($certificate === false) {

    error_log(
        'Invalid Aiven CA certificate: ' . $ca
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'database' => 'invalid CA certificate'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Tạo PDO DSN
|--------------------------------------------------------------------------
*/

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $host,
    (int)$port,
    $db
);

/*
|--------------------------------------------------------------------------
| PDO Options
|--------------------------------------------------------------------------
*/

$options = [

    // Hiển thị lỗi bằng Exception
    PDO::ATTR_ERRMODE =>
        PDO::ERRMODE_EXCEPTION,

    // Trả kết quả dạng associative array
    PDO::ATTR_DEFAULT_FETCH_MODE =>
        PDO::FETCH_ASSOC,

    // Sử dụng native prepared statements
    PDO::ATTR_EMULATE_PREPARES =>
        false,

    /*
    |--------------------------------------------------------------
    | Aiven SSL
    |--------------------------------------------------------------
    */

    PDO::MYSQL_ATTR_SSL_CA =>
        $ca,

    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT =>
        true
];

/*
|--------------------------------------------------------------------------
| Kết nối Aiven MySQL
|--------------------------------------------------------------------------
*/

try {

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        $options
    );

    /*
    |--------------------------------------------------------------------------
    | Test connection
    |--------------------------------------------------------------------------
    */

    $pdo->query('SELECT 1');

} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Ghi lỗi thật vào Render Logs
    |--------------------------------------------------------------------------
    */

    error_log(
        'Database connection failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    /*
    |--------------------------------------------------------------------------
    | Không trả password/host ra browser
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        'success' => false,
        'database' => 'connection failed'
    ]);

    exit;
}
