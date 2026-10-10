<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A signed, stateless token for the public lead form.
 *
 * The lead form has no session, on purpose. Inside a member's own website it
 * runs in a third-party iframe, where browsers drop our cookie, so a session
 * CSRF token would fail every embedded submission. This does the same job
 * without a cookie: the token names the business and the moment the form was
 * drawn, signed with app_key.
 *
 * It also gives a cheap bot check. A form posted less than MIN_SECONDS after it
 * was drawn was not filled in by a person.
 */
final class FormToken
{
    public const MIN_SECONDS = 3;
    public const MAX_SECONDS = 6 * 3600;

    public static function issue(string $slug, ?int $at = null): string
    {
        $at ??= time();
        return $at . '.' . self::sign($slug, $at);
    }

    /** Null when good, otherwise why not (for the log, never shown verbatim). */
    public static function problem(string $slug, ?string $token, ?int $now = null): ?string
    {
        $now ??= time();
        if (!is_string($token) || !preg_match('/^(\d{9,11})\.([a-f0-9]{64})$/', $token, $m)) {
            return 'malformed';
        }
        $at = (int) $m[1];
        if (!hash_equals(self::sign($slug, $at), $m[2])) {
            return 'bad signature';
        }
        $age = $now - $at;
        if ($age < self::MIN_SECONDS) {
            return 'too fast';
        }
        if ($age > self::MAX_SECONDS) {
            return 'expired';
        }
        return null;
    }

    /** The token to keep on a re-rendered form: the original if still valid, else a fresh one. */
    public static function carry(string $slug, ?string $token): string
    {
        $problem = self::problem($slug, $token, time() + self::MIN_SECONDS);
        return $problem === null ? (string) $token : self::issue($slug);
    }

    private static function sign(string $slug, int $at): string
    {
        return hash_hmac('sha256', 'leadform|' . $slug . '|' . $at, AppKey::get());
    }
}
