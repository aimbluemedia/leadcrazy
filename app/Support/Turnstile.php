<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Cloudflare Turnstile, the "I am not a robot" check on the lead form.
 *
 * Optional: with no keys in config the form still has the honeypot, the signed
 * timing token and the rate limit, and this simply reports success. That keeps
 * a fresh install usable before a Cloudflare account exists.
 */
final class Turnstile
{
    private const VERIFY = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public static function siteKey(): string
    {
        return trim((string) Config::get('turnstile.site_key', ''));
    }

    public static function enabled(): bool
    {
        return self::siteKey() !== '' && trim((string) Config::get('turnstile.secret_key', '')) !== '';
    }

    public static function passes(?string $response): bool
    {
        if (!self::enabled()) {
            return true;
        }
        if (!is_string($response) || $response === '' || strlen($response) > 2048) {
            return false;
        }

        $ch = curl_init(self::VERIFY);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'secret' => (string) Config::get('turnstile.secret_key'),
                'response' => $response,
                'remoteip' => Request::ip(),
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
        ]);
        $raw = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if (!is_string($raw)) {
            // Cloudflare unreachable. Failing open would let every bot through
            // during an outage; failing closed loses real leads. Closed, but
            // logged, because a run of these is something the operator must see.
            ErrorHandler::note('turnstile', 'verify failed: ' . $error);
            return false;
        }

        $body = json_decode($raw, true);
        return is_array($body) && ($body['success'] ?? false) === true;
    }
}
