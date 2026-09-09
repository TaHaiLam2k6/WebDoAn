<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Method không được hỗ trợ.'
    ], 405);
}

$data = inputJson();

$username = trim((string)($data['username'] ?? ''));
$email = trim(strtolower((string)($data['email'] ?? '')));
$fullName = trim((string)($data['full_name'] ?? ''));
$password = (string)($data['password'] ?? '');

if (!preg_match('/^[A-Za-z0-9_]{3,80}$/', $username)) {
    jsonResponse([
        'success' => false,
        'message' => 'Username phải dài 3-80 ký tự và chỉ gồm chữ, số, _.'
    ], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse([
        'success' => false,
        'message' => 'Email không hợp lệ.'
    ], 422);
}

if (strlen($password) < 6) {
    jsonResponse([
        'success' => false,
        'message' => 'Mật khẩu phải có ít nhất 6 ký tự.'
    ], 422);
}

if ($fullName === '') {
    $fullName = $username;
}

try {
    $pdo = db();

    $check = $pdo->prepare("
        SELECT id
        FROM accounts
        WHERE username = ?
           OR email = ?
        LIMIT 1
    ");

    $check->execute([
        $username,
        $email
    ]);

    if ($check->fetch()) {
        jsonResponse([
            'success' => false,
            'message' => 'Username hoặc email đã tồn tại.'
        ], 409);
    }


    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    if ($passwordHash === false) {
        throw new RuntimeException(
            'Không thể tạo password hash.'
        );
    }

    $stmt = $pdo->prepare("
        INSERT INTO accounts
        (
            username,
            email,
            password_hash,
            full_name,
            role,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            'user',
            'active'
        )
    ");

    $stmt->execute([
        $username,
        $email,
        $passwordHash,
        $fullName
    ]);

    $accountId = (int)$pdo->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Tạo tài khoản thành công. Bạn có thể đăng nhập ngay.',
        'account' => [
            'id' => $accountId,
            'username' => $username,
            'email' => $email,
            'full_name' => $fullName,
            'role' => 'user',
            'status' => 'active'
        ]
    ], 201);

} catch (PDOException $e) {

    error_log(
        'REGISTER PDO ERROR: ' .
        $e->getMessage()
    );

    jsonResponse([
        'success' => false,
        'message' => 'Lỗi database.',
        'error' => $e->getMessage(),
        'sql_state' => $e->getCode()
    ], 500);

} catch (Throwable $e) {

    error_log(
        'REGISTER ERROR: ' .
        $e->getMessage()
    );

    jsonResponse([
        'success' => false,
        'message' => 'Lỗi máy chủ.',
        'error' => $e->getMessage()
    ], 500);
}
