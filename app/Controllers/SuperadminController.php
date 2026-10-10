<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Audit;
use App\Support\Auth;
use App\Support\Billing;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\FormToken;
use App\Support\Pages;
use App\Support\Plans;
use App\Support\Request;
use App\Support\Slug;
use App\Support\Uploads;
use App\Support\View;

/**
 * LeadCrazy staff. Builds every business's page from its intake, and keeps an
 * eye on accounts, leads and change requests.
 */
final class SuperadminController
{
    public function __construct()
    {
        Auth::requireStaff();
    }

    public function overview(): void
    {
        $stats = Database::first(
            "SELECT COUNT(*) AS accounts,
                    SUM(plan = 'free') AS free, SUM(plan = 'pro') AS pro, SUM(plan = 'premium') AS premium,
                    SUM(page_status = 'live') AS live, SUM(page_status IN ('intake','building')) AS queue
               FROM accounts"
        ) ?? [];
        $leads = Database::first(
            'SELECT COUNT(*) AS total, SUM(created_at >= :d) AS week FROM leads',
            ['d' => date('Y-m-d H:i:s', strtotime('-7 days'))],
        ) ?? [];

        $this->render('superadmin/overview', [
            'title' => 'Overview',
            'current' => 'overview',
            'stats' => $stats,
            'leadStats' => $leads,
            'queue' => Database::all(
                "SELECT a.*, (SELECT MAX(submitted_at) FROM intakes i WHERE i.account_id = a.id) AS intake_at
                   FROM accounts a WHERE a.page_status IN ('intake','building')
               ORDER BY (intake_at IS NULL), intake_at, a.created_at LIMIT 50"
            ),
            'requested' => Database::all(
                'SELECT * FROM accounts WHERE requested_plan IS NOT NULL ORDER BY updated_at DESC LIMIT 50'
            ),
            'openRequests' => (int) (Database::first("SELECT COUNT(*) AS n FROM change_requests WHERE status = 'open'")['n'] ?? 0),
            'mrr' => (int) ($stats['pro'] ?? 0) * Plans::price(Plans::PRO) + (int) ($stats['premium'] ?? 0) * Plans::price(Plans::PREMIUM),
        ]);
    }

