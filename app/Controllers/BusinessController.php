<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Config;
use App\Support\ErrorHandler;
use App\Support\FormToken;
use App\Support\Leads;
use App\Support\Pages;
use App\Support\Plans;
use App\Support\RateLimiter;
use App\Support\Request;
use App\Support\Turnstile;
use App\Support\View;

/**
 * The public side: a business's Million Dollar Lead Form page, the lead it
 * takes, and the embed Premium members put on their own websites.
 *
 * Nothing here has a session (see bootstrap.php). The visitor is a customer
 * of one of our customers.
 */
final class BusinessController
{
    /** GET /{slug} */
    public function show(string $slug): void
    {
        $bundle = $this->live($slug);

        echo View::render('business/layout', $bundle + [
            'title' => $this->title($bundle),
            'mode' => 'hosted',
            'form' => $this->freshForm($slug, isset($_GET['thanks'])),
            'body' => 'business/page',
        ]);
    }

    /**
     * GET /embed/{slug}?view=page|form
     *
     * What the $49 widget puts in its iframe. Only Premium pages can be framed,
     * which is what stops a Free page being lifted onto somebody's site.
     */
    public function embed(string $slug): void
    {
        $bundle = $this->live($slug, embed: true);
        $view = ($_GET['view'] ?? 'form') === 'page' ? 'page' : 'form';

        header("Content-Security-Policy: frame-ancestors *");

        echo View::render('business/layout', $bundle + [
            'title' => $this->title($bundle),
            'mode' => $view === 'page' ? 'embed-page' : 'embed-form',
            'form' => $this->freshForm($slug, isset($_GET['thanks'])),
            'body' => $view === 'page' ? 'business/page' : 'business/embed-form',
        ]);
    }

    /**
     * GET /widget/{slug}.js
     *
     * The one line a member pastes. It draws an iframe of /embed/{slug} where
     * the script tag sits and keeps its height matched to the content, so the
     * form never shows a scrollbar inside their page.
     */
    public function widget(string $file): void
    {
        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: public, max-age=300');

        $slug = str_ends_with($file, '.js') ? substr($file, 0, -3) : $file;
        $bundle = preg_match('/^[a-z0-9-]{1,80}$/', $slug) ? Pages::bundle($slug) : null;

        if ($bundle === null || !Pages::isPublic($bundle['account'])
            || !Plans::canEmbed((string) $bundle['account']['plan'])) {
            echo "console.warn('LeadCrazy: this lead form is not available for embedding. "
                . "Embedding is part of the Premium plan.');\n";
            return;
        }

        echo View::render('business/widget-js', [
            'slug' => $slug,
            'origin' => rtrim((string) Config::get('app_url', ''), '/'),
        ]);
    }

    /** POST /lead/{slug} */
    public function submit(string $slug): void
    {
        $via = (string) ($_POST['via'] ?? 'hosted');
        $isEmbed = in_array($via, ['embed-page', 'embed-form'], true);
        $bundle = $this->live($slug, embed: $isEmbed);
        $account = $bundle['account'];

        if ($isEmbed) {
            header("Content-Security-Policy: frame-ancestors *");
        }

        $thanks = $isEmbed
            ? '/embed/' . rawurlencode($slug) . '?view=' . ($via === 'embed-page' ? 'page' : 'form') . '&thanks=1'
            : '/' . rawurlencode($slug) . '?thanks=1#quote';

        // The honeypot. Hidden from people with CSS and aria-hidden, filled in by
        // bots that complete every field. Told it worked, so it does not adapt.
        if (trim((string) ($_POST['company_website'] ?? '')) !== '') {
            Request::redirect($thanks);
        }

        $token = is_string($_POST['_ft'] ?? null) ? $_POST['_ft'] : null;
        $problem = FormToken::problem($slug, $token);
        if ($problem === 'too fast') {
            Request::redirect($thanks);
        }

        $errors = [];
        $old = $_POST;

        if ($problem !== null) {
            $errors['_form'] = 'This form had been open a long time. Please check your details and send it again.';
            $token = null;
        }

        $ip = Request::ip();
        if (RateLimiter::tooManyAttempts('lead:' . $ip, 12, 3600)
            || RateLimiter::tooManyAttempts('lead:' . $slug . ':' . $ip, 4, 600)) {
            $errors['_form'] = 'We have had several requests from your connection just now. '
                . 'Please wait a few minutes, or call us directly.';
        }

        if ($errors === [] && !Turnstile::passes($_POST['cf-turnstile-response'] ?? null)) {
            $errors['_form'] = 'Please tick "I am not a robot" and send again.';
        }

        $data = $this->validate($bundle, $errors);

        if ($errors !== []) {
            http_response_code(422);
            echo View::render('business/layout', $bundle + [
                'title' => $this->title($bundle),
                'mode' => $isEmbed ? $via : 'hosted',
                'form' => [
                    'token' => FormToken::carry($slug, $token),
                    'errors' => $errors,
                    'old' => $old,
                    'thanks' => false,
                ],
                'body' => $via === 'embed-form' ? 'business/embed-form' : 'business/page',
            ]);
            return;
        }

        try {
            Leads::create($account, $data, $isEmbed ? 'embed' : 'hosted');
        } catch (\Throwable $e) {
            ErrorHandler::note('lead', 'could not store a lead for ' . $slug . ': ' . $e->getMessage());
            throw $e;
        }

        Request::redirect($thanks);
    }

