<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The secret that signs lead form and builder tokens.
 *
 * Normally app_key in app/config.php. A fresh install often leaves it empty,
 * and the public pages must not fall over because of that, so when it is
 * missing a random key is generated once and kept in storage/app.key. If
 * storage is not writable either, a key is derived from the database
 * credentials, which are just as private and stay the same between requests.
 */
final class AppKey
{
    private static ?string $key = null;

    public static function get(): string
    {
        if (self::$key !== null) {
            return self::$key;
        }

        $configured = trim((string) Config::get('app_key', ''));
        if ($configured !== '') {
            return self::$key = $configured;
        }

        $file = BASE_PATH . '/storage/app.key';
        $stored = is_file($file) ? trim((string) @file_get_contents($file)) : '';
        if (strlen($stored) >= 32) {
            return self::$key = $stored;
        }

        $generated = bin2hex(random_bytes(32));
        if (@file_put_contents($file, $generated, LOCK_EX) !== false) {
            @chmod($file, 0600);
            ErrorHandler::note('app_key', 'app_key is empty in app/config.php; generated storage/app.key instead.');
            return self::$key = $generated;
        }

        return self::$key = hash('sha256', 'leadcrazy|' . Config::get('db.host') . '|' . Config::get('db.database')
            . '|' . Config::get('db.username') . '|' . Config::get('db.password') . '|' . APP_ROOT);
    }
}
