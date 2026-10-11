<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The emails LeadCrazy sends about things that happen: a new lead, a page
 * going live, a new signup for the team.
 *
 * Sent after the response has gone to the browser (see later()), so a slow
 * or unreachable mail server never makes a visitor wait or breaks a lead
 * form. A failed send is logged by Mailer and otherwise ignored: the lead is
 * already saved and visible in the dashboard either way.
 */
final class Notify
{
    /** @var list<callable> */
    private static array $queue = [];

    /** Runs $job after the response is sent. */
    public static function later(callable $job): void
    {
        if (self::$queue === []) {
            register_shutdown_function([self::class, 'flush']);
        }
        self::$queue[] = $job;
    }

    /** @internal shutdown handler */
    public static function flush(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        if (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        } elseif (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        foreach (self::$queue as $job) {
            try {
                $job();
            } catch (\Throwable $e) {
                ErrorHandler::note('notify', $e->getMessage());
            }
        }
        self::$queue = [];
    }

    /* ------------------------------------------------------------ to members */

    /** @param array<string,mixed> $account */
    public static function newLead(array $account, int $leadId): void
    {
        self::later(static function () use ($account, $leadId): void {
            $lead = Database::first('SELECT * FROM leads WHERE id = :id', ['id' => $leadId]);
            if ($lead === null) {
                return;
            }
            $link = View::url('/members/lead?id=' . $leadId);
            $name = trim($lead['first_name'] . ' ' . ($lead['last_name'] ?? ''));

            if (!Leads::visible($lead, $account)) {
                $subject = 'You have a new lead waiting';
                $text = "Good news - " . $account['business_name'] . " just got a new quote request.\n\n"
                    . 'Your Free plan includes ' . Plans::FREE_MONTHLY_LEADS . " leads a month and you have used them all, so this one is saved and locked.\n"
                    . "Upgrade to Pro to see who it is and how to reach them - plus every lead after it:\n"
                    . View::url('/members/billing') . "\n\n- The LeadCrazy team\n";
            } else {
                $services = Leads::services($lead['services']);
                $subject = 'New lead: ' . $name . ($services ? ' - ' . implode(', ', array_slice($services, 0, 2)) : '');
                $rows = [
                    'Name' => $name,
                    'Phone' => $lead['phone'],
                    'Email' => $lead['email'],
                    'City / Zip' => trim(($lead['city'] ?? '') . ' ' . ($lead['zip'] ?? '')),
                    'Services' => implode(', ', $services),
                    'Start' => $lead['timeline'],
                    'Budget' => $lead['budget'],
                    'Offer' => $lead['offer'],
                ];
                $text = $account['business_name'] . " has a new quote request.\n\n";
                foreach ($rows as $label => $value) {
                    if (trim((string) $value) !== '') {
                        $text .= str_pad($label . ':', 12) . ' ' . $value . "\n";
                    }
                }
                if (trim((string) $lead['message']) !== '') {
                    $text .= "\nProject:\n" . trim((string) $lead['message']) . "\n";
                }
                $text .= "\nThe sooner you call, the more likely you win the job.\n"
                    . "View and track this lead: " . $link . "\n\n- LeadCrazy\n";
            }
            foreach (self::ownerEmails((int) $account['id']) as $to) {
                Mailer::send($to, $subject, $text);
            }
        });
    }

    /** @param array<string,mixed> $account */
    public static function pageLive(array $account): void
    {
        self::later(static function () use ($account): void {
            $url = Pages::url((string) $account['slug']);
            $text = "Great news - your LeadCrazy page for " . $account['business_name'] . " has been approved and is now live:\n\n"
                . $url . "\n\n"
                . "Share it everywhere your customers look:\n"
                . "  - Your Google Business Profile (website or booking link)\n"
                . "  - Facebook, Instagram and Nextdoor\n"
                . "  - Text messages, email signatures, flyers and truck wraps\n\n"
                . "Every quote request lands in your dashboard and we will email you each one:\n"
                . View::url('/members') . "\n\n- The LeadCrazy team\n";
            foreach (self::ownerEmails((int) $account['id']) as $to) {
                Mailer::send($to, 'Your LeadCrazy page is live', $text);
            }
        });
    }

    /* ------------------------------------------------------------ to the team */

    /** @param array<string,string> $details */
    public static function team(string $subject, int $accountId, array $details): void
    {
        $to = self::teamEmail();
        if ($to === '') {
            return;
        }
        self::later(static function () use ($to, $subject, $accountId, $details): void {
            $text = '';
            foreach ($details as $label => $value) {
                if (trim($value) !== '') {
                    $text .= str_pad($label . ':', 10) . ' ' . $value . "\n";
                }
            }
            $text .= "\nReview it: " . View::url('/superadmin/account?id=' . $accountId) . "\n";
            Mailer::send($to, $subject, $text);
        });
    }

    public static function teamEmail(): string
    {
        $to = trim((string) Config::get('notify_email', '')) ?: trim((string) Config::get('support_email', ''));
        return filter_var($to, FILTER_VALIDATE_EMAIL) ? $to : '';
    }

    /** @return list<string> */
    private static function ownerEmails(int $accountId): array
    {
        return array_map(static fn ($r) => (string) $r['email'], Database::all(
            "SELECT u.email FROM users u JOIN account_users au ON au.user_id = u.id
              WHERE au.account_id = :a AND u.status = 'active'",
            ['a' => $accountId],
        ));
    }
}
