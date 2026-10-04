<?php

declare(strict_types=1);

/**
 * Creates (or resets) a LeadCrazy superadmin login.
 *
 *   php database/make-admin.php you@leadcrazy.com
 *
 * Prints a temporary password; you choose your own on first sign-in at
 * /superadmin/login.
 *
 * No shell on your host? Run this anywhere PHP is installed to get a hash:
 *   php -r "echo password_hash('a-long-temporary-password', PASSWORD_DEFAULT), PHP_EOL;"
 * then in phpMyAdmin:
 *   INSERT INTO users (email, password_hash, is_admin, must_change_password)
 *   VALUES ('you@leadcrazy.com', '<the hash>', 1, 1);
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Support\Database;

$email = mb_strtolower(trim((string) ($argv[1] ?? '')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php database/make-admin.php you@example.com\n");
    exit(1);
}

$temp = bin2hex(random_bytes(8));
$hash = password_hash($temp, PASSWORD_DEFAULT);

Database::run(
    'INSERT INTO users (email, password_hash, is_admin, must_change_password, status)
     VALUES (:e, :h, 1, 1, \'active\')
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), is_admin = 1,
                             must_change_password = 1, status = \'active\'',
    ['e' => $email, 'h' => $hash],
);

echo "Superadmin ready: {$email}\nTemporary password: {$temp}\nSign in at /superadmin/login and choose your own.\n";
