<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/_init.php';

use Luntian\Database;
use Luntian\PaymentProofStorage;

require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET, OPTIONS');
    exit;
}

$paymentId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$paymentId || $paymentId < 1) {
    http_response_code(400);
    exit('Invalid payment id');
}

$stmt = Database::connection()->prepare('SELECT proof_path FROM payments WHERE id = :id');
$stmt->execute(['id' => $paymentId]);
$storedPath = $stmt->fetchColumn();
if (!is_string($storedPath) || $storedPath === '') {
    http_response_code(404);
    exit('Payment proof not found');
}

try {
    $proof = PaymentProofStorage::read($storedPath);
} catch (\Throwable $e) {
    error_log('Admin payment proof retrieval failed: ' . $e->getMessage());
    http_response_code(404);
    header('Cache-Control: no-store');
    exit('Payment proof file is unavailable');
}

header('Content-Type: ' . $proof['mime']);
header('Content-Length: ' . (string) strlen($proof['bytes']));
header('Content-Disposition: inline; filename="payment-proof.' . $proof['extension'] . '"');
header('Cache-Control: no-store, private, max-age=0');
header('X-Content-Type-Options: nosniff');
echo $proof['bytes'];
exit;
