<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Accounts;
use App\Support\Audit;
use App\Support\Auth;
use App\Support\Database;
use App\Support\ErrorHandler;
use App\Support\Notify;
use App\Support\Plans;
use App\Support\Request;
use App\Support\Toutify\Client;
use App\Support\Toutify\Members;
use App\Support\Toutify\SignInRefused;
use App\Support\View;

/**
 * Members sign in through Toutify, LeadCrazy's parent site (OpenID Connect).
 *
 *   GET  /auth/toutify/login                  start sign-in (?signup=1 asks Toutify to show sign-up)
 *   GET  /auth/toutify/callback               finish it
 *   GET  /auth/toutify/initiate               Toutify sends members here after sign-up or from its dashboard
 *   GET  /auth/toutify/logout                 sign out here and on Toutify
 *   POST /auth/toutify/backchannel-logout     Toutify ends a member's sessions
 *   POST /auth/toutify/webhook                Toutify reports member changes
 *
 * The first four have a session (app/bootstrap.php). The last two do not:
 * a signed token or signature is their authorisation, not a cookie.
 *
 * Superadmins are not Toutify members; they keep /superadmin/login.
 */
final class ToutifyController
{
    /** How long a started sign-in stays valid; sign-up on Toutify can take a while. */
    private const AUTH_TTL = 3600;
    private const WEBHOOK_TOLERANCE = 300;

    public function login(): void
    {
        $this->requireConfigured();

        $pending = array_filter(
            (array) ($_SESSION['toutify_auth'] ?? []),
            static fn ($a) => is_array($a) && (int) ($a['at'] ?? 0) > time() - self::AUTH_TTL,
        );
        // A few sign-ins in flight at once (several tabs) is fine; more is not.
        $pending = array_slice($pending, -4, null, true);

        $auth = Client::authorization(($_GET['signup'] ?? '') === '1');
        $pending[$auth['state']] = ['nonce' => $auth['nonce'], 'verifier' => $auth['verifier'], 'at' => time()];
        $_SESSION['toutify_auth'] = $pending;

        Request::redirect($auth['url']);
    }

    public function callback(): void
    {
        $this->requireConfigured();

        $state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
        $pending = $_SESSION['toutify_auth'][$state] ?? null;
        if ($state !== '') {
            unset($_SESSION['toutify_auth'][$state]);
        }

        if (isset($_GET['error'])) {
            $this->refuse('Sign-in was not completed on Toutify. Please try again.', 'authorize returned ' . mb_substr((string) $_GET['error'], 0, 60));
        }
        if (!is_array($pending) || (int) ($pending['at'] ?? 0) <= time() - self::AUTH_TTL) {
            $this->refuse('That sign-in link has expired or was already used. Please sign in again.', null);
        }
        $code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
        if ($code === '') {
            $this->refuse('Toutify did not send a sign-in code. Please try again.', 'callback without a code');
        }

        try {
            $result = Client::finish($code, (string) $pending['verifier'], (string) $pending['nonce']);
        } catch (\Throwable $e) {
            $this->refuse('We could not complete your sign-in with Toutify. Please try again.', 'sign-in failed: ' . $e->getMessage());
        }

        $info = $result['userinfo'];
        $status = (string) ($info['account_status'] ?? $result['claims']['account_status'] ?? '');
        if ($status !== 'active') {
            $this->refuse($status === 'suspended'
                ? 'Your Toutify account is suspended, so you cannot sign in to LeadCrazy right now.'
                : 'Your Toutify account is not active, so you cannot sign in to LeadCrazy.', null);
        }

        try {
            ['user' => $user] = Members::fromClaims($info);
        } catch (SignInRefused $e) {
            $this->refuse($e->getMessage(), 'sign-in refused for ' . mb_substr((string) $info['sub'], 0, 26) . ': ' . $e->getMessage());
        }

        $flash = $this->ensureAccount($user, $info);

        $sub = (string) $info['sub'];
        $sid = is_string($result['claims']['sid'] ?? null) ? $result['claims']['sid'] : null;
        Auth::signIn((int) $user['id'], 'auth.toutify_login');
        $_SESSION['toutify'] = [
            'key' => Members::openSession((int) $user['id'], $sub, $sid),
            'sub' => $sub,
            'sid' => $sid,
            'id_token' => $result['id_token'],
        ];
        if ($flash !== null) {
            $_SESSION['members_flash'] = $flash;
        }
        Request::redirect('/members');
    }

