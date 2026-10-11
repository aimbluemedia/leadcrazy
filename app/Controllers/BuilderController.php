<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Accounts;
use App\Support\Audit;
use App\Support\Database;
use App\Support\Duplicates;
use App\Support\FormToken;
use App\Support\Pages;
use App\Support\Plans;
use App\Support\RateLimiter;
use App\Support\Request;
use App\Support\Trades;
use App\Support\View;

/**
 * The free lead form builder on /how-it-works, and publishing what it built.
 *
 * Submitting creates a Free account with the page pending approval. The
 * owner logs in to finish their account; staff approve it and it goes live.
 *
 * The builder page has no session (it is a public marketing page), so the
 * publish form is protected by a signed FormToken rather than a session CSRF
 * token, plus a honeypot and a per-IP limit. The POST goes to /members/build,
 * which does have a session, so the new owner can be signed in on success.
 */
final class BuilderController
{
    private const TOKEN_SCOPE = 'builder';

    public function show(): void
    {
        $this->render([], [], null);
    }

    /**
     * POST /members/check -- the builder asks this when leaving step 2, so a
     * business that already has a profile finds out before filling in the rest.
     * Answers only yes or no, and is rate limited.
     */
    public function check(): void
    {
        header('Content-Type: application/json');
        if (RateLimiter::tooManyAttempts('dupcheck:' . Request::ip(), 30, 600)) {
            echo json_encode(['match' => false]);
            return;
        }
        $business = mb_substr(trim((string) ($_POST['business'] ?? '')), 0, 160);
        $website = mb_substr(trim((string) ($_POST['website'] ?? '')), 0, 300);
        $email = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 254);
        echo json_encode(['match' => Duplicates::find($email, $business, $website) !== []]);
    }

    /** The "you already have a profile" page. */
    public static function exists(string $email, string $business): never
    {
        http_response_code(409);
        echo View::render('auth/exists', ['title' => 'You already have a profile', 'email' => $email, 'business' => $business]);
        exit;
    }

    /** GET /members/pending -- shown once, right after submitting the builder. */
    public function pending(): void
    {
        $info = $_SESSION['builder_pending'] ?? null;
        unset($_SESSION['builder_pending']);
        if (!is_array($info)) {
            Request::redirect('/members/login');
        }
        echo View::render('auth/pending', ['title' => 'Your lead form is pending approval'] + $info);
    }

    public function publish(): void
    {
        if (trim((string) ($_POST['company_website'] ?? '')) !== '') {
            Request::redirect('/how-it-works');
        }

        $raw = json_decode((string) ($_POST['builder'] ?? ''), true);
        $state = is_array($raw) ? $raw : [];
        $old = [
            'first_name' => mb_substr(trim((string) ($_POST['first_name'] ?? '')), 0, 80),
            'last_name' => mb_substr(trim((string) ($_POST['last_name'] ?? '')), 0, 80),
            'email' => mb_strtolower(mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 254)),
        ];

        $problem = FormToken::problem(self::TOKEN_SCOPE, is_string($_POST['_ft'] ?? null) ? $_POST['_ft'] : null);
        if ($problem !== null && $problem !== 'too fast') {
            $this->fail($state, $old, 'This page had been open a long time. Please check your details and publish again.');
        }
        if (RateLimiter::tooManyAttempts('build:' . Request::ip(), 5, 3600)) {
            $this->fail($state, $old, 'Too many new pages from your connection. Please try again in an hour.');
        }

        [$clean, $error] = $this->validate($state);
        if ($error !== null) {
            $this->fail($state, $old, $error);
        }

        $password = (string) ($_POST['password'] ?? '');
        if ($old['first_name'] === '') {
            $this->fail($clean, $old, 'Enter your first name.');
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $this->fail($clean, $old, 'Enter a valid email address.');
        }
        if (strlen($password) < PasswordController::MIN_LENGTH) {
            $this->fail($clean, $old, 'Use a password of at least ' . PasswordController::MIN_LENGTH . ' characters.');
        }
        if (empty($_POST['agree'])) {
            $this->fail($clean, $old, 'Please agree to the terms to publish your page.');
        }
        if (Duplicates::find($old['email'], $clean['business'], $clean['website']) !== []) {
            $this->exists($old['email'], $clean['business']);
        }

        $trade = Trades::get($clean['trade']);
        $place = trim($clean['city'] . ($clean['state'] !== '' ? ', ' . $clean['state'] : ''));
        $json = static fn (array $list): string => json_encode(array_values($list), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';

        $created = Accounts::create(
            [
                'business' => $clean['business'], 'first' => $old['first_name'], 'last' => $old['last_name'] ?: null,
                'email' => $old['email'], 'password' => $password, 'plan' => Plans::FREE, 'status' => 'building',
            ],
            [
                'badge' => 'Free ' . $trade['noun'] . ' Quote',
                'headline' => $clean['business'] . ' - ' . $trade['label'] . ' in ' . $place,
                'subheadline' => 'Your free quote in 24 hours.',
                'intro' => 'Tell us about your project and get a free, no-obligation quote.',
                'about' => $clean['business'] . ' provides ' . mb_strtolower($trade['label']) . ' services in ' . $place
                    . ' and nearby areas. Fill in the quick form and we will get back to you with a free, detailed quote.',
                'phone' => $clean['phone'],
                'public_email' => $old['email'],
                'city' => $clean['city'],
                'state' => $clean['state'] ?: null,
                'website' => $clean['website'] !== '' ? (preg_match('#^https?://#i', $clean['website']) ? $clean['website'] : 'https://' . $clean['website']) : null,
                'services' => $json($clean['services']),
                'form_services' => $json($clean['services']),
                'locations' => $json($clean['cities']),
                'zipcodes' => $json($clean['zips']),
                'form_budgets' => $json($trade['budgets']),
                'accent_color' => '#0071e3',
            ],
        );

        if ($clean['offer'] !== null) {
            Database::run(
                "INSERT INTO offers (account_id, label, title, body, expires_on, sort_order, is_active)
                 VALUES (:a, 'LIMITED TIME', :t, :b, :e, 1, 1)",
                ['a' => $created['account_id'], 't' => $clean['offer']['title'], 'b' => $clean['offer']['body'] ?: null, 'e' => $clean['offer']['expires']],
            );
        }

        Audit::log('account.created', 'account', $created['account_id'], null, ['via' => 'builder', 'trade' => $clean['trade'], 'slug' => $created['slug']]);

        // Not signed in on purpose: the next step is to log in and finish the
        // account, and the page waits for a person to approve it before it goes
        // live. The pending page reads these once.
        $_SESSION['builder_pending'] = [
            'business' => $clean['business'],
            'url' => Pages::url($created['slug']),
            'email' => $old['email'],
        ];
        Request::redirect('/members/pending');
    }

    /**
     * @param array<string,mixed> $s
     * @return array{0:array<string,mixed>,1:?string}
     */
    private function validate(array $s): array
    {
        $str = static fn ($v, int $max): string => is_scalar($v) ? mb_substr(trim((string) $v), 0, $max) : '';
        $list = static function ($v, int $max, int $count): array {
            $out = [];
            foreach (is_array($v) ? $v : [] as $item) {
                $item = is_scalar($item) ? mb_substr(trim((string) $item), 0, $max) : '';
                if ($item !== '' && !in_array($item, $out, true)) {
                    $out[] = $item;
                }
            }
            return array_slice($out, 0, $count);
        };

        $offerIn = is_array($s['offer'] ?? null) ? $s['offer'] : [];
        $clean = [
            'trade' => Trades::exists((string) ($s['trade'] ?? '')) ? (string) $s['trade'] : '',
            'business' => $str($s['business'] ?? '', 160),
            'phone' => $str($s['phone'] ?? '', 40),
            'city' => $str($s['city'] ?? '', 80),
            'state' => $str($s['state'] ?? '', 40),
            'website' => $str($s['website'] ?? '', 300),
            'services' => $list($s['services'] ?? [], 60, 15),
            'cities' => $list($s['cities'] ?? [], 60, Plans::FREE_CITIES),
            // Filter before capping, so a bad entry never pushes out a good one.
            'zips' => $list(array_filter(is_array($s['zips'] ?? null) ? $s['zips'] : [],
                static fn ($z) => is_scalar($z) && preg_match('/^\d{5}$/', trim((string) $z)) === 1), 5, Plans::FREE_ZIPS),
            'offer' => null,
        ];

        $offerOn = !empty($offerIn['on']);
        $offer = [
            'on' => $offerOn,
            'title' => $str($offerIn['title'] ?? '', 120),
            'body' => $str($offerIn['body'] ?? '', 600),
            'expires' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($offerIn['expires'] ?? '')) ? (string) $offerIn['expires'] : null,
        ];
        if ($offer['expires'] !== null && $offer['expires'] < date('Y-m-d')) {
            $offer['expires'] = null;
        }
        // Echoed back to the page on error, so the builder restores the offer
        // even when it is switched off.
        $clean['offerState'] = $offer;
        if ($offerOn && $offer['title'] !== '') {
            $clean['offer'] = $offer;
        }

        $error = match (true) {
            $clean['trade'] === '' => 'Pick your trade.',
            $clean['business'] === '' => 'Enter your business name.',
            strlen((string) preg_replace('/\D+/', '', $clean['phone'])) < 10 => 'Enter a phone number customers can call.',
            $clean['city'] === '' => 'Enter the city you are based in.',
            $clean['website'] !== '' && Duplicates::host($clean['website']) === '' => 'That website address does not look right. Use something like yourbusiness.com, or leave it blank.',
            $clean['services'] === [] => 'Turn on at least one service.',
            $clean['cities'] === [] && $clean['zips'] === [] => 'Add at least one city or zip code.',
            $offerOn && $offer['title'] === '' => 'Give your offer a title, or switch it off.',
            default => null,
        };

        return [$clean, $error];
    }

    /** @param array<string,mixed> $state @param array<string,string> $old */
    private function fail(array $state, array $old, string $error): never
    {
        if (isset($state['offerState'])) {
            $state['offer'] = $state['offerState'];
            unset($state['offerState']);
        }
        http_response_code(422);
        $this->render($state, $old, $error);
        exit;
    }

    /** @param array<string,mixed> $state @param array<string,string> $old */
    private function render(array $state, array $old, ?string $error): void
    {
        echo View::page('how-it-works', [
            'title' => 'Build your free lead form - LeadCrazy',
            'description' => 'Build a lead form for your business in six quick steps and publish it free.',
            'current' => 'how',
            'state' => $state,
            'old' => $old,
            'error' => $error,
            'token' => FormToken::issue(self::TOKEN_SCOPE),
        ]);
    }
}
