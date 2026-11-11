<?php

declare(strict_types=1);

/**
 * Application bootstrap. Loaded by public/index.php.
 *
 * No Composer, same as PromoMonster: shared hosting often has no shell, so the
 * app is a plain upload with a hand-rolled PSR-4 autoloader and no build step.
 */

define('APP_ROOT', __DIR__);
define('BASE_PATH', dirname(__DIR__));

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', BASE_PATH . '/public');
}

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $file = APP_ROOT . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$configFile = APP_ROOT . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuration missing. Copy app/config.example.php to app/config.php and fill it in.');
}

/** @var array<string,mixed> $config */
$config = require $configFile;

date_default_timezone_set($config['timezone'] ?? 'UTC');

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

App\Support\ErrorHandler::register((bool) ($config['debug'] ?? false));
App\Support\Config::load($config);

/**
 * Sessions only where somebody logs in.
 *
 * Business pages, the embed and the lead form are visited by a business's
 * customers, who have never heard of LeadCrazy. They get no cookie: there is
 * no login to keep, the lead form is protected by a signed token instead (see
 * FormToken), and a cookie set inside an embed on somebody else's website is
 * one their privacy policy would then have to declare.
 */
$requestPath = '/' . ltrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
if (str_starts_with($requestPath, '/public/')) {
    $requestPath = substr($requestPath, 7);
}
define('REQUEST_PATH', $requestPath);

// Toutify sign-in needs the session to carry state, nonce and PKCE verifier
// across the trip to Toutify; /signup remembers a chosen plan. The back-channel
// logout and webhook routes are server-to-server and get no session.
$needsSession = PHP_SAPI !== 'cli'
    && (preg_match('#^/(members|superadmin)(/|$)#', $requestPath) === 1
        || preg_match('#^/auth/toutify/(login|callback|initiate|logout)$#', $requestPath) === 1
        || $requestPath === '/signup');

if ($needsSession) {
    // Sessions live in storage/sessions with a two-hour idle lifetime: long
    // enough for sign-up on Toutify (30+ minutes), and out of the shared temp
    // directory where other sites' cleanup could delete them early.
    $sessionDir = BASE_PATH . '/storage/sessions';
    if ((is_dir($sessionDir) || @mkdir($sessionDir, 0700, true)) && is_writable($sessionDir)) {
        session_save_path($sessionDir);
    }
    ini_set('session.gc_maxlifetime', '7200');
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('lc_session');
    session_start();
}
