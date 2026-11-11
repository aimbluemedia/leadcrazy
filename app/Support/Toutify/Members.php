<?php

declare(strict_types=1);

namespace App\Support\Toutify;

use App\Support\Audit;
use App\Support\Database;
use App\Support\ErrorHandler;

/**
 * Members as Toutify sees them, and their LeadCrazy sessions.
 *
 * A member is a row in `users` with a toutify_sub, the permanent 26-character
 * Toutify member ID. Superadmins are rows without one and keep their LeadCrazy
 * password login.
 *
 * Every member session has a row in toutify_sessions. Auth checks that row on
 * each request, so deleting it (back-channel logout, suspension, deletion)
 * signs the member out on their next click, wherever the session is.
 */
final class Members
{
    /** Session rows older than this are cleared out; sessions are much shorter-lived. */
    private const SESSION_ROW_DAYS = 30;

    /* ------------------------------------------------------------ sign-in */

    /**
     * Finds the member for these UserInfo claims, linking or creating the row,
     * and copies Toutify's details onto it.
     *
     * Linking: a member imported into Toutify from LeadCrazy has a row here
     * with their email and no toutify_sub yet. The first Toutify sign-in links
     * the two, but only when Toutify says the email is verified, and never for
     * a staff login.
     *
     * @param array<string,mixed> $info  UserInfo claims
     * @return array{user:array<string,mixed>,created:bool}
     * @throws SignInRefused with a message fit to show the member
     */
    public static function fromClaims(array $info): array
    {
        $sub = (string) $info['sub'];
        $email = mb_strtolower(trim((string) ($info['email'] ?? '')));
        $verified = ($info['email_verified'] ?? false) === true || ($info['email_verified'] ?? null) === 'true';

        $user = Database::first('SELECT * FROM users WHERE toutify_sub = :s LIMIT 1', ['s' => $sub]);
        $created = false;

        if ($user === null) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new SignInRefused('Your Toutify account has no email address. Add one on Toutify, then sign in again.');
            }
            $existing = Database::first('SELECT * FROM users WHERE email = :e LIMIT 1', ['e' => $email]);
            if ($existing !== null) {
                if ((int) $existing['is_admin'] === 1) {
                    throw new SignInRefused('That email address belongs to a LeadCrazy staff login. Staff sign in at /superadmin/login.');
                }
                if ($existing['toutify_sub'] !== null && $existing['toutify_sub'] !== '') {
                    throw new SignInRefused('That email address is already linked to a different Toutify account. Please contact support.');
                }
                if (!$verified) {
                    throw new SignInRefused('Please verify your email address on Toutify first, then sign in again.');
                }
                Database::run('UPDATE users SET toutify_sub = :s WHERE id = :id', ['s' => $sub, 'id' => $existing['id']]);
                Audit::log('auth.toutify_linked', 'user', (int) $existing['id'], null, ['sub' => $sub]);
                $user = $existing;
            } else {
                Database::run(
                    "INSERT INTO users (email, password_hash, toutify_sub) VALUES (:e, '', :s)",
                    ['e' => $email, 's' => $sub],
                );
                $user = ['id' => (int) Database::connection()->lastInsertId()];
                $created = true;
                Audit::log('auth.toutify_member_created', 'user', (int) $user['id'], null, ['sub' => $sub]);
            }
        }

        self::copy((int) $user['id'], $info, true);
        return ['user' => Database::first('SELECT * FROM users WHERE id = :id', ['id' => $user['id']]) ?? $user, 'created' => $created];
    }

    /**
     * Copies Toutify's member details onto the row. $force skips the
     * updated_at ordering check (sign-in always takes the latest UserInfo).
     *
     * @param array<string,mixed> $m  claims: name, email, business_name, phone_number, account_status, updated_at
     * @return bool whether anything was applied
     */
    public static function copy(int $userId, array $m, bool $force = false): bool
    {
        $row = Database::first('SELECT id, email, business_name, toutify_updated_at FROM users WHERE id = :id', ['id' => $userId]);
        if ($row === null) {
            return false;
        }
        $updatedAt = isset($m['updated_at']) && is_numeric($m['updated_at']) ? (int) $m['updated_at'] : null;
        if (!$force && $updatedAt !== null && $row['toutify_updated_at'] !== null && $updatedAt < (int) $row['toutify_updated_at']) {
            return false;
        }

        $set = [];
        $params = ['id' => $userId];
        $str = static fn ($v, int $max): ?string => is_scalar($v) && trim((string) $v) !== '' ? mb_substr(trim((string) $v), 0, $max) : null;

        if (($name = $str($m['name'] ?? null, 160)) !== null) {
            [$first, $last] = self::splitName($name);
            $set[] = 'name = :name, first_name = :first, last_name = :last';
            $params += ['name' => $name, 'first' => $first, 'last' => $last];
        }
        if (($email = $str($m['email'] ?? null, 254)) !== null) {
            $email = mb_strtolower($email);
            $taken = Database::first('SELECT id FROM users WHERE email = :e AND id <> :id', ['e' => $email, 'id' => $userId]);
            if ($taken === null) {
                $set[] = 'email = :email';
                $params['email'] = $email;
            } elseif ($email !== $row['email']) {
                ErrorHandler::note('toutify', 'did not change user ' . $userId . "'s email: the new address is already used by user " . $taken['id']);
            }
        }
        // Toutify omits empty values, so a missing business name or phone
        // number means it was cleared there.
        $set[] = 'business_name = :business, phone = :phone';
        $params['business'] = $str($m['business_name'] ?? null, 160);
        $params['phone'] = $str($m['phone_number'] ?? null, 40);
        if (($status = $str($m['account_status'] ?? null, 20)) !== null) {
            $set[] = 'toutify_status = :status';
            $params['status'] = $status;
        }
        if ($updatedAt !== null) {
            $set[] = 'toutify_updated_at = :updated';
            $params['updated'] = $updatedAt;
        }
        Database::run('UPDATE users SET ' . implode(', ', $set) . ' WHERE id = :id', $params);

        // When the business name changes on Toutify, carry the change to the
        // business accounts this member owns, so their page and dashboard match.
        // Only a change: the first sync, or a name that was never different,
        // leaves a name chosen on LeadCrazy (e.g. in the builder) alone.
        if ($params['business'] !== null && $row['business_name'] !== null && $params['business'] !== $row['business_name']) {
            Database::run(
                "UPDATE accounts SET business_name = :b
                  WHERE id IN (SELECT account_id FROM account_users WHERE user_id = :u AND role = 'owner')",
                ['b' => $params['business'], 'u' => $userId],
            );
        }
        return true;
    }

    /** @return array{0:string,1:?string} */
    public static function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [$name];
        return [mb_substr($parts[0], 0, 80), isset($parts[1]) ? mb_substr($parts[1], 0, 80) : null];
    }

    /* ------------------------------------------------------------ sessions */

    /** Records a new member session and returns its key (kept in $_SESSION). */
    public static function openSession(int $userId, string $sub, ?string $sid): string
    {
        $key = bin2hex(random_bytes(32));
        Database::run(
            'INSERT INTO toutify_sessions (session_id, sid, sub, user_id, created_at) VALUES (:k, :sid, :sub, :u, NOW())',
            ['k' => $key, 'sid' => $sid, 'sub' => $sub, 'u' => $userId],
        );
        Database::run('DELETE FROM toutify_sessions WHERE created_at < NOW() - INTERVAL ' . self::SESSION_ROW_DAYS . ' DAY');
        return $key;
    }

    public static function sessionValid(string $key, string $sub): bool
    {
        return Database::first(
            'SELECT 1 AS ok FROM toutify_sessions WHERE session_id = :k AND sub = :s',
            ['k' => $key, 's' => $sub],
        ) !== null;
    }

    public static function closeSession(string $key): void
    {
        Database::run('DELETE FROM toutify_sessions WHERE session_id = :k', ['k' => $key]);
    }

    /** Ends every LeadCrazy session for this member. @return int sessions ended */
    public static function endAll(string $sub): int
    {
        return Database::run('DELETE FROM toutify_sessions WHERE sub = :s', ['s' => $sub])->rowCount();
    }

    /** Ends the sessions from one Toutify login session. @return int sessions ended */
    public static function endSid(string $sid, ?string $sub): int
    {
        if ($sub !== null) {
            return Database::run('DELETE FROM toutify_sessions WHERE sid = :sid AND sub = :s', ['sid' => $sid, 's' => $sub])->rowCount();
        }
        return Database::run('DELETE FROM toutify_sessions WHERE sid = :sid', ['sid' => $sid])->rowCount();
    }
}