    /* ------------------------------------------------------------ helpers */

    /** @return array<string,mixed> */
    private function live(string $slug, bool $embed = false): array
    {
        $bundle = preg_match('/^[a-z0-9-]{1,80}$/', $slug) ? Pages::bundle($slug) : null;

        if ($bundle === null || !Pages::isPublic($bundle['account'])
            || ($embed && !Plans::canEmbed((string) $bundle['account']['plan']))) {
            http_response_code(404);
            if ($embed) {
                header("Content-Security-Policy: frame-ancestors *");
                echo '<!doctype html><meta charset="utf-8"><p style="font:14px system-ui;color:#666">'
                    . 'This lead form is not available.</p>';
                exit;
            }
            echo View::page('errors/404', ['title' => 'Page not found']);
            exit;
        }

        return $bundle;
    }

    /** @return array<string,mixed> */
    private function freshForm(string $slug, bool $thanks): array
    {
        return ['token' => FormToken::issue($slug), 'errors' => [], 'old' => [], 'thanks' => $thanks];
    }

    /** @param array<string,mixed> $bundle */
    private function title(array $bundle): string
    {
        $headline = trim((string) ($bundle['page']['headline'] ?? ''));
        return $headline !== '' ? $headline : (string) $bundle['account']['business_name'];
    }

    /**
     * @param array<string,mixed> $bundle
     * @param array<string,string> $errors
     * @return array<string,mixed>
     */
    private function validate(array $bundle, array &$errors): array
    {
        $s = static fn (string $k, int $max = 120): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

        $data = [
            'first_name' => $s('first_name', 80),
            'last_name' => $s('last_name', 80),
            'email' => mb_strtolower($s('email', 254)),
            'phone' => $s('phone', 40),
            'city' => $s('city', 80),
            'zip' => $s('zip', 10),
            'message' => $s('message', 4000),
        ];

        if ($data['first_name'] === '') {
            $errors['first_name'] = 'Enter your first name.';
        }
        if ($data['last_name'] === '') {
            $errors['last_name'] = 'Enter your last name.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        $digits = preg_replace('/\D+/', '', $data['phone']) ?? '';
        if (strlen($digits) < 10 || strlen($digits) > 15) {
            $errors['phone'] = 'Enter a phone number we can reach you on.';
        }
        if ($data['city'] === '') {
            $errors['city'] = 'Enter your city.';
        }
        if (!preg_match('/^\d{5}(-\d{4})?$/', $data['zip'])) {
            $errors['zip'] = 'Enter a 5-digit zip code.';
        }

        // Choices must be ones the page actually offered. Anything else is
        // dropped rather than stored, so a lead never carries text the business
        // did not write.
        $services = array_values(array_intersect(
            array_map('strval', (array) ($_POST['services'] ?? [])),
            $bundle['formServices'],
        ));
        if ($bundle['formServices'] !== [] && $services === []) {
            $errors['services'] = 'Choose at least one service.';
        }
        $data['services'] = $services;

        $timeline = (string) ($_POST['timeline'] ?? '');
        if (!in_array($timeline, $bundle['timelines'], true)) {
            $errors['timeline'] = 'Tell us when you are looking to start.';
            $timeline = '';
        }
        $data['timeline'] = $timeline;

        $budget = (string) ($_POST['budget'] ?? '');
        if (!in_array($budget, $bundle['budgets'], true)) {
            $errors['budget'] = 'Choose a budget range.';
            $budget = '';
        }
        $data['budget'] = $budget;

        $offer = (string) ($_POST['offer'] ?? '');
        $offerTitles = $bundle['full'] ? array_map(static fn ($o) => (string) $o['title'], $bundle['offers']) : [];
        $data['offer'] = in_array($offer, $offerTitles, true) ? $offer : null;

        if ($data['message'] === '') {
            $errors['message'] = 'Tell us a little about your project.';
        }

        foreach (['last_name', 'city', 'zip', 'timeline', 'budget'] as $optional) {
            if ($data[$optional] === '') {
                $data[$optional] = null;
            }
        }

        return $data;
    }
}
