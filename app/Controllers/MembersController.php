<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Audit;
use App\Support\Auth;
use App\Support\Billing;
use App\Support\Config;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Leads;
use App\Support\Pages;
use App\Support\Plans;
use App\Support\Request;
use App\Support\Uploads;
use App\Support\View;

/**
 * The members area: leads, the page we built, embed code and billing.
 *
 * Members do not edit their page directly -- LeadCrazy staff build it from the
 * intake. They see it, and ask for changes.
 */
final class MembersController
{
    /** @var array<string,mixed> */
    private array $account;

    public function __construct()
    {
        Auth::requireMember();
        $this->account = Auth::account() ?? [];
    }

    public function overview(): void
    {
        $id = (int) $this->account['id'];
        $plan = (string) $this->account['plan'];

        $counts = Database::first(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'new') AS fresh,
                    SUM(created_at >= :start) AS month,
                    SUM(is_locked = 1) AS locked
               FROM leads WHERE account_id = :id",
            ['id' => $id, 'start' => Leads::monthStart()],
        ) ?? [];

        $recent = Database::all(
            'SELECT * FROM leads WHERE account_id = :id ORDER BY created_at DESC, id DESC LIMIT 5',
            ['id' => $id],
        );

        $this->render('members/overview', [
            'title' => 'Dashboard',
            'current' => 'overview',
            'counts' => $counts,
            'recent' => $recent,
            'monthCount' => Leads::countThisMonth($id),
            'hasIntake' => $this->latestIntake() !== null,
            'plan' => $plan,
        ]);
    }

    /* ------------------------------------------------------------ intake */

    public function intake(): void
    {
        $latest = $this->latestIntake();
        $this->render('members/intake', [
            'title' => 'Tell us about your business',
            'current' => 'page',
            'data' => $latest !== null ? (json_decode((string) $latest['data'], true) ?: []) : [],
            'page' => Database::first('SELECT * FROM pages WHERE account_id = :id', ['id' => $this->account['id']]) ?? [],
            'submittedAt' => $latest['submitted_at'] ?? null,
        ]);
    }

    public function saveIntake(): void
    {
        // A POST bigger than post_max_size arrives with $_POST and $_FILES empty,
        // which would otherwise look like a CSRF failure and lose everything.
        if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->flash('Those photos were too large to send together. Try fewer at a time.', '/members/intake');
        }
        $this->csrf('/members/intake');

        $id = (int) $this->account['id'];
        $t = static fn (string $k, int $max = 2000): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

        $data = [
            'business_name' => $t('business_name', 160) ?: (string) $this->account['business_name'],
            'phone' => $t('phone', 40),
            'public_email' => $t('public_email', 254),
            'website' => $t('website', 300),
            'city' => $t('city', 80),
            'state' => $t('state', 40),
            'years' => $t('years', 40),
            'services' => $t('services', 4000),
            'locations' => $t('locations', 4000),
            'zipcodes' => $t('zipcodes', 4000),
            'about' => $t('about', 6000),
            'why' => $t('why', 4000),
            'offers' => $t('offers', 6000),
            'testimonials' => $t('testimonials', 8000),
            'video_url' => $t('video_url', 300),
            'notes' => $t('notes', 4000),
            'photos' => [],
            'logo' => null,
        ];

        $problems = [];
        if (!empty($_FILES['logo']['name'])) {
            $saved = Uploads::image($_FILES['logo'], $id);
            isset($saved['path']) ? $data['logo'] = $saved['path'] : $problems[] = 'Logo: ' . $saved['error'];
        }
        foreach (array_slice(Uploads::many('photos'), 0, 12) as $file) {
            $saved = Uploads::image($file, $id);
            isset($saved['path']) ? $data['photos'][] = $saved['path'] : $problems[] = $file['name'] . ': ' . $saved['error'];
        }

        Database::run(
            'INSERT INTO intakes (account_id, data) VALUES (:id, :data)',
            ['id' => $id, 'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
        );

        $this->prefillPage($data);

        if ($this->account['page_status'] === 'intake') {
            Database::run("UPDATE accounts SET page_status = 'building' WHERE id = :id", ['id' => $id]);
        }
        Audit::log('intake.submitted', 'account', $id);

        $message = 'Thanks! We have what we need and will build your page, usually within one business day.';
        if ($problems !== []) {
            $message .= ' Some images were not saved: ' . implode('; ', $problems);
        }

        // Chose $19 or $49 at signup: take them to pay now, while they are here.
        $requested = (string) ($this->account['requested_plan'] ?? '');
        if (Plans::isPaid($requested) && !Billing::hasLiveSubscription($this->account) && Billing::canCharge($requested)) {
            $_SESSION['members_flash'] = $message;
            (new BillingController())->checkout($this->account, $requested);
        }

        $this->flash($message, '/members');
    }

    /**
     * Copies what the member told us onto the page where the page is still
     * blank, so staff start from the member's own words. Never overwrites
     * anything staff already wrote.
     *
     * @param array<string,mixed> $data
     */
    private function prefillPage(array $data): void
    {
        $id = (int) $this->account['id'];
        $page = Database::first('SELECT * FROM pages WHERE account_id = :id', ['id' => $id]) ?? [];

        $fill = [
            'phone' => $data['phone'],
            'public_email' => $data['public_email'],
            'website' => $data['website'],
            'city' => $data['city'],
            'state' => $data['state'],
            'about' => $data['about'],
            'video_url' => $data['video_url'],
            'logo_path' => $data['logo'],
            'services' => $data['services'] !== '' ? Pages::fromLines($data['services'], true) : null,
            'locations' => $data['locations'] !== '' ? Pages::fromLines($data['locations'], true) : null,
            'zipcodes' => $data['zipcodes'] !== '' ? Pages::fromLines($data['zipcodes'], true) : null,
            'why_points' => $data['why'] !== '' ? Pages::fromLines($data['why']) : null,
            'hero_path' => $data['photos'][0] ?? null,
        ];

        $sets = [];
        $params = ['id' => $id];
        foreach ($fill as $column => $value) {
            $currentlyBlank = trim((string) ($page[$column] ?? '')) === '' || ($page[$column] ?? '') === '[]';
            if ($value !== null && $value !== '' && $currentlyBlank) {
                $sets[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
        }
        if ($sets !== []) {
            Database::run('UPDATE pages SET ' . implode(', ', $sets) . ' WHERE account_id = :id', $params);
        }

        $order = (int) (Database::first('SELECT COALESCE(MAX(sort_order), 0) AS n FROM gallery_images WHERE account_id = :id', ['id' => $id])['n'] ?? 0);
        foreach ($data['photos'] as $path) {
            Database::run(
                'INSERT INTO gallery_images (account_id, path, sort_order) VALUES (:id, :path, :o)',
                ['id' => $id, 'path' => $path, 'o' => ++$order],
            );
        }
    }

    /* ------------------------------------------------------------- leads */

    public function leads(): void
    {
        $status = (string) ($_GET['status'] ?? '');
        $where = 'account_id = :id';
        $params = ['id' => $this->account['id']];
        if (in_array($status, Leads::STATUSES, true)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }

        $page = max(1, (int) ($_GET['p'] ?? 1));
        $per = 50;
        $total = (int) (Database::first("SELECT COUNT(*) AS n FROM leads WHERE {$where}", $params)['n'] ?? 0);
        $rows = Database::all(
            "SELECT * FROM leads WHERE {$where} ORDER BY created_at DESC, id DESC LIMIT {$per} OFFSET " . (($page - 1) * $per),
            $params,
        );

        $this->render('members/leads', [
            'title' => 'Leads',
            'current' => 'leads',
            'leads' => $rows,
            'status' => $status,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $per)),
            'total' => $total,
        ]);
    }

    public function lead(): void
    {
        $lead = $this->findLead((int) ($_GET['id'] ?? 0));
        $this->render('members/lead', [
            'title' => 'Lead',
            'current' => 'leads',
            'lead' => $lead,
            'visible' => Leads::visible($lead, $this->account),
        ]);
    }

    public function updateLead(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $this->csrf('/members/lead?id=' . $id);
        $lead = $this->findLead($id);

        if (!Leads::visible($lead, $this->account)) {
            $this->flash('Upgrade to work this lead.', '/members/lead?id=' . $id);
        }

        $status = (string) ($_POST['status'] ?? $lead['status']);
        if (!in_array($status, Leads::STATUSES, true)) {
            $status = (string) $lead['status'];
        }
        Database::run(
            'UPDATE leads SET status = :s, notes = :n WHERE id = :id AND account_id = :a',
            [
                's' => $status,
                'n' => mb_substr(trim((string) ($_POST['notes'] ?? '')), 0, 8000) ?: null,
                'id' => $id,
                'a' => $this->account['id'],
            ],
        );

        $this->flash('Lead updated.', '/members/lead?id=' . $id);
    }

    /** CSV of every lead the member can see. Locked leads are left out, not blanked. */
    public function exportLeads(): void
    {
        $rows = Database::all('SELECT * FROM leads WHERE account_id = :id ORDER BY created_at DESC', ['id' => $this->account['id']]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="leads-' . $this->account['slug'] . '-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Date', 'First name', 'Last name', 'Email', 'Phone', 'City', 'Zip', 'Services', 'Start', 'Budget', 'Offer', 'Project', 'Status', 'Notes', 'Source']);
        foreach ($rows as $r) {
            if (!Leads::visible($r, $this->account)) {
                continue;
            }
            $cells = [
                $r['created_at'], $r['first_name'], $r['last_name'], $r['email'], $r['phone'], $r['city'],
                $r['zip'], implode('; ', Leads::services($r['services'])), $r['timeline'], $r['budget'],
                $r['offer'], $r['message'], $r['status'], $r['notes'], $r['source'],
            ];
            // A cell starting with = + - @ is a formula to Excel. Leads are typed
            // by strangers, so neutralise it.
            $cells = array_map(static fn ($c) => preg_match('/^[=+\-@\t\r]/', (string) $c) ? "'" . $c : (string) $c, $cells);
            fputcsv($out, $cells);
        }
        fclose($out);
    }

    /* -------------------------------------------------------------- page */

    public function page(): void
    {
        $this->render('members/page', [
            'title' => 'Your page',
            'current' => 'page',
            'requests' => Database::all(
                'SELECT * FROM change_requests WHERE account_id = :id ORDER BY created_at DESC LIMIT 20',
                ['id' => $this->account['id']],
            ),
            'hasIntake' => $this->latestIntake() !== null,
        ]);
    }

    public function requestChange(): void
    {
        $this->csrf('/members/page');
        $body = mb_substr(trim((string) ($_POST['body'] ?? '')), 0, 4000);
        if ($body === '') {
            $this->flash('Tell us what you would like changed.', '/members/page');
        }

        Database::run(
            'INSERT INTO change_requests (account_id, user_id, body) VALUES (:a, :u, :b)',
            ['a' => $this->account['id'], 'u' => Auth::user()['id'] ?? null, 'b' => $body],
        );
        $this->flash('Got it. We will make that change and let you know.', '/members/page');
    }

    public function embed(): void
    {
        $this->render('members/embed', [
            'title' => 'Embed on your website',
            'current' => 'embed',
            'canEmbed' => Plans::canEmbed((string) $this->account['plan']),
            'live' => $this->account['page_status'] === 'live',
        ]);
    }

    /* ----------------------------------------------------------- billing */

    public function billing(): void
    {
        $this->render('members/billing', [
            'title' => 'Plan & billing',
            'current' => 'billing',
            'stripeLive' => Billing::live(),
            'testMode' => Billing::testMode(),
            'subscribed' => Billing::hasLiveSubscription($this->account),
        ]);
    }

    /**
     * Without Stripe, choosing a paid plan records a request for staff to act
     * on. Also the only way to ask for Free, which needs no payment.
     */
    public function requestPlan(): void
    {
        $this->csrf('/members/billing');
        $plan = (string) ($_POST['plan'] ?? '');

        if ($plan === Plans::FREE) {
            if (Billing::hasLiveSubscription($this->account)) {
                $this->flash('Cancel from "Manage billing" and your page moves to Free at the end of the period.', '/members/billing');
            }
            Database::run("UPDATE accounts SET plan = 'free', requested_plan = NULL, plan_changed_at = NOW() WHERE id = :id",
                ['id' => $this->account['id']]);
            $this->flash('You are on the Free plan.', '/members/billing');
        }

        if (!Plans::isPaid($plan)) {
            $this->flash('Choose a plan.', '/members/billing');
        }
        Database::run('UPDATE accounts SET requested_plan = :p WHERE id = :id', ['p' => $plan, 'id' => $this->account['id']]);
        Audit::log('account.plan_requested', 'account', (int) $this->account['id'], null, ['plan' => $plan]);
        $this->flash('Request received. We will be in touch to set up ' . Plans::name($plan) . ' (' . Plans::priceLabel($plan) . ').', '/members/billing');
    }

    /* ----------------------------------------------------------- helpers */

    /** @return array<string,mixed>|null */
    private function latestIntake(): ?array
    {
        return Database::first(
            'SELECT * FROM intakes WHERE account_id = :id ORDER BY submitted_at DESC, id DESC LIMIT 1',
            ['id' => $this->account['id']],
        );
    }

    /** @return array<string,mixed> */
    private function findLead(int $id): array
    {
        $lead = Database::first('SELECT * FROM leads WHERE id = :id AND account_id = :a', ['id' => $id, 'a' => $this->account['id']]);
        if ($lead === null) {
            $this->flash('That lead was not found.', '/members/leads');
        }
        return $lead;
    }

    /** @param array<string,mixed> $data */
    private function render(string $view, array $data): void
    {
        echo View::members($view, $data + [
            'account' => $this->account,
            'user' => Auth::user(),
            'flash' => View::flash('members_flash'),
            'support' => (string) Config::get('support_email', ''),
        ]);
    }

    private function csrf(string $back): void
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $this->flash('Your session expired. Please try again.', $back);
        }
    }

    private function flash(string $message, string $to): never
    {
        $_SESSION['members_flash'] = $message;
        Request::redirect($to);
    }
}
