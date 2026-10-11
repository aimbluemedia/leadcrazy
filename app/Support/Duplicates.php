<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Does this business already have a LeadCrazy profile?
 *
 * Checked on three things, any one of which is a match:
 *  - email:    the login email is already registered
 *  - business: the same name once case, punctuation and endings like
 *              "LLC", "Inc" or "Co" are ignored ("Storm Landscaping, LLC"
 *              matches "storm landscaping")
 *  - domain:   the business website's domain, or a company email domain,
 *              matches another account's website or company email domain.
 *              Free mailbox providers (gmail.com and friends) never count.
 *
 * Callers show one generic "you already have a profile" message rather than
 * which field matched, so this cannot be used to look up who is registered.
 */
final class Duplicates
{
    private const FREE_MAIL = [
        'gmail.com', 'googlemail.com', 'yahoo.com', 'ymail.com', 'hotmail.com', 'outlook.com', 'live.com', 'msn.com',
        'aol.com', 'icloud.com', 'me.com', 'mac.com', 'comcast.net', 'att.net', 'sbcglobal.net', 'verizon.net',
        'cox.net', 'charter.net', 'protonmail.com', 'proton.me', 'gmx.com', 'mail.com', 'zoho.com', 'yandex.com',
    ];

    private const NAME_NOISE = ['llc', 'l', 'inc', 'incorporated', 'co', 'corp', 'corporation', 'company', 'ltd', 'limited', 'the', 'pllc', 'lp'];

    /** @return list<string> Which of 'email', 'business', 'domain' matched. */
    public static function find(?string $email, ?string $business, ?string $website): array
    {
        $found = [];
        $email = mb_strtolower(trim((string) $email));

        if ($email !== '' && Accounts::emailTaken($email)) {
            $found[] = 'email';
        }

        $key = self::nameKey((string) $business);
        if ($key !== '') {
            foreach (Database::all('SELECT business_name FROM accounts') as $row) {
                if (self::nameKey((string) $row['business_name']) === $key) {
                    $found[] = 'business';
                    break;
                }
            }
        }

        $domains = array_values(array_filter(array_unique([self::host($website), self::emailDomain($email)])));
        if ($domains !== [] && self::domainTaken($domains)) {
            $found[] = 'domain';
        }

        return $found;
    }

    /** "Storm Landscaping, L.L.C." -> "stormlandscaping" */
    public static function nameKey(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = str_replace('&', ' and ', $name);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = (string) preg_replace('/[^a-z0-9]+/', ' ', $ascii === false ? $name : $ascii);
        $words = array_filter(explode(' ', $name), static fn ($w) => $w !== '' && !in_array($w, self::NAME_NOISE, true));
        return implode('', $words);
    }

    /** "https://www.StormLandscaping.com/about" -> "stormlandscaping.com" */
    public static function host(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'http://' . $url;
        }
        $host = mb_strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = (string) preg_replace('/^www\./', '', $host);
        return str_contains($host, '.') ? $host : '';
    }

    /** Company email domain, or '' for free providers. */
    public static function emailDomain(string $email): string
    {
        $at = strrpos($email, '@');
        if ($at === false) {
            return '';
        }
        $domain = mb_strtolower(substr($email, $at + 1));
        return in_array($domain, self::FREE_MAIL, true) || !str_contains($domain, '.') ? '' : $domain;
    }

    /** @param list<string> $domains */
    private static function domainTaken(array $domains): bool
    {
        foreach (Database::all("SELECT website FROM pages WHERE website IS NOT NULL AND website <> ''") as $row) {
            if (in_array(self::host((string) $row['website']), $domains, true)) {
                return true;
            }
        }
        foreach ($domains as $domain) {
            if (in_array($domain, self::FREE_MAIL, true)) {
                continue;
            }
            if (Database::first('SELECT id FROM users WHERE is_admin = 0 AND email LIKE :d LIMIT 1', ['d' => '%@' . $domain]) !== null) {
                return true;
            }
        }
        return false;
    }
}
