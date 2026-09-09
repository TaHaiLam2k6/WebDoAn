<?php

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config/db.php';

if (!$pdo) {
    http_response_code(500);
    echo "Không thể kết nối database.\n";
    exit;
}

// ===============================
// THÔNG TIN ADMIN
// ===============================

$username = 'admin';
$email    = 'admin@phoneshop.local';
$password = 'Admin@123';
$fullName = 'Administrator';
$role     = 'admin';
$status   = 'active';

// ===============================
// HASH PASSWORD
// ===============================

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

try {

    // Kiểm tra tài khoản đã tồn tại chưa
    $check = $pdo->prepare("
        SELECT id
        FROM accounts
        WHERE username = :username
        LIMIT 1
    ");

    $check->execute([
        ':username' => $username
    ]);

    $account = $check->fetch();

    // ===============================
    // NẾU ĐÃ CÓ ADMIN
    // ===============================

    if ($account) {

        $stmt = $pdo->prepare("
            UPDATE accounts
            SET
                email = :email,
                password_hash = :password_hash,
                full_name = :full_name,
                role = :role,
                status = :status
            WHERE username = :username
        ");

        $stmt->execute([
            ':email'         => $email,
            ':password_hash' => $passwordHash,
            ':full_name'     => $fullName,
            ':role'          => $role,
            ':status'        => $status,
            ':username'      => $username
        ]);

        echo "====================================\n";
        echo "ADMIN ĐÃ ĐƯỢC RESET\n";
        echo "====================================\n";
        echo "Username : $username\n";
        echo "Password : $password\n";
        echo "Role     : $role\n";
        echo "Status   : $status\n";
        echo "====================================\n";

    }

    // ===============================
    // NẾU CHƯA CÓ ADMIN
    // ===============================

    else {

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
                :username,
                :email,
                :password_hash,
                :full_name,
                :role,
                :status
            )
        ");

        $stmt->execute([
            ':username'      => $username,
            ':email'         => $email,
            ':password_hash' => $passwordHash,
            ':full_name'     => $fullName,
            ':role'          => $role,
            ':status'        => $status
        ]);

        echo "====================================\n";
        echo "TẠO ADMIN THÀNH CÔNG\n";
        echo "====================================\n";
        echo "Username : $username\n";
        echo "Password : $password\n";
        echo "Role     : $role\n";
        echo "Status   : $status\n";
        echo "====================================\n";
    }

} catch (PDOException $e) {

    http_response_code(500);

    echo "LỖI DATABASE\n";
    echo $e->getMessage();
}