    public function accounts(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $params = [];
        $where = '1 = 1';
        if ($q !== '') {
            $where = '(a.business_name LIKE :q OR a.slug LIKE :q OR u.email LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }

        $this->render('superadmin/accounts', [
            'title' => 'Accounts',
            'current' => 'accounts',
            'q' => $q,
            'accounts' => Database::all(
                "SELECT a.*, MIN(u.email) AS email,
                        (SELECT COUNT(*) FROM leads l WHERE l.account_id = a.id) AS lead_count
                   FROM accounts a
              LEFT JOIN account_users au ON au.account_id = a.id
              LEFT JOIN users u ON u.id = au.user_id
                  WHERE {$where}
               GROUP BY a.id
               ORDER BY a.created_at DESC LIMIT 200",
                $params,
            ),
        ]);
    }

    /** The page builder for one account. */
    public function account(): void
    {
        $account = $this->findAccount((int) ($_GET['id'] ?? 0));
        $id = (int) $account['id'];
        $intake = Database::first('SELECT * FROM intakes WHERE account_id = :id ORDER BY submitted_at DESC, id DESC LIMIT 1', ['id' => $id]);

        $this->render('superadmin/account', [
            'title' => $account['business_name'],
            'current' => 'accounts',
            'account' => $account,
            'page' => Database::first('SELECT * FROM pages WHERE account_id = :id', ['id' => $id]) ?? [],
            'offers' => Database::all('SELECT * FROM offers WHERE account_id = :id ORDER BY sort_order, id', ['id' => $id]),
            'gallery' => Database::all('SELECT * FROM gallery_images WHERE account_id = :id ORDER BY sort_order, id', ['id' => $id]),
            'testimonials' => Database::all('SELECT * FROM testimonials WHERE account_id = :id ORDER BY sort_order, id', ['id' => $id]),
            'users' => Database::all(
                'SELECT u.id, u.email, u.first_name, u.last_name, u.last_login_at FROM users u
                   JOIN account_users au ON au.user_id = u.id WHERE au.account_id = :id',
                ['id' => $id],
            ),
            'intake' => $intake !== null ? (json_decode((string) $intake['data'], true) ?: []) : null,
            'intakeAt' => $intake['submitted_at'] ?? null,
            'requests' => Database::all("SELECT * FROM change_requests WHERE account_id = :id AND status = 'open' ORDER BY created_at", ['id' => $id]),
            'leadCount' => (int) (Database::first('SELECT COUNT(*) AS n FROM leads WHERE account_id = :id', ['id' => $id])['n'] ?? 0),
            'liveSubscription' => Billing::hasLiveSubscription($account),
            'tab' => (string) ($_GET['tab'] ?? 'content'),
        ]);
    }

    /** The page as the public will see it, whatever its status. */
    public function preview(): void
    {
        $account = $this->findAccount((int) ($_GET['id'] ?? 0));
        $bundle = Pages::bundleFor($account);
        $slug = (string) $account['slug'];

        echo View::render('business/layout', $bundle + [
            'title' => 'Preview: ' . $account['business_name'],
            'mode' => 'preview',
            'form' => ['token' => FormToken::issue($slug), 'errors' => [], 'old' => [], 'thanks' => false],
            'body' => 'business/page',
        ]);
    }

    public function savePage(): void
    {
        $account = $this->postedAccount();
        $id = (int) $account['id'];
        $t = static fn (string $k, int $max = 240): ?string => ($v = mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max)) === '' ? null : $v;

        $accent = (string) ($_POST['accent_color'] ?? '');
        $fields = [
            'badge' => $t('badge', 80),
            'headline' => $t('headline'),
            'subheadline' => $t('subheadline'),
            'intro' => $t('intro', 400),
            'about' => $t('about', 8000),
            'why_title' => $t('why_title', 160),
            'why_points' => Pages::fromLines((string) ($_POST['why_points'] ?? ''), false, 20),
            'closing' => $t('closing', 4000),
            'form_title' => $t('form_title', 160),
            'form_intro' => $t('form_intro', 400),
            'cta_label' => $t('cta_label', 80),
            'phone' => $t('phone', 40),
            'public_email' => $t('public_email', 254),
            'website' => $t('website', 300),
            'city' => $t('city', 80),
            'state' => $t('state', 40),
            'video_url' => $t('video_url', 300),
            'stat_rating' => $t('stat_rating', 20),
            'stat_projects' => $t('stat_projects', 20),
            'stat_response' => $t('stat_response', 20),
            'services' => Pages::fromLines((string) ($_POST['services'] ?? ''), true, 60),
            'locations' => Pages::fromLines((string) ($_POST['locations'] ?? ''), true, 100),
            'zipcodes' => Pages::fromLines((string) ($_POST['zipcodes'] ?? ''), true, 300),
            'form_services' => Pages::fromLines((string) ($_POST['form_services'] ?? ''), false, 40),
            'form_timelines' => Pages::fromLines((string) ($_POST['form_timelines'] ?? ''), false, 10),
            'form_budgets' => Pages::fromLines((string) ($_POST['form_budgets'] ?? ''), false, 12),
            'accent_color' => preg_match('/^#[0-9a-fA-F]{6}$/', $accent) ? strtolower($accent) : null,
        ];

        $columns = array_keys($fields);
        Database::run(
            'INSERT INTO pages (account_id, ' . implode(', ', $columns) . ')
             VALUES (:account_id, :' . implode(', :', $columns) . ')
             ON DUPLICATE KEY UPDATE ' . implode(', ', array_map(static fn ($c) => "{$c} = VALUES({$c})", $columns)),
            ['account_id' => $id] + $fields,
        );

        Audit::log('page.saved', 'account', $id);
        $this->back($id, 'Page saved.', 'content');
    }