    public function initiate(): void
    {
        $this->requireConfigured();
        if (!is_string($_GET['iss'] ?? null) || rtrim($_GET['iss'], '/') !== Client::issuer()) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Unknown issuer.';
            return;
        }
        Request::redirect('/auth/toutify/login');
    }

    public function logout(): void
    {
        $idToken = is_string($_SESSION['toutify']['id_token'] ?? null) ? $_SESSION['toutify']['id_token'] : null;
        Auth::logout();
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time() - 3600] + array_intersect_key($params, array_flip(['path', 'domain', 'secure', 'httponly', 'samesite'])));
            session_destroy();
        }

        if (!Client::configured()) {
            Request::redirect('/');
        }
        try {
            Request::redirect(Client::endSessionUrl($idToken));
        } catch (\Throwable $e) {
            ErrorHandler::note('toutify', 'logout: could not reach Toutify end_session: ' . $e->getMessage());
            Request::redirect('/');
        }
    }

    public function backchannelLogout(): void
    {
        header('Cache-Control: no-store');
        $token = is_string($_POST['logout_token'] ?? null) ? $_POST['logout_token'] : '';
        try {
            if ($token === '' || !Client::configured()) {
                throw new \RuntimeException('no logout_token');
            }
            $claims = Client::verifyLogoutToken($token);
        } catch (\Throwable $e) {
            ErrorHandler::note('toutify', 'back-channel logout refused: ' . $e->getMessage());
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_request']);
            return;
        }

        $sub = is_string($claims['sub'] ?? null) ? $claims['sub'] : null;
        $sid = is_string($claims['sid'] ?? null) ? $claims['sid'] : null;
        $ended = $sid !== null ? Members::endSid($sid, $sub) : Members::endAll((string) $sub);
        Audit::log('auth.toutify_backchannel_logout', 'user', null, null, ['sub' => $sub, 'sid' => $sid, 'ended' => $ended]);

        http_response_code(200);
    }

    public function webhook(): void
    {
        header('Content-Type: application/json');
        $raw = (string) file_get_contents('php://input');
        $secret = Client::webhookSecret();

        if ($secret === '' || !$this->signatureValid($raw, (string) ($_SERVER['HTTP_TOUTIFY_SIGNATURE'] ?? ''), $secret)) {
            ErrorHandler::note('toutify', 'webhook refused: bad or missing signature');
            http_response_code(400);
            echo json_encode(['error' => 'bad signature']);
            return;
        }

        $event = json_decode($raw, true);
        if (!is_array($event) || !is_string($event['id'] ?? null) || $event['id'] === '' || !is_string($event['type'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'bad payload']);
            return;
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            // Insert first: a delivery we have already handled stops here.
            try {
                Database::run(
                    'INSERT INTO toutify_events (event_id, type, received_at) VALUES (:id, :type, NOW())',
                    ['id' => mb_substr($event['id'], 0, 40), 'type' => mb_substr($event['type'], 0, 40)],
                );
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    $pdo->rollBack();
                    echo json_encode(['ok' => true, 'duplicate' => true]);
                    return;
                }
                throw $e;
            }

            $this->applyEvent($event);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ErrorHandler::note('toutify', 'webhook ' . $event['type'] . ' ' . $event['id'] . ' failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'could not apply']);
            return;
        }

        // Keep the dedupe table to a month.
        Database::run('DELETE FROM toutify_events WHERE received_at < NOW() - INTERVAL 30 DAY');
        echo json_encode(['ok' => true]);
    }

    /* ------------------------------------------------------------ helpers */

    /** @param array<string,mixed> $event */
    private function applyEvent(array $event): void
    {
        $type = $event['type'];
        $member = is_array($event['member'] ?? null) ? $event['member'] : [];
        $sub = is_string($member['sub'] ?? null) ? $member['sub'] : '';

        if (!in_array($type, ['member.updated', 'member.reactivated', 'member.suspended', 'member.closed', 'member.deleted'], true) || $sub === '') {
            return; // site.test, website.* and anything new: acknowledged, ignored.
        }

        $user = Database::first('SELECT id FROM users WHERE toutify_sub = :s', ['s' => $sub]);

        if ($type === 'member.deleted') {
            Members::endAll($sub);
            if ($user !== null) {
                // Marked, not removed: their page and leads stay until staff decide.
                Database::run("UPDATE users SET toutify_status = 'deleted' WHERE id = :id", ['id' => $user['id']]);
                Audit::log('auth.toutify_member_deleted', 'user', (int) $user['id'], null, ['sub' => $sub]);
            }
            return;
        }

        if (in_array($type, ['member.suspended', 'member.closed'], true)) {
            Members::endAll($sub);
        }
        if ($user !== null) {
            Members::copy((int) $user['id'], $member);
        }
    }

    private function signatureValid(string $raw, string $header, string $secret): bool
    {
        $t = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($k === 't' && ctype_digit($v)) {
                $t = (int) $v;
            } elseif ($k === 'v1' && $v !== '') {
                $signatures[] = strtolower($v);
            }
        }
        if ($t === null || $signatures === [] || abs(time() - $t) > self::WEBHOOK_TOLERANCE) {
            return false;
        }
        $expected = hash_hmac('sha256', $t . '.' . $raw, $secret);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Makes sure the member has a business account: the one the builder
     * described, or a new one to fill in through intake.
     *
     * @param array<string,mixed> $user @param array<string,mixed> $info
     * @return ?string a message for the dashboard
     */
    private function ensureAccount(array $user, array $info): ?string
    {
        $build = $_SESSION['builder_build'] ?? null;
        unset($_SESSION['builder_build']);
        $plan = (string) ($_SESSION['signup_plan'] ?? Plans::FREE);
        unset($_SESSION['signup_plan']);

        $has = Database::first('SELECT account_id FROM account_users WHERE user_id = :u LIMIT 1', ['u' => $user['id']]);
        if ($has !== null) {
            return is_array($build) ? 'You already have a lead page, so we did not create another one.' : null;
        }

        if (is_array($build) && is_array($build['clean'] ?? null) && (int) ($build['at'] ?? 0) > time() - 86400) {
            BuilderController::createFromBuild($build['clean'], $user);
            return 'Your lead form is in! Our team will review it and publish it shortly.';
        }

        $business = trim((string) ($info['business_name'] ?? '')) ?: trim((string) ($info['name'] ?? '')) ?: strstr((string) $user['email'], '@', true);
        $website = is_string($info['website'] ?? null) && $info['website'] !== '' ? mb_substr($info['website'], 0, 300) : null;
        $created = Accounts::create(
            ['user_id' => (int) $user['id'], 'business' => (string) $business, 'plan' => Plans::isValid($plan) ? $plan : Plans::FREE],
            ['phone' => $user['phone'] ?? null, 'public_email' => $user['email'], 'website' => $website],
        );
        Notify::team('New signup: ' . $business, $created['account_id'], [
            'Business' => (string) $business, 'Plan' => $plan,
            'Owner' => trim((string) ($user['name'] ?? '')) . ' <' . $user['email'] . '>', 'Phone' => (string) ($user['phone'] ?? ''),
        ]);
        Audit::log('account.created', 'account', $created['account_id'], null, ['via' => 'toutify', 'plan' => $plan, 'slug' => $created['slug']]);
        return 'Welcome to LeadCrazy! Tell us about your business and we will build your page.';
    }

    private function requireConfigured(): void
    {
        if (!Client::configured() || Client::site() === '') {
            ErrorHandler::note('toutify', 'sign-in is not configured: set TOUTIFY_ISSUER, TOUTIFY_CLIENT_ID, TOUTIFY_CLIENT_SECRET and app_url');
            $this->refuse('Sign-in is being set up. Please try again shortly.', null);
        }
    }

    private function refuse(string $message, ?string $log): never
    {
        if ($log !== null) {
            ErrorHandler::note('toutify', $log);
        }
        http_response_code(400);
        echo View::render('auth/toutify-error', ['title' => 'Sign-in problem', 'message' => $message]);
        exit;
    }
}
