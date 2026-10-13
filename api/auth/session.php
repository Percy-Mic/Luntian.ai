<?php

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

use Luntian\FirebaseAuth;
use Luntian\Response;
use Luntian\UserRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

$body = read_json_body();
$idToken = trim((string) ($body['idToken'] ?? ''));

if ($idToken === '') {
    Response::error('idToken is required', 400);
}

$firebaseUser = FirebaseAuth::verifyIdToken($idToken);
$user = UserRepository::upsertFromFirebase($firebaseUser);

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
