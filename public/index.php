<?php

declare(strict_types=1);

/**
 * Front controller. Every request that is not a real file on disk is rewritten
 * here by public/.htaccess.
 */

/**
 * The PHP built-in server routes every request through this script, static
 * files included, so hand real files back to it. Apache never reaches this
 * branch — public/.htaccess already excludes existing files from the rewrite.
 */
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $candidate = realpath(__DIR__ . urldecode($path));
    // Only ever hand back known static assets. Anything else -- above all a
    // .php file -- must fall through so it is executed, never emitted as
    // source. Returning the raw bytes of a stray config.php would leak
    // credentials.
    $types = [
        'css' => 'text/css', 'js' => 'text/javascript', 'svg' => 'image/svg+xml',
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif', 'webp' => 'image/webp', 'ico' => 'image/x-icon',
        'woff' => 'font/woff', 'woff2' => 'font/woff2', 'txt' => 'text/plain',
    ];
    $ext = strtolower(pathinfo((string) $candidate, PATHINFO_EXTENSION));

    if ($candidate !== false
        && is_file($candidate)
        && str_starts_with($candidate, __DIR__ . DIRECTORY_SEPARATOR)
        && isset($types[$ext])
    ) {
        // Emitted directly rather than via `return false`, because the built-in
        // server resolves that against ITS document root, which is the project
        // root under the shared-hosting fallback layout.
        header('Content-Type: ' . $types[$ext]);
        readfile($candidate);
        return true;
    }

    // A real .php file under public/ (diagnose.php, say) is handed back to the
    // server so it EXECUTES it. `return false` is what makes that happen —
    // reading the bytes ourselves would emit the source instead.
    if ($candidate !== false
        && is_file($candidate)
        && str_starts_with($candidate, __DIR__ . DIRECTORY_SEPARATOR)
        && $ext === 'php'
        && $candidate !== __FILE__
    ) {
        return false;
    }
}

/**
 * Absolute path to the web root. Resolved from this file rather than from the
 * project root, because in the recommended deployment the contents of public/
 * are copied into public_html and no `public` directory exists on the server.
 */
define('PUBLIC_PATH', __DIR__);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\BillingController;
use App\Controllers\BuilderController;
use App\Controllers\BusinessController;
use App\Controllers\FeedController;
use App\Controllers\InstallController;
use App\Controllers\MembersController;
use App\Controllers\PageController;
use App\Controllers\PasswordController;
use App\Controllers\SignupController;
use App\Controllers\SuperadminController;
use App\Support\Router;

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Everything refuses to be framed except /embed/, which exists to be framed on
// Premium members' own websites. BusinessController::embed() sets the matching
// Content-Security-Policy for it.
if (!str_starts_with(REQUEST_PATH, '/embed/')) {
    header('X-Frame-Options: DENY');
}

$router = new Router();
$pages = new PageController();

$router->get('/',             [$pages, 'home']);
$router->get('/how-it-works', static fn () => (new BuilderController())->show());
$router->get('/pricing',      [$pages, 'pricing']);
$router->get('/privacy',      [$pages, 'privacy']);
$router->get('/terms',        [$pages, 'terms']);

// First-time setup; guarded by app_key. See InstallController.
$router->get('/install',  static fn () => (new InstallController())->show());
$router->post('/install', static fn () => (new InstallController())->run());

// ---------------------------------------------------------------- public pages
// No session on any of these; see app/bootstrap.php.
$router->postToken('/lead',   static fn (string $s) => (new BusinessController())->submit($s));
$router->getToken('/embed',   static fn (string $s) => (new BusinessController())->embed($s));
$router->getToken('/widget',  static fn (string $s) => (new BusinessController())->widget($s));
$router->get('/feed/monsterlist', static fn () => (new FeedController())->monsterList());

// Stripe. The signature header is the authorisation; no CSRF, no session.
$router->post('/webhooks/stripe', static fn () => (new BillingController())->webhook());

