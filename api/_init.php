<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

use Luntian\Response;

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'OPTIONS') {
    header('Access-Control-Allow-Origin: ' . cors_origin());
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Admin-Secret');
    header('Access-Control-Max-Age: 86400');
    http_response_code(204);
    exit;
}

header('Access-Control-Allow-Origin: ' . cors_origin());
header('Vary: Origin');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Admin-Secret');

function cors_origin(): string
{
    $configured = env('APP_URL', 'http://localhost/luntianAi');
    $configuredOrigin = parse_url($configured, PHP_URL_SCHEME) . '://' . parse_url($configured, PHP_URL_HOST);
    $configuredPort = parse_url($configured, PHP_URL_PORT);
    if ($configuredPort !== null) {
        $configuredOrigin .= ':' . $configuredPort;
    }

    $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
    if ($origin === '') {
        return $configuredOrigin;
    }

    $parts = parse_url($origin);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
        return $configuredOrigin;
    }
    $normalizedOrigin = strtolower($parts['scheme'] . '://' . $parts['host']);
    if (isset($parts['port'])) {
        $normalizedOrigin .= ':' . $parts['port'];
    }

    if (hash_equals(strtolower($configuredOrigin), $normalizedOrigin)) {
        return $origin;
    }

    if (env('APP_ENV') === 'local' && in_array($normalizedOrigin, [
        'http://localhost',
        'http://127.0.0.1',
    ], true)) {
        return $origin;
    }

    // Return the configured origin for unknown origins; never reflect arbitrary origins.
    return $configuredOrigin;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        Response::error('Invalid JSON body', 400);
    }
    return $data;
}

function require_auth_user(): array
{
    $firebase = \Luntian\FirebaseAuth::userFromRequest();
    if ($firebase === null) {
        Response::error('Sign in required', 401);
    }
    return \Luntian\UserRepository::upsertFromFirebase($firebase);
}

function require_admin(): void
{
    $secret = env('ADMIN_SECRET');
    $provided = $_SERVER['HTTP_X_ADMIN_SECRET'] ?? '';
    if ($secret === null || $secret === '' || !hash_equals($secret, $provided)) {
        Response::error('Forbidden', 403);
    }
}
