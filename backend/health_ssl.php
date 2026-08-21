<?php

header('Content-Type: application/json');

$ca = '/etc/secrets/ca.pem';

$result = [
    'php_version' => PHP_VERSION,
    'openssl' => defined('OPENSSL_VERSION_TEXT')
        ? OPENSSL_VERSION_TEXT
        : 'not available',
    'ca_exists' => file_exists($ca),
    'ca_readable' => is_readable($ca),
];

if (file_exists($ca)) {
    $result['ca_size'] = filesize($ca);

    $cert = openssl_x509_parse(
        file_get_contents($ca)
    );

    $result['ca_valid'] = $cert !== false;

    if ($cert !== false) {
        $result['ca_subject'] = $cert['subject']['CN'] ?? null;
        $result['ca_issuer'] = $cert['issuer']['CN'] ?? null;
        $result['ca_valid_from'] = date(
            'Y-m-d H:i:s',
            $cert['validFrom_time_t']
        );
        $result['ca_valid_to'] = date(
            'Y-m-d H:i:s',
            $cert['validTo_time_t']
        );
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);
