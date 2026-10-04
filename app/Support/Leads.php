<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Leads: storing them, and the Free plan's monthly allowance.
 *
 * Times are written from PHP rather than left to MySQL's CURRENT_TIMESTAMP, so
 * "this month" means the same thing on insert and on read. Shared hosts often
 * run MySQL on UTC while the site runs on America/Phoenix, and a lead at 6pm on
 * the 31st must not count toward next month.
 */
final class Leads
{
    public const STATUSES = ['new', 'contacted', 'quoted', 'won', 'lost'];

    /** @param array<string,mixed> $data @param array<string,mixed> $account */
    public static function create(array $account, array $data, string $source): int
    {
        $locked = Plans::hasLeadCap((string) $account['plan'])
            && self::countThisMonth((int) $account['id']) >= Plans::FREE_MONTHLY_LEADS;

        Database::run(
            'INSERT INTO leads (account_id, first_name, last_name, email, phone, city, zip, services,
                                timeline, budget, offer, message, source, referrer, ip, user_agent,
                                is_locked, created_at)
             VALUES (:account_id, :first_name, :last_name, :email, :phone, :city, :zip, :services,
                     :timeline, :budget, :offer, :message, :source, :referrer, :ip, :user_agent,
                     :is_locked, :created_at)',
            [
                'account_id' => (int) $account['id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'city' => $data['city'],
                'zip' => $data['zip'],
                'services' => json_encode($data['services'], JSON_UNESCAPED_UNICODE),
                'timeline' => $data['timeline'],
                'budget' => $data['budget'],
                'offer' => $data['offer'],
                'message' => $data['message'],
                'source' => $source === 'embed' ? 'embed' : 'hosted',
                'referrer' => Request::referer(),
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'is_locked' => $locked ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        );

        return (int) Database::connection()->lastInsertId();
    }

    public static function monthStart(): string
    {
        return date('Y-m-01 00:00:00');
    }

    public static function countThisMonth(int $accountId): int
    {
        $row = Database::first(
            'SELECT COUNT(*) AS n FROM leads WHERE account_id = :id AND created_at >= :start',
            ['id' => $accountId, 'start' => self::monthStart()],
        );
        return (int) ($row['n'] ?? 0);
    }

    /**
     * True when the member may see this lead's contact details. A lead locked on
     * Free opens the moment the account is on a paid plan: the flag records that
     * it arrived over the allowance, not that it is hidden forever.
     *
     * @param array<string,mixed> $lead @param array<string,mixed> $account
     */
    public static function visible(array $lead, array $account): bool
    {
        return (int) $lead['is_locked'] === 0 || !Plans::hasLeadCap((string) $account['plan']);
    }

    /** @return list<string> */
    public static function services(?string $json): array
    {
        return Pages::list($json);
    }
}
