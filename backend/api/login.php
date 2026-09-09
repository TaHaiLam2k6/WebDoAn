<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

$input = inputJson();

$login = trim((string)($input['login'] ?? ''));
$password = (string)($input['password'] ?? '');

if ($login === '' || $password === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Vui lòng nhập tài khoản và mật khẩu.'
    ], 400);
}

try {
    $stmt = db()->prepare("
        SELECT
            id,
            username,
            email,
            password_hash,
            full_name,
            role,
            status
        FROM accounts
        WHERE username = :login
           OR email = :login
        LIMIT 1
    ");

    $stmt->execute([
        ':login' => $login
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

    // Tạo token đăng nhập
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    // Token hết hạn sau 7 ngày
    $expiresAt = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60);

    $tokenStmt = db()->prepare("
        INSERT INTO account_tokens
        (
            account_id,
            token_hash,
            expires_at
        )
        VALUES
        (
            :account_id,
            :token_hash,
            :expires_at
        )
    ");

    $tokenStmt->execute([
        ':account_id' => $account['id'],
        ':token_hash' => $tokenHash,
        ':expires_at' => $expiresAt
    ]);

    unset($account['password_hash']);

    jsonResponse([
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'token' => $token,
        'account' => $account
    ]);

} catch (Throwable $e) {

    error_log($e->getMessage());

    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}
