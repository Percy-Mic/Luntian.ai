<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/_init.php';

use Luntian\Database;
use Luntian\Response;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST, OPTIONS');
    Response::error('Method not allowed', 405);
}

$user = require_auth_user();
$reference = trim((string) ($_POST['reference_code'] ?? ''));
$gcashRef = trim((string) ($_POST['gcash_reference'] ?? ''));

if (!preg_match('/^LTN-[A-F0-9]{16}$/', $reference)) {
    Response::error('A valid order reference is required.', 400);
}
if ($gcashRef === '' || mb_strlen($gcashRef) > 64 || !preg_match('/^[A-Za-z0-9\- ]+$/', $gcashRef)) {
    Response::error('Enter a valid GCash reference number.', 400);
}

if (!isset($_FILES['proof']) || !is_array($_FILES['proof']) || ($_FILES['proof']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    Response::error('Payment screenshot (proof) is required.', 400);
}

$file = $_FILES['proof'];
if (!is_uploaded_file($file['tmp_name'] ?? '') || (int) ($file['size'] ?? 0) < 1) {
    Response::error('The uploaded file is invalid.', 400);
}
if ((int) $file['size'] > 5 * 1024 * 1024) {
    Response::error('Proof file must be 5 MB or smaller.', 400);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
if ($finfo === false) {
    Response::error('File validation is unavailable on this server.', 503);
}
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
if (!is_string($mime) || !isset($allowed[$mime])) {
    Response::error('Proof must be a valid JPG, PNG, or WebP image.', 400);
}

$imageInfo = @getimagesize($file['tmp_name']);
if ($imageInfo === false || !isset($imageInfo['mime']) || $imageInfo['mime'] !== $mime) {
    Response::error('The uploaded file is not a valid image.', 400);
}

$pdo = Database::connection();
$pdo->beginTransaction();
$dest = null;

try {
    $stmt = $pdo->prepare(
        'SELECT id, status FROM payments
         WHERE reference_code = :ref AND user_id = :uid
         FOR UPDATE'
    );
    $stmt->execute(['ref' => $reference, 'uid' => (int) $user['id']]);
    $payment = $stmt->fetch();

    if (!$payment) {
        $pdo->rollBack();
        Response::error('Payment not found.', 404);
    }
    if (!in_array($payment['status'], ['pending', 'submitted'], true)) {
        $pdo->rollBack();
        Response::error('This payment can no longer be updated.', 409);
    }

    $uploadDir = dirname(__DIR__, 2) . '/storage/payment-proofs';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0700, true) && !is_dir($uploadDir)) {
        throw new \RuntimeException('Could not create private proof storage directory.');
    }
    @chmod($uploadDir, 0700);

    $filename = bin2hex(random_bytes(24)) . '.' . $allowed[$mime];
    $dest = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new \RuntimeException('Could not move uploaded payment proof.');
    }
    @chmod($dest, 0600);

    $relativePath = 'storage/payment-proofs/' . $filename;
    $update = $pdo->prepare(
        "UPDATE payments
         SET status = 'submitted', gcash_reference = :gref, proof_path = :path, updated_at = NOW()
         WHERE id = :id AND user_id = :uid"
    );
    $update->execute([
        'gref' => $gcashRef,
        'path' => $relativePath,
        'id' => (int) $payment['id'],
        'uid' => (int) $user['id'],
    ]);

    $pdo->commit();
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($dest !== null && is_file($dest)) {
        @unlink($dest);
    }
    error_log('Payment proof submission failed: ' . $e->getMessage());
    Response::error('Could not submit the payment proof. Please try again.', 500);
}

Response::json([
    'ok' => true,
    'message' => 'Payment proof submitted. We will verify it manually before activating Pro.',
    'status' => 'submitted',
]);
