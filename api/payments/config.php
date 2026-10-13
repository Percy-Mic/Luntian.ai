<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/_init.php';

use Luntian\Response;

Response::json([
    'ok' => true,
    'gcash' => [
        'accountName' => env('GCASH_ACCOUNT_NAME', ''),
        'mobile' => env('GCASH_MOBILE', ''),
        'qrImageUrl' => app_url() . '/public/assets/images/gcash-qr.png',
        'monthlyPhp' => (float) (env('GCASH_PRO_MONTHLY_PHP', '1499') ?? '1499'),
        'yearlyPhp' => (float) (env('GCASH_PRO_YEARLY_PHP', '14390') ?? '14390'),
    ],
    'note' => 'Manual GCash: pay via QR, then submit your reference number and screenshot for approval.',
]);
