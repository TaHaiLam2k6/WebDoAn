<?php

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {

    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập tài khoản và mật khẩu.'
    ]);

    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            username,
            email,
            password_hash,
            full_name,
            role,
            status
        FROM accounts
        WHERE username = :username
        LIMIT 1
    ");

    $stmt->execute([
        ':username' => $username
    ]);

    $account = $stmt->fetch();

    if (!$account) {

        echo json_encode([
            'success' => false,
            'message' => 'Tài khoản hoặc mật khẩu không đúng.'
        ]);

        exit;
    }

    if ($account['status'] !== 'active') {

        echo json_encode([
            'success' => false,
            'message' => 'Tài khoản đã bị khóa.'
        ]);

        exit;
    }


    if (!password_verify($password, $account['password_hash'])) {

        echo json_encode([
            'success' => false,
            'message' => 'Tài khoản hoặc mật khẩu không đúng.'
        ]);

        exit;
    }


    unset($account['password_hash']);

    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'user' => $account
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Lỗi máy chủ hoặc cơ sở dữ liệu.'
    ]);
}