    /** Name, slug, plan and status. */
    public function saveSettings(): void
    {
        $account = $this->postedAccount();
        $id = (int) $account['id'];

        $name = mb_substr(trim((string) ($_POST['business_name'] ?? '')), 0, 160);
        $slug = Slug::from((string) ($_POST['slug'] ?? ''));
        $plan = (string) ($_POST['plan'] ?? $account['plan']);
        $status = (string) ($_POST['page_status'] ?? $account['page_status']);

        if ($name === '') {
            $this->back($id, 'The business needs a name.', 'settings');
        }
        if (!Slug::isValid($slug)) {
            $this->back($id, 'That page address is not allowed. Use letters, numbers and dashes, and avoid reserved words.', 'settings');
        }
        if (Slug::taken($slug, $id)) {
            $this->back($id, 'Another business already has /' . $slug . '.', 'settings');
        }
        if (!Plans::isValid($plan) || !in_array($status, ['intake', 'building', 'live', 'paused'], true)) {
            $this->back($id, 'Choose a plan and a status.', 'settings');
        }
        // Stripe owns the plan of a paying account. Changing it here would leave
        // the card charged for one plan while the page runs on another.
        if ($plan !== $account['plan'] && Billing::hasLiveSubscription($account)) {
            $this->back($id, 'This account has a live Stripe subscription. Change the plan in Stripe; it will sync back here.', 'settings');
        }

        // Decided here rather than in SQL: MySQL applies SET left to right, so
        // an IF() on `plan` after `plan = :p` would compare against the new value.
        $planChanged = $plan !== $account['plan'];
        Database::run(
            'UPDATE accounts SET business_name = :n, slug = :s, plan = :p, page_status = :st'
            . ($planChanged ? ', requested_plan = NULL, plan_changed_at = NOW()' : '')
            . ' WHERE id = :id',
            ['n' => $name, 's' => $slug, 'p' => $plan, 'st' => $status, 'id' => $id],
        );
        if ($status === 'live') {
            Database::run('UPDATE pages SET published_at = COALESCE(published_at, NOW()) WHERE account_id = :id', ['id' => $id]);
        }

        Audit::log('account.settings', 'account', $id,
            ['slug' => $account['slug'], 'plan' => $account['plan'], 'status' => $account['page_status']],
            ['slug' => $slug, 'plan' => $plan, 'status' => $status]);
        $this->back($id, 'Settings saved.', 'settings');
    }

    /** Logo or hero banner. */
    public function uploadImage(): void
    {
        $account = $this->postedAccount();
        $id = (int) $account['id'];
        $which = ($_POST['which'] ?? '') === 'logo' ? 'logo_path' : 'hero_path';
        $page = Database::first('SELECT * FROM pages WHERE account_id = :id', ['id' => $id]) ?? [];

        if (!empty($_POST['remove'])) {
            Database::run("UPDATE pages SET {$which} = NULL WHERE account_id = :id", ['id' => $id]);
            $this->back($id, 'Image removed.', 'media');
        }

        $saved = Uploads::image($_FILES['image'] ?? [], $id);
        if (!isset($saved['path'])) {
            $this->back($id, (string) $saved['error'], 'media');
        }
        Database::run(
            "INSERT INTO pages (account_id, {$which}) VALUES (:id, :p) ON DUPLICATE KEY UPDATE {$which} = VALUES({$which})",
            ['id' => $id, 'p' => $saved['path']],
        );
        // The old file goes only if no gallery image points at it as well.
        $old = $page[$which] ?? null;
        if ($old && Database::first('SELECT id FROM gallery_images WHERE path = :p', ['p' => $old]) === null) {
            Uploads::delete($old);
        }
        $this->back($id, 'Image updated.', 'media');
    }

    public function addGallery(): void
    {
        $account = $this->postedAccount();
        $id = (int) $account['id'];
        $files = Uploads::many('photos');
        if ($files === []) {
            $this->back($id, 'Choose one or more photos.', 'media');
        }

        $order = (int) (Database::first('SELECT COALESCE(MAX(sort_order), 0) AS n FROM gallery_images WHERE account_id = :id', ['id' => $id])['n'] ?? 0);
        $added = 0;
        $errors = [];
        foreach ($files as $file) {
            $saved = Uploads::image($file, $id);
            if (!isset($saved['path'])) {
                $errors[] = $file['name'] . ': ' . $saved['error'];
                continue;
            }
            Database::run(
                'INSERT INTO gallery_images (account_id, path, caption, sort_order) VALUES (:id, :p, NULL, :o)',
                ['id' => $id, 'p' => $saved['path'], 'o' => ++$order],
            );
            $added++;
        }
        $this->back($id, $added . ' photo(s) added.' . ($errors ? ' Skipped: ' . implode('; ', $errors) : ''), 'media');
    }

    public function deleteGallery(): void
    {
        $account = $this->postedAccount();
        $id = (int) $account['id'];
        $row = Database::first('SELECT * FROM gallery_images WHERE id = :g AND account_id = :id', ['g' => (int) ($_POST['image_id'] ?? 0), 'id' => $id]);
        if ($row !== null) {
            Database::run('DELETE FROM gallery_images WHERE id = :g', ['g' => $row['id']]);
            $inUse = Database::first('SELECT account_id FROM pages WHERE logo_path = :p OR hero_path = :p2', ['p' => $row['path'], 'p2' => $row['path']]);
            if ($inUse === null) {
                Uploads::delete((string) $row['path']);
            }
        }
        $this->back($id, 'Photo removed.', 'media');
    }

