<?php

declare(strict_types=1);

namespace Luntian;

use RuntimeException;

final class PaymentProofStorage
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Stores a validated uploaded image and returns an opaque database path.
     */
    public static function store(string $temporaryPath, string $mime, string $extension): string
    {
        if (!in_array($mime, self::ALLOWED_MIME, true) || !in_array($extension, ['jpg', 'png', 'webp'], true)) {
            throw new RuntimeException('Unsupported payment proof format.');
        }

        $filename = bin2hex(random_bytes(24)) . '.' . $extension;
        $driver = strtolower((string) env('PAYMENT_STORAGE_DRIVER', 'local'));

        if ($driver === 'supabase') {
            $config = self::supabaseConfig();
            $objectPath = 'payment-proofs/' . $filename;
            $bytes = file_get_contents($temporaryPath);
            if (!is_string($bytes)) {
                throw new RuntimeException('Could not read payment proof upload.');
            }

            $url = self::supabaseObjectUrl($config, $objectPath);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $config['key'],
                    'apikey: ' . $config['key'],
                    'Content-Type: ' . $mime,
                    'x-upsert: false',
                ],
                CURLOPT_POSTFIELDS => $bytes,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 45,
            ]);
            $response = curl_exec($ch);
            $errno = curl_errno($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($errno !== 0 || $response === false || $status < 200 || $status >= 300) {
                error_log('Private payment proof upload failed (HTTP ' . $status . ', curl ' . $errno . ').');
                throw new RuntimeException('Private payment storage is temporarily unavailable.');
            }

            return 'supabase:' . $objectPath;
        }

        if ($driver !== 'local') {
            throw new RuntimeException('Unsupported payment storage driver.');
        }

        // Vercel function filesystems are ephemeral/read-only for durable user uploads.
        if (env('VERCEL') === '1' || getenv('VERCEL') === '1') {
            throw new RuntimeException('Configure Supabase private storage before accepting payment proofs on Vercel.');
        }

        $directory = dirname(__DIR__) . '/storage/payment-proofs';
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create private proof storage.');
        }
        @chmod($directory, 0700);

        $destination = $directory . '/' . $filename;
        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Could not save payment proof.');
        }
        @chmod($destination, 0600);

        return 'local:' . $filename;
    }

    /**
     * @return array{bytes:string,mime:string,extension:string}
     */
    public static function read(string $storedPath): array
    {
        if (str_starts_with($storedPath, 'supabase:')) {
            $objectPath = substr($storedPath, strlen('supabase:'));
            if (!preg_match('#^payment-proofs/[a-f0-9]{48}\.(?:jpg|png|webp)$#', $objectPath)) {
                throw new RuntimeException('Payment proof path is invalid.');
            }

            $config = self::supabaseConfig();
            $ch = curl_init(self::supabaseObjectUrl($config, $objectPath));
            curl_setopt_array($ch, [
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $config['key'],
                    'apikey: ' . $config['key'],
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
            ]);
            $bytes = curl_exec($ch);
            $errno = curl_errno($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $mime = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($errno !== 0 || $bytes === false || $status < 200 || $status >= 300) {
                error_log('Private payment proof retrieval failed (HTTP ' . $status . ', curl ' . $errno . ').');
                throw new RuntimeException('Payment proof is unavailable.');
            }

            $mime = strtolower(trim(explode(';', $mime)[0]));
            if (!in_array($mime, self::ALLOWED_MIME, true)) {
                throw new RuntimeException('Unsupported payment proof content type.');
            }

            return [
                'bytes' => $bytes,
                'mime' => $mime,
                'extension' => strtolower(pathinfo($objectPath, PATHINFO_EXTENSION)),
            ];
        }

        $filename = '';
        if (str_starts_with($storedPath, 'local:')) {
            $filename = substr($storedPath, strlen('local:'));
        } else {
            // Backward compatibility for payment records saved before private paths were introduced.
            $filename = basename(str_replace('\\', '/', $storedPath));
        }

        if (!preg_match('/^[A-Za-z0-9._-]+\.(?:jpg|jpeg|png|webp)$/i', $filename)) {
            throw new RuntimeException('Payment proof path is invalid.');
        }

        $root = dirname(__DIR__);
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
            throw new RuntimeException('Payment proof file is unavailable.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        if (!is_string($mime) || !in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('Unsupported payment proof content type.');
        }
        $bytes = file_get_contents($file);
        if (!is_string($bytes)) {
            throw new RuntimeException('Payment proof file could not be read.');
        }

        return [
            'bytes' => $bytes,
            'mime' => $mime,
            'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
        ];
    }

    public static function delete(string $storedPath): void
    {
        if (str_starts_with($storedPath, 'supabase:')) {
            $objectPath = substr($storedPath, strlen('supabase:'));
            if (!preg_match('#^payment-proofs/[a-f0-9]{48}\.(?:jpg|png|webp)$#', $objectPath)) {
                return;
            }
            try {
                $config = self::supabaseConfig();
                $ch = curl_init(self::supabaseObjectUrl($config, $objectPath));
                curl_setopt_array($ch, [
                    CURLOPT_CUSTOMREQUEST => 'DELETE',
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $config['key'],
                        'apikey: ' . $config['key'],
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT => 15,
                ]);
                curl_exec($ch);
                curl_close($ch);
            } catch (\Throwable $e) {
                error_log('Could not clean up a failed payment proof upload.');
            }
            return;
        }

        if (str_starts_with($storedPath, 'local:')) {
            $filename = substr($storedPath, strlen('local:'));
            if (preg_match('/^[a-f0-9]{48}\.(?:jpg|png|webp)$/', $filename)) {
                $file = dirname(__DIR__) . '/storage/payment-proofs/' . $filename;
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    private static function supabaseConfig(): array
    {
        $url = rtrim((string) env('SUPABASE_URL', ''), '/');
        $key = (string) env('SUPABASE_SERVICE_ROLE_KEY', '');
        $bucket = (string) env('SUPABASE_PAYMENT_BUCKET', 'payment-proofs');

        if ($url === '' || $key === '' || !filter_var($url, FILTER_VALIDATE_URL) || !preg_match('/^[A-Za-z0-9_-]+$/', $bucket)) {
            throw new RuntimeException('Private Supabase Storage is not configured.');
        }

        return ['url' => $url, 'key' => $key, 'bucket' => $bucket];
    }

    private static function supabaseObjectUrl(array $config, string $objectPath): string
    {
        $segments = array_map('rawurlencode', explode('/', $objectPath));
        return $config['url'] . '/storage/v1/object/' . rawurlencode($config['bucket']) . '/' . implode('/', $segments);
    }
}
