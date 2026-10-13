<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/_init.php';

use Luntian\Database;
use Luntian\Response;
use Luntian\UserRepository;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST, OPTIONS');
    Response::error('Method not allowed', 405);
}

$user = require_auth_user();
$body = read_json_body();
$cycle = $body['billingCycle'] ?? 'monthly';

if (!in_array($cycle, ['monthly', 'yearly'], true)) {
    Response::error('billingCycle must be monthly or yearly', 400);
}

if (UserRepository::isPro($user)) {
    Response::error('Your Pro subscription is already active.', 409);
}

$amount = $cycle === 'yearly'
    ? (float) (env('GCASH_PRO_YEARLY_PHP', '14390') ?? '14390')
    : (float) (env('GCASH_PRO_MONTHLY_PHP', '1499') ?? '1499');

if (!is_finite($amount) || $amount <= 0) {
    error_log('Invalid configured GCash subscription price.');
    Response::error('Subscription pricing is temporarily unavailable.', 503);
}

$pdo = Database::connection();
$pdo->beginTransaction();
try {
    // Serialize order creation per user and billing cycle, including when no row exists yet.
    $cycleLock = $cycle === 'yearly' ? 2 : 1;
    $lock = $pdo->prepare('SELECT pg_advisory_xact_lock(:uid, :cycle)');
    $lock->execute(['uid' => (int) $user['id'], 'cycle' => $cycleLock]);

    // Reuse an existing unfinished order instead of creating duplicate pending orders.
    $existing = $pdo->prepare(
        "SELECT id, reference_code, amount_php, billing_cycle, status, created_at
         FROM payments
         WHERE user_id = :uid AND billing_cycle = :cycle AND status IN ('pending', 'submitted')
         ORDER BY created_at DESC
         LIMIT 1
         FOR UPDATE"
    );
    $existing->execute(['uid' => (int) $user['id'], 'cycle' => $cycle]);
    $payment = $existing->fetch();

    if (!$payment) {
        $reference = 'LTN-' . strtoupper(bin2hex(random_bytes(8)));
        $stmt = $pdo->prepare(
            'INSERT INTO payments (user_id, reference_code, amount_php, billing_cycle, status)
             VALUES (:uid, :ref, :amount, :cycle, :status)
             RETURNING id, reference_code, amount_php, billing_cycle, status, created_at'
        );
        $stmt->execute([
            'uid' => (int) $user['id'],
            'ref' => $reference,
            'amount' => number_format($amount, 2, '.', ''),
            'cycle' => $cycle,
            'status' => 'pending',
        ]);
        $payment = $stmt->fetch();
    }

    $pdo->commit();
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Payment order creation failed: ' . $e->getMessage());
    Response::error('Could not create a payment order. Please try again.', 500);
}

Response::json([
    'ok' => true,
    'payment' => $payment,
    'paymentPageUrl' => app_url() . '/payment-gcashPage.php?ref=' . urlencode((string) $payment['reference_code']),
]);
