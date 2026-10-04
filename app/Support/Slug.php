<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The leadcrazy.com/{slug} part of a business page.
 */
final class Slug
{
    /**
     * Single-segment paths the site uses or may use. A business named "Pricing"
     * must not get /pricing, and a business named "Members" must not get a
     * page that looks like the login.
     */
    public const RESERVED = [
        'admin', 'api', 'assets', 'billing', 'blog', 'contact', 'embed', 'feed',
        'form', 'help', 'home', 'how-it-works', 'index', 'lead', 'leadcrazy',
        'leads', 'login', 'logout', 'members', 'monsterlist', 'pricing', 'privacy',
        'public', 'signup', 'superadmin', 'support', 'terms', 'uploads', 'webhooks',
        'widget', 'www',
    ];

    public static function from(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = (string) preg_replace('/&/', ' and ', $value);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = $ascii === false ? $value : $ascii;
        $value = (string) preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim($value, '-');

        return substr($value, 0, 60);
    }

    public static function isValid(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9](?:[a-z0-9-]{0,58}[a-z0-9])?$/', $slug)
            && !in_array($slug, self::RESERVED, true);
    }

    /** A free slug based on the business name: storm-landscaping, storm-landscaping-2... */
    public static function unique(string $businessName, ?int $exceptAccountId = null): string
    {
        $base = self::from($businessName);
        if ($base === '' || in_array($base, self::RESERVED, true)) {
            $base = 'business' . ($base === '' ? '' : '-' . $base);
        }

        $candidate = $base;
        for ($n = 2; self::taken($candidate, $exceptAccountId); $n++) {
            $candidate = substr($base, 0, 55) . '-' . $n;
        }

        return $candidate;
    }

    public static function taken(string $slug, ?int $exceptAccountId = null): bool
    {
        $row = Database::first(
            'SELECT id FROM accounts WHERE slug = :slug AND id <> :id LIMIT 1',
            ['slug' => $slug, 'id' => $exceptAccountId ?? 0],
        );

        return $row !== null;
    }
}
