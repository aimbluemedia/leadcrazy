<?php

declare(strict_types=1);

namespace App\Support;

use PDO;

/**
 * Makes sure every table in database/schema.sql exists.
 *
 * A phpMyAdmin import that dies halfway leaves some tables and not others,
 * and the site then fails in odd places (signing in needs login_attempts,
 * for instance). Every statement in schema.sql is CREATE TABLE IF NOT EXISTS,
 * so running it can never touch existing data -- it only fills the gaps.
 *
 * It runs once per version of schema.sql: a fingerprint of the file is kept
 * in storage/schema.version. If storage is not writable, it checks the table
 * list (one cheap query) on each request instead and only runs when a table
 * is missing.
 */
final class Schema
{
    private static bool $done = false;

    public static function ensure(PDO $pdo): void
    {
        if (self::$done) {
            return;
        }
        self::$done = true;

        $file = BASE_PATH . '/database/schema.sql';
        if (!is_file($file)) {
            return;
        }
        $sql = (string) file_get_contents($file);
        $version = substr(hash('sha256', $sql), 0, 16);
        $marker = BASE_PATH . '/storage/schema.version';

        if (is_file($marker) && trim((string) @file_get_contents($marker)) === $version) {
            return;
        }

        $statements = array_values(array_filter(array_map(
            static fn ($s) => trim((string) preg_replace('/^--.*$/m', '', $s)),
            preg_split('/;\s*$/m', $sql) ?: [],
        )));

        preg_match_all('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $m);
        $wanted = $m[1];
        $have = array_map('strtolower', $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) ?: []);
        $missing = array_values(array_diff(array_map('strtolower', $wanted), $have));

        if ($missing !== []) {
            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }
            ErrorHandler::note('schema', 'created missing tables: ' . implode(', ', $missing));
        }

        @file_put_contents($marker, $version, LOCK_EX);
    }
}