// ---------------------------------------------------------------- superadmin
// Controllers whose constructor demands a login are built inside each closure,
// so building them never redirects a public request.
$auth = new AuthController();
$router->get('/superadmin/login',     static fn () => $auth->showLogin('superadmin'));
$router->post('/superadmin/login',    static fn () => $auth->login('superadmin'));
$router->post('/superadmin/logout',   static fn () => $auth->logout('superadmin'));
$router->get('/superadmin/password',  static fn () => (new PasswordController())->show('superadmin'));
$router->post('/superadmin/password', static fn () => (new PasswordController())->update('superadmin'));

$sa = static fn (string $method) => static fn () => (new SuperadminController())->$method();
$router->get('/superadmin',                     $sa('overview'));
$router->get('/superadmin/accounts',            $sa('accounts'));
$router->get('/superadmin/account',             $sa('account'));
$router->get('/superadmin/preview',             $sa('preview'));
$router->post('/superadmin/account/page',       $sa('savePage'));
$router->post('/superadmin/account/settings',   $sa('saveSettings'));
$router->post('/superadmin/account/image',      $sa('uploadImage'));
$router->post('/superadmin/account/password',   $sa('setTempPassword'));
$router->post('/superadmin/gallery/add',        $sa('addGallery'));
$router->post('/superadmin/gallery/delete',     $sa('deleteGallery'));
$router->post('/superadmin/offer/save',         $sa('saveOffer'));
$router->post('/superadmin/offer/delete',       $sa('deleteOffer'));
$router->post('/superadmin/testimonial/save',   $sa('saveTestimonial'));
$router->post('/superadmin/testimonial/delete', $sa('deleteTestimonial'));
$router->get('/superadmin/leads',               $sa('leads'));
$router->get('/superadmin/requests',            $sa('requests'));
$router->get('/superadmin/errors',              $sa('errors'));
$router->post('/superadmin/requests/done',      $sa('closeRequest'));

// ---------------------------------------------------------------- members
$router->get('/members/login',     static fn () => $auth->showLogin('members'));
$router->post('/members/login',    static fn () => $auth->login('members'));
$router->post('/members/logout',   static fn () => $auth->logout('members'));
$router->get('/members/password',  static fn () => (new PasswordController())->show('members'));
$router->post('/members/password', static fn () => (new PasswordController())->update('members'));
$router->get('/members/forgot',    static fn () => $auth->forgot());

// The free lead form builder on /how-it-works publishes here. Under /members so
// it has a session to sign the new owner in; protected by a signed form token.
$router->post('/members/build', static fn () => (new BuilderController())->publish());

$router->get('/members/signup',  static fn () => (new SignupController())->show());
$router->post('/members/signup', static fn () => (new SignupController())->store());

$m = static fn (string $method) => static fn () => (new MembersController())->$method();
$router->get('/members',                 $m('overview'));
$router->get('/members/intake',          $m('intake'));
$router->post('/members/intake',         $m('saveIntake'));
$router->get('/members/leads',           $m('leads'));
$router->get('/members/lead',            $m('lead'));
$router->post('/members/lead',           $m('updateLead'));
$router->get('/members/leads/export',    $m('exportLeads'));
$router->get('/members/page',            $m('page'));
$router->post('/members/page/request',   $m('requestChange'));
$router->get('/members/embed',           $m('embed'));
$router->get('/members/billing',         $m('billing'));
$router->post('/members/plan',           $m('requestPlan'));

$router->post('/members/billing/start',  static fn () => (new BillingController())->start());
$router->post('/members/billing/manage', static fn () => (new BillingController())->manage());
$router->get('/members/billing/return',  static fn () => (new BillingController())->finish());

// ---------------------------------------------------------------- businesses
// leadcrazy.com/{slug}. Checked last: every route above wins over a slug.
$router->fallback('GET', static fn (string $s) => (new BusinessController())->show($s));

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/',
);
