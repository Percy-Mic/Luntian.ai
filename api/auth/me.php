<?php

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

use Luntian\Response;
use Luntian\UserRepository;

$user = require_auth_user();

Response::json([
    'ok' => true,
    'user' => [
        'id' => (int) $user['id'],
        'email' => $user['email'],
        'displayName' => $user['display_name'],
        'photoUrl' => $user['photo_url'],
        'plan' => $user['plan'],
        'planExpiresAt' => $user['plan_expires_at'],
        'isPro' => UserRepository::isPro($user),
    ],
]);
