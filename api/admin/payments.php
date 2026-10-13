<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/_init.php';

use Luntian\Database;
use Luntian\Response;
use Luntian\UserRepository;

require_admin();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pdo = Database::connection();

if ($method === 'GET') {
    $status = (string) ($_GET['status'] ?? 'submitted');
    $allowedStatuses = ['pending', 'submitted', 'approved', 'rejected', 'cancelled'];
    if (!in_array($status, $allowedStatuses, true)) {
        Response::error('Invalid payment status filter.', 400);
    }

    $stmt = $pdo->prepare(
        'SELECT p.id, p.reference_code, p.amount_php, p.billing_cycle, p.status,
                p.gcash_reference, p.proof_path, p.admin_note, p.created_at, p.updated_at, p.approved_at,
                u.email, u.display_name
         FROM payments p
         JOIN users u ON u.id = p.user_id
         WHERE p.status = :status
         ORDER BY p.updated_at DESC, p.id DESC
         LIMIT 100'
    );
    $stmt->execute(['status' => $status]);
    Response::json(['ok' => true, 'payments' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $body = read_json_body();
    $paymentId = filter_var($body['paymentId'] ?? null, FILTER_VALIDATE_INT);
    $action = $body['action'] ?? '';

    if (!$paymentId || $paymentId < 1 || !in_array($action, ['approve', 'reject'], true)) {
        Response::error('A valid paymentId and action (approve or reject) are required.', 400);
    }

    $note = trim((string) ($body['adminNote'] ?? ''));
    if (mb_strlen($note) > 1000) {
        Response::error('Rejection note must be 1,000 characters or fewer.', 400);
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $paymentId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            $pdo->rollBack();
            Response::error('Payment not found.', 404);
        }
        if ($payment['status'] !== 'submitted') {
            $pdo->rollBack();
            Response::error('Only submitted payments can be reviewed.', 409);
        }

        if ($action === 'approve') {
            $update = $pdo->prepare(
                "UPDATE payments SET status = 'approved', approved_at = NOW(), updated_at = NOW()
                 WHERE id = :id AND status = 'submitted'"
            );
            $update->execute(['id' => $paymentId]);
            if ($update->rowCount() !== 1) {
                throw new \RuntimeException('Payment status changed during review.');
            }

            UserRepository::activatePro((int) $payment['user_id'], (string) $payment['billing_cycle']);
        } else {
            $update = $pdo->prepare(
                "UPDATE payments SET status = 'rejected', admin_note = :note, updated_at = NOW()
                 WHERE id = :id AND status = 'submitted'"
            );
            $update->execute(['id' => $paymentId, 'note' => ($note !== '' ? $note : null)]);
            if ($update->rowCount() !== 1) {
                throw new \RuntimeException('Payment status changed during review.');
            }
        }

        $pdo->commit();
        Response::json(['ok' => true, 'status' => $action === 'approve' ? 'approved' : 'rejected']);
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin payment review failed: ' . $e->getMessage());
        Response::error('Could not process this payment. Refresh the queue and try again.', 500);
    }
}

header('Allow: GET, POST, OPTIONS');
Response::error('Method not allowed', 405);
