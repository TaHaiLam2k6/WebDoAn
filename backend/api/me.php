<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

$account = requireLogin();

jsonResponse([
    'success' => true,
    'account' => $account
]);
