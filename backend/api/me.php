<?php

require_once __DIR__ . '/../config/bootstrap.php';

$headers = function_exists('getallheaders')
    ? getallheaders()
    : [];

jsonResponse([
    'success' => true,
    'authorization_server' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
    'authorization_redirect' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null,
    'headers' => $headers
]);
