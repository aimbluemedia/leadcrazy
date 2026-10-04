<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Config;
use App\Support\Database;
use App\Support\View;

/**
 * /install -- creates the tables and the first superadmin from the browser.
 *
 * Exists because phpMyAdmin on some shared hosts mangles long CREATE TABLE
 * statements on import. This runs database/schema.sql through PDO instead.
 *
 * Guarded by app_key: nothing happens unless the person typing knows the
 * value in app/config.php, which only somebody with file access does. Every
 * statement is CREATE TABLE IF NOT EXISTS, so running it twice is harmless,
 * and a superadmin is only created while none exists.
 */
final class InstallController
{
    public function show(): void
    {
        $this->render([]);
    }

    public function run(): void
    {
        $key = (string) Config::get('app_key', '');
        if ($key === '' || !hash_equals($key, (string) ($_POST['app_key'] ?? ''))) {
            $this->render(['error' => 'That is not the app_key from app/config.php.']);
            return;
        }

        $log = [];
        $sql = (string) file_get_contents(BASE_PATH . '/database/schema.sql');
        foreach (preg_split('/;\s*$/m', $sql) ?: [] as $statement) {
            $statement = trim((string) preg_replace('/^--.*$/m', '', $statement));
            if ($statement === '') {
                continue;
            }
            try {
                Database::connection()->exec($statement);
                $log[] = 'OK  ' . (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $statement, $m) ? 'table ' . $m[1] : mb_substr($statement, 0, 40));
            } catch (\PDOException $e) {
                $log[] = 'ERR ' . $e->getMessage();
            }
        }

        $hasAdmin = Database::first('SELECT id FROM users WHERE is_admin = 1 LIMIT 1') !== null;
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if (!$hasAdmin && $email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < PasswordController::MIN_LENGTH) {
                $log[] = 'ERR superadmin needs a valid email and a password of at least ' . PasswordController::MIN_LENGTH . ' characters.';
            } else {
                Database::run(
                    "INSERT INTO users (email, password_hash, is_admin, status, password_changed_at)
                     VALUES (:e, :h, 1, 'active', NOW())
                     ON DUPLICATE KEY UPDATE is_admin = 1, password_hash = VALUES(password_hash)",
                    ['e' => $email, 'h' => password_hash($password, PASSWORD_DEFAULT)],
                );
                $log[] = 'OK  superadmin ' . $email . ' created -- sign in at /superadmin/login';
                $hasAdmin = true;
            }
        }

        $this->render(['log' => $log, 'hasAdmin' => $hasAdmin]);
    }

    /** @param array<string,mixed> $data */
    private function render(array $data): void
    {
        $hasAdmin = $data['hasAdmin'] ?? $this->adminExists();
        echo View::page('install', $data + ['title' => 'Install LeadCrazy', 'hasAdmin' => $hasAdmin]);
    }

    private function adminExists(): bool
    {
        try {
            return Database::first('SELECT id FROM users WHERE is_admin = 1 LIMIT 1') !== null;
        } catch (\PDOException) {
            return false;   // no tables yet
        }
    }
}
