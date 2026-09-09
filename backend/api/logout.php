<?php

require_once __DIR__ . '/../config/bootstrap.php';

$token = bearerToken();

if ($token) {

    $stmt = db()->prepare("
        DELETE FROM account_tokens
        WHERE token = :token
    ");

    $stmt->execute([
        ':token' => $token
    ]);
}

jsonResponse([
    'success' => true
]);
