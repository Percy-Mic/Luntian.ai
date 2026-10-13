<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/_init.php';

use Luntian\Database;
use Luntian\PaymentProofStorage;
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

$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
if (!is_string($mime) || !isset($extensions[$mime])) {
    Response::error('Proof must be a valid JPG, PNG, or WebP image.', 400);
}
$imageInfo = @getimagesize($file['tmp_name']);
if ($imageInfo === false || !isset($imageInfo['mime']) || $imageInfo['mime'] !== $mime) {
    Response::error('The uploaded file is not a valid image.', 400);
}

$pdo = Database::connection();
$pdo->beginTransaction();
$storedPath = null;

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

    $storedPath = PaymentProofStorage::store($file['tmp_name'], $mime, $extensions[$mime]);
    $update = $pdo->prepare(
        "UPDATE payments
         SET status = 'submitted', gcash_reference = :gref, proof_path = :path, updated_at = NOW()
         WHERE id = :id AND user_id = :uid"
    );
    $update->execute([
        'gref' => $gcashRef,
        'path' => $storedPath,
        'id' => (int) $payment['id'],
        'uid' => (int) $user['id'],
    ]);
    if ($update->rowCount() !== 1) {
        throw new \RuntimeException('Payment record could not be updated.');
    }

    $pdo->commit();
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($storedPath !== null) {
        PaymentProofStorage::delete($storedPath);
    }
    error_log('Payment proof submission failed: ' . $e->getMessage());
    $storageUnavailable = str_contains($e->getMessage(), 'Configure Supabase private storage')
        || str_contains($e->getMessage(), 'Private Supabase Storage is not configured')
        || str_contains($e->getMessage(), 'Private payment storage is temporarily unavailable');
    Response::error(
        $storageUnavailable
            ? 'Payment proof storage is not configured or is temporarily unavailable.'
            : 'Could not submit the payment proof. Please try again.',
        $storageUnavailable ? 503 : 500
    );
}

Response::json([
    'ok' => true,
    'message' => 'Payment proof submitted. We will verify it manually before activating Pro.',
    'status' => 'submitted',
]);