    public function saveOffer(): void
    {
        $account = $this->postedAccount();
        $id = (int) $account['id'];
        $offerId = (int) ($_POST['offer_id'] ?? 0);
        $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 120);
        if ($title === '') {
            $this->back($id, 'An offer needs a title.', 'offers');
        }
        $expires = (string) ($_POST['expires_on'] ?? '');
        $params = [
            'label' => mb_substr(trim((string) ($_POST['label'] ?? '')), 0, 40) ?: null,
            'title' => $title,
            'body' => mb_substr(trim((string) ($_POST['body'] ?? '')), 0, 4000) ?: null,
            'expires' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $expires) ? $expires : null,
            'sort' => (int) ($_POST['sort_order'] ?? 0),
            'active' => empty($_POST['is_active']) ? 0 : 1,
            'id' => $id,
        ];

        if ($offerId > 0) {
            Database::run(
                'UPDATE offers SET label = :label, title = :title, body = :body, expires_on = :expires,
                        sort_order = :sort, is_active = :active
                  WHERE id = :oid AND account_id = :id',
                $params + ['oid' => $offerId],
            );
        } else {
            Database::run(
                'INSERT INTO offers (account_id, label, title, body, expires_on, sort_order, is_active)
                 VALUES (:id, :label, :title, :body, :expires, :sort, :active)',
                $params,
            );
        }
        $this->back($id, 'Offer saved.', 'offers');
    }

    public function deleteOffer(): void
    {
        $account = $this->postedAccount();
        Database::run('DELETE FROM offers WHERE id = :o AND account_id = :id', ['o' => (int) ($_POST['offer_id'] ?? 0), 'id' => $account['id']]);
        $this->back((int) $account['id'], 'Offer deleted.', 'offers');
    }

    public function saveTestimonial(): void
    {
        $account = $this->postedAccount();
        $id = (int) $account['id'];
        $tid = (int) ($_POST['testimonial_id'] ?? 0);
        $author = mb_substr(trim((string) ($_POST['author'] ?? '')), 0, 120);
        $body = mb_substr(trim((string) ($_POST['body'] ?? '')), 0, 4000);
        if ($author === '' || $body === '') {
            $this->back($id, 'A testimonial needs a name and the words.', 'testimonials');
        }
        $params = [
            'author' => $author,
            'location' => mb_substr(trim((string) ($_POST['location'] ?? '')), 0, 120) ?: null,
            'rating' => max(1, min(5, (int) ($_POST['rating'] ?? 5))),
            'body' => $body,
            'sort' => (int) ($_POST['sort_order'] ?? 0),
            'id' => $id,
        ];
        if ($tid > 0) {
            Database::run(
                'UPDATE testimonials SET author = :author, location = :location, rating = :rating, body = :body, sort_order = :sort
                  WHERE id = :tid AND account_id = :id',
                $params + ['tid' => $tid],
            );
        } else {
            Database::run(
                'INSERT INTO testimonials (account_id, author, location, rating, body, sort_order)
                 VALUES (:id, :author, :location, :rating, :body, :sort)',
                $params,
            );
        }
        $this->back($id, 'Testimonial saved.', 'testimonials');
    }

    public function deleteTestimonial(): void
    {
        $account = $this->postedAccount();
        Database::run('DELETE FROM testimonials WHERE id = :t AND account_id = :id', ['t' => (int) ($_POST['testimonial_id'] ?? 0), 'id' => $account['id']]);
        $this->back((int) $account['id'], 'Testimonial deleted.', 'testimonials');
    }

    /** The way back into an account while there is no password-reset email. */
    public function setTempPassword(): void
    {
        $account = $this->postedAccount();
        $userId = (int) ($_POST['user_id'] ?? 0);
        $linked = Database::first('SELECT user_id FROM account_users WHERE account_id = :a AND user_id = :u', ['a' => $account['id'], 'u' => $userId]);
        if ($linked === null) {
            $this->back((int) $account['id'], 'That user is not on this account.', 'settings');
        }

        // Readable aloud over the phone: no 0/O or 1/l.
        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        $temp = '';
        for ($i = 0; $i < 12; $i++) {
            $temp .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $temp = implode('-', str_split($temp, 4));

        Auth::setTemporaryPassword($userId, $temp);
        $this->back((int) $account['id'], 'Temporary password: ' . $temp . ' -- give it to the member; they must change it when they sign in.', 'settings');
    }

    /**
     * The tail of storage/logs/error.log, newest first, so an error reference
     * shown to a visitor can be looked up from the browser instead of the
     * host's file manager.
     */
    public function errors(): void
    {
        $file = BASE_PATH . '/storage/logs/error.log';
        $entries = [];
        if (is_file($file)) {
            $size = (int) filesize($file);
            $fh = fopen($file, 'rb');
            if ($fh !== false) {
                fseek($fh, max(0, $size - 400000));
                $tail = (string) stream_get_contents($fh);
                fclose($fh);
                foreach (preg_split('/\R{2,}/', trim($tail)) ?: [] as $chunk) {
                    if (preg_match('/^\[[0-9: -]{19}\]/', $chunk)) {
                        $entries[] = $chunk;
                    }
                }
            }
        }
        $ref = strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', (string) ($_GET['ref'] ?? '')) ?? '');
        if ($ref !== '') {
            $entries = array_values(array_filter($entries, static fn ($e) => str_contains($e, $ref)));
        }

        $this->render('superadmin/errors', [
            'title' => 'Errors',
            'current' => 'errors',
            'entries' => array_slice(array_reverse($entries), 0, 50),
            'ref' => $ref,
            'exists' => is_file($file),
            'appKeyMissing' => trim((string) \App\Support\Config::get('app_key', '')) === '',
        ]);
    }

    public function leads(): void
    {
        $this->render('superadmin/leads', [
            'title' => 'All leads',
            'current' => 'leads',
            'leads' => Database::all(
                'SELECT l.id, l.first_name, l.last_name, l.city, l.zip, l.status, l.source, l.is_locked, l.created_at,
                        a.business_name, a.id AS account_id, a.plan
                   FROM leads l JOIN accounts a ON a.id = l.account_id
               ORDER BY l.created_at DESC LIMIT 300'
            ),
        ]);
    }

    public function requests(): void
    {
        $this->render('superadmin/requests', [
            'title' => 'Change requests',
            'current' => 'requests',
            'requests' => Database::all(
                "SELECT r.*, a.business_name FROM change_requests r JOIN accounts a ON a.id = r.account_id
                  ORDER BY r.status = 'done', r.created_at DESC LIMIT 200"
            ),
        ]);
    }

    public function closeRequest(): void
    {
        $this->csrf('/superadmin/requests');
        Database::run(
            "UPDATE change_requests SET status = 'done', resolved_at = NOW() WHERE id = :id",
            ['id' => (int) ($_POST['request_id'] ?? 0)],
        );
        $back = (string) ($_POST['back'] ?? '');
        $_SESSION['admin_flash'] = 'Marked done.';
        Request::redirect(preg_match('#^/superadmin[a-z/]*(\?id=\d+(&tab=[a-z]+)?)?$#', $back) ? $back : '/superadmin/requests');
    }

    /* ----------------------------------------------------------- helpers */

    /** @return array<string,mixed> */
    private function findAccount(int $id): array
    {
        $account = Database::first('SELECT * FROM accounts WHERE id = :id', ['id' => $id]);
        if ($account === null) {
            $_SESSION['admin_flash'] = 'That account was not found.';
            Request::redirect('/superadmin/accounts');
        }
        return $account;
    }

    /** CSRF-checked account from a posted form. @return array<string,mixed> */
    private function postedAccount(): array
    {
        $id = (int) ($_POST['account_id'] ?? 0);
        if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $_SESSION['admin_flash'] = 'That upload was larger than the server accepts (post_max_size). Try fewer or smaller photos.';
            Request::redirect('/superadmin/accounts');
        }
        $this->csrf('/superadmin/account?id=' . $id);
        return $this->findAccount($id);
    }

    private function csrf(string $back): void
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $_SESSION['admin_flash'] = 'Your session expired. Please try again.';
            Request::redirect($back);
        }
    }

    private function back(int $id, string $message, string $tab): never
    {
        $_SESSION['admin_flash'] = $message;
        Request::redirect('/superadmin/account?id=' . $id . '&tab=' . $tab);
    }

    /** @param array<string,mixed> $data */
    private function render(string $view, array $data): void
    {
        echo View::superadmin($view, $data + [
            'user' => Auth::user(),
            'flash' => View::flash('admin_flash'),
        ]);
    }
}
