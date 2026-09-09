<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$origin = getenv('FRONTEND_ORIGIN') ?: '*';

header("Access-Control-Allow-Origin: {$origin}");
header('Vary: Origin');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';


function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}


function inputJson(): array
{
    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    return is_array($data) ? $data : [];
}


function bearerToken(): ?string
{

    $header =
        $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';


    if ($header === '' && function_exists('getallheaders')) {
        $headers = getallheaders();

        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                $header = $value;
                break;
            }
        }
    }

    if ($header === '') {
        return null;
    }

    if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
        return trim($matches[1]);
    }

    return null;
}


function currentAccount(): ?array
{
    $token = bearerToken();

    if (!$token) {
        return null;
    }

    $tokenHash = hash('sha256', $token);

    $stmt = db()->prepare("
        SELECT
            a.id,
            a.username,
            a.email,
            a.full_name,
            a.role,
            a.status
        FROM account_tokens t
        INNER JOIN accounts a
            ON a.id = t.account_id
        WHERE t.token = ?
          AND t.expires_at > NOW()
        LIMIT 1
    ");

    $stmt->execute([
        $tokenHash
    ]);

    $account = $stmt->fetch();

    if (!$account) {
        return null;
    }

    if ($account['status'] !== 'active') {
        return null;
    }

    $account['id'] = (int)$account['id'];

    return $account;
}


function requireLogin(): array
{
    $account = currentAccount();

    if (!$account) {
        jsonResponse([
            'success' => false,
            'message' => 'Bạn chưa đăng nhập hoặc phiên đã hết hạn.'
        ], 401);
    }

    return $account;
}


function requireAdmin(): array
{
    $account = requireLogin();

    if ($account['role'] !== 'admin') {
        jsonResponse([
            'success' => false,
            'message' => 'Bạn không có quyền quản trị.'
        ], 403);
    }

    return $account;
}
