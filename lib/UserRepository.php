<?php

declare(strict_types=1);

namespace Luntian;

final class UserRepository
{
    /** @param array{uid: string, email: ?string, name: ?string, picture: ?string, provider: string} $firebaseUser */
    public static function upsertFromFirebase(array $firebaseUser): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO users (firebase_uid, email, display_name, photo_url, auth_provider, updated_at)
             VALUES (:uid, :email, :name, :picture, :provider, NOW())
             ON CONFLICT (firebase_uid) DO UPDATE SET
               email = EXCLUDED.email,
               display_name = EXCLUDED.display_name,
               photo_url = EXCLUDED.photo_url,
               auth_provider = EXCLUDED.auth_provider,
               updated_at = NOW()
             RETURNING id, firebase_uid, email, display_name, photo_url, plan, plan_expires_at'
        );
        $stmt->execute([
            'uid' => $firebaseUser['uid'],
            'email' => $firebaseUser['email'],
            'name' => $firebaseUser['name'],
            'picture' => $firebaseUser['picture'],
            'provider' => $firebaseUser['provider'],
        ]);
        return $stmt->fetch() ?: [];
    }

    public static function findByFirebaseUid(string $uid): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT id, firebase_uid, email, display_name, photo_url, plan, plan_expires_at
             FROM users WHERE firebase_uid = :uid LIMIT 1'
        );
        $stmt->execute(['uid' => $uid]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function activatePro(int $userId, string $billingCycle): void
    {
        if (!in_array($billingCycle, ['monthly', 'yearly'], true)) {
            throw new \InvalidArgumentException('Unsupported billing cycle.');
        }

        // Renewal adds time to the later of now or the current expiry, so approving
        // a renewal early does not discard unused subscription time.
        $interval = $billingCycle === 'yearly' ? "1 year" : "1 month";
        $sql = "UPDATE users
                SET plan = 'pro',
                    plan_expires_at = GREATEST(COALESCE(plan_expires_at, NOW()), NOW()) + INTERVAL '{$interval}',
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $userId]);
        if ($stmt->rowCount() !== 1) {
            throw new \RuntimeException('Subscription user could not be updated.');
        }
    }

    public static function isPro(?array $user): bool
    {
        if ($user === null || ($user['plan'] ?? 'free') !== 'pro') {
            return false;
        }
        if (empty($user['plan_expires_at'])) {
            return true;
        }
        $expiresAt = strtotime((string) $user['plan_expires_at']);
        return $expiresAt !== false && $expiresAt > time();
    }
}
