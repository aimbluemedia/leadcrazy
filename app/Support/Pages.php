<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Reading and writing a business's lead page.
 */
final class Pages
{
    public const DEFAULT_TIMELINES = [
        'As soon as possible',
        'Within 1-3 months',
        'Within 3-6 months',
        'Just planning/researching',
    ];

    public const DEFAULT_BUDGETS = [
        'Under $5,000',
        '$5,000 - $10,000',
        '$10,000 - $25,000',
        '$25,000 - $50,000',
        '$50,000+',
    ];

    /**
     * Everything needed to draw a page, or null when there is no such business.
     * Status is NOT checked here: the caller decides whether a page that is not
     * live may be shown (superadmin preview may, the public may not).
     *
     * @return array<string,mixed>|null
     */
    public static function bundle(string $slug): ?array
    {
        $account = Database::first('SELECT * FROM accounts WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
        return $account === null ? null : self::bundleFor($account);
    }

    /** @param array<string,mixed> $account @return array<string,mixed> */
    public static function bundleFor(array $account): array
    {
        $id = (int) $account['id'];
        $page = Database::first('SELECT * FROM pages WHERE account_id = :id', ['id' => $id]) ?? ['account_id' => $id];

        $offers = Database::all(
            'SELECT * FROM offers
              WHERE account_id = :id AND is_active = 1
                AND (expires_on IS NULL OR expires_on >= :today)
           ORDER BY sort_order, id',
            ['id' => $id, 'today' => date('Y-m-d')],
        );

        return [
            'account' => $account,
            'page' => $page,
            'offers' => $offers,
            'gallery' => Database::all('SELECT * FROM gallery_images WHERE account_id = :id ORDER BY sort_order, id', ['id' => $id]),
            'testimonials' => Database::all('SELECT * FROM testimonials WHERE account_id = :id ORDER BY sort_order, id', ['id' => $id]),
            'services' => self::list($page['services'] ?? null),
            'locations' => self::list($page['locations'] ?? null),
            'zipcodes' => self::list($page['zipcodes'] ?? null),
            'whyPoints' => self::list($page['why_points'] ?? null),
            'formServices' => self::list($page['form_services'] ?? null) ?: self::list($page['services'] ?? null),
            'timelines' => self::list($page['form_timelines'] ?? null) ?: self::DEFAULT_TIMELINES,
            'budgets' => self::list($page['form_budgets'] ?? null) ?: self::DEFAULT_BUDGETS,
            'full' => Plans::fullPage((string) $account['plan']),
        ];
    }

    /** @param array<string,mixed> $account */
    public static function isPublic(array $account): bool
    {
        return $account['page_status'] === 'live';
    }

    /** A stored JSON list as strings. Bad JSON reads as empty rather than throwing. @return list<string> */
    public static function list(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $value = json_decode($json, true);
        if (!is_array($value)) {
            return [];
        }
        return array_values(array_filter(array_map(
            static fn ($v) => is_scalar($v) ? trim((string) $v) : '',
            $value,
        ), static fn ($v) => $v !== ''));
    }

    /**
     * One-per-line textarea input to a stored JSON list. Commas also split, for
     * zip codes and cities pasted as "85001, 85002, 85003".
     */
    public static function fromLines(?string $text, bool $splitCommas = false, int $max = 200): string
    {
        $pattern = $splitCommas ? '/[\r\n,]+/' : '/[\r\n]+/';
        $items = preg_split($pattern, (string) $text) ?: [];
        $items = array_values(array_unique(array_filter(
            array_map(static fn ($v) => mb_substr(trim($v), 0, 160), $items),
            static fn ($v) => $v !== '',
        )));

        return json_encode(array_slice($items, 0, $max), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    /** Stored JSON list back to textarea lines. */
    public static function toLines(?string $json): string
    {
        return implode("\n", self::list($json));
    }

    /** A YouTube or Vimeo URL as an embeddable player URL, or null. */
    public static function videoEmbed(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        return null;
    }

    /** The public URL of a business page. */
    public static function url(string $slug): string
    {
        return View::url('/' . $slug);
    }
}
