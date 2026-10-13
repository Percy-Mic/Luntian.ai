<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/_init.php';

use Luntian\Database;

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
$path = $stmt->fetchColumn();
if (!is_string($path) || $path === '') {
    http_response_code(404);
    exit('Payment proof not found');
}

$root = dirname(__DIR__, 2);
$filename = basename(str_replace('\\', '/', $path));
if ($filename === '.' || $filename === '..' || !preg_match('/^[A-Za-z0-9._-]+\.(?:jpg|jpeg|png|webp)$/i', $filename)) {
    http_response_code(404);
    exit('Payment proof not found');
}

// New uploads are stored outside the web root. This also allows older records
// created before the private-storage change to be reviewed through this endpoint.
$candidates = [
    $root . '/storage/payment-proofs/' . $filename,
    $root . '/public/uploads/payment-proofs/' . $filename,
];
$file = null;
foreach ($candidates as $candidate) {
    $real = realpath($candidate);
    if ($real !== false && is_file($real)) {
        $file = $real;
        break;
    }
}
if ($file === null) {
    http_response_code(404);
    exit('Payment proof file is unavailable');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo ? finfo_file($finfo, $file) : false;
if ($finfo) {
    finfo_close($finfo);
}
$allowed = ['image/jpeg', 'image/png', 'image/webp'];
if (!is_string($mime) || !in_array($mime, $allowed, true)) {
    http_response_code(415);
    exit('Unsupported proof format');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($file));
header('Content-Disposition: inline; filename="payment-proof.' . strtolower(pathinfo($file, PATHINFO_EXTENSION)) . '"');
header('Cache-Control: no-store, private, max-age=0');
header('X-Content-Type-Options: nosniff');
readfile($file);
exit;
