<?php

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'service' => 'PhoneShop PHP API',
    'message' => 'API is running'
]);
