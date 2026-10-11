<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Single-use password reset links.
 *
 * Only a SHA-256 of the token is stored, so a copy of the database cannot be
 * turned into working links. Links last an hour, die on use, and asking for a
 * new one cancels any older ones for that login.
 */
final class PasswordReset
{
    public const MINUTES = 60;

    /** Creates a link for a member login and returns the raw token, or null if there is no such member. */
    public static function issue(string $email): ?array
    {
        $user = Database::first(
            "SELECT u.id, u.email, u.first_name FROM users u
              WHERE u.email = :e AND u.status = 'active'
                AND EXISTS (SELECT 1 FROM account_users au WHERE au.user_id = u.id)
              LIMIT 1",
            ['e' => mb_strtolower(trim($email))],
        );
        if ($user === null) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        Database::run('UPDATE password_resets SET used_at = :now WHERE user_id = :u AND used_at IS NULL',
            ['now' => date('Y-m-d H:i:s'), 'u' => $user['id']]);
        Database::run(
            'INSERT INTO password_resets (user_id, token_hash, expires_at, ip, created_at) VALUES (:u, :h, :x, :ip, :now)',
            [
                'u' => $user['id'],
                'h' => hash('sha256', $token),
                'x' => date('Y-m-d H:i:s', time() + self::MINUTES * 60),
                'ip' => Request::ip(),
                'now' => date('Y-m-d H:i:s'),
            ],
        );

        return ['token' => $token, 'user' => $user];
    }

    /** The reset row for a still-valid token, or null. @return array<string,mixed>|null */
    public static function find(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return Database::first(
            'SELECT * FROM password_resets WHERE token_hash = :h AND used_at IS NULL AND expires_at > :now LIMIT 1',
            ['h' => hash('sha256', $token), 'now' => date('Y-m-d H:i:s')],
        );
    }

    public static function consume(int $resetId, int $userId): void
    {
        Database::run('UPDATE password_resets SET used_at = :now WHERE user_id = :u AND used_at IS NULL',
            ['now' => date('Y-m-d H:i:s'), 'u' => $userId]);
    }
}
