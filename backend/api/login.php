<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$data = inputJson();

$login = trim((string)($data['login'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($login === '' || $password === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Vui lòng nhập tài khoản và mật khẩu.'
    ], 400);
}

try {
    $pdo = db();


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
           OR email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':username' => $login,
        ':email' => $login
    ]);

    $account = $stmt->fetch();


    if (!$account) {
        jsonResponse([
            'success' => false,
            'message' => 'Tài khoản hoặc mật khẩu không đúng.'
        ], 401);
    }


    if ($account['status'] !== 'active') {
        jsonResponse([
            'success' => false,
            'message' => 'Tài khoản đã bị khóa.'
        ], 403);
    }


    if (!password_verify($password, $account['password_hash'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Tài khoản hoặc mật khẩu không đúng.'
        ], 401);
    }


    $token = bin2hex(random_bytes(32));


    $expiresAt = date(
        'Y-m-d H:i:s',
        time() + (7 * 24 * 60 * 60)
    );

    /*
     * Xóa token cũ của tài khoản
     */
    $delete = $pdo->prepare("
        DELETE FROM account_tokens
        WHERE account_id = ?
    ");

    $delete->execute([
        (int)$account['id']
    ]);


    $insert = $pdo->prepare("
        INSERT INTO account_tokens
            (account_id, token, expires_at)
        VALUES
            (?, ?, ?)
    ");

    $insert->execute([
        (int)$account['id'],
        $token,
        $expiresAt
    ]);


    unset($account['password_hash']);


    jsonResponse([
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'token' => $token,
        'account' => [
            'id' => (int)$account['id'],
            'username' => $account['username'],
            'email' => $account['email'],
            'full_name' => $account['full_name'],
            'role' => $account['role'],
            'status' => $account['status']
        ]
    ], 200);

} catch (Throwable $e) {


    error_log(
        'LOGIN ERROR: ' . $e->getMessage()
    );

    jsonResponse([
        'success' => false,
        'message' => 'Lỗi máy chủ hoặc cơ sở dữ liệu.'
    ], 500);
}
