<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The three ways to have a Million Dollar Lead Form, and what each one may use.
 *
 * Every "can this account do X" question in the app is answered here, from
 * accounts.plan, so the rules live in one place. Billing writes the plan; it
 * never decides what a plan includes.
 */
final class Plans
{
    public const FREE    = 'free';
    public const PRO     = 'pro';
    public const PREMIUM = 'premium';

    public const ALL  = [self::FREE, self::PRO, self::PREMIUM];
    public const PAID = [self::PRO, self::PREMIUM];

    /** Leads a Free account can see per calendar month. More are kept, locked. */
    public const FREE_MONTHLY_LEADS = 10;

    /** What a Free page shows. Anything past these is kept, and appears on upgrade. */
    public const FREE_OFFERS = 1;
    public const FREE_CITIES = 5;
    public const FREE_ZIPS = 5;

    /** @var array<string,array{name:string,price:int,tagline:string,features:list<string>}> */
    public const DETAILS = [
        self::FREE => [
            'name' => 'Free',
            'price' => 0,
            'tagline' => 'Your own lead page, hosted on LeadCrazy.',
            'features' => [
                'Million Dollar Lead Form page at leadcrazy.com/your-business',
                'Your services and 1 special offer',
                'Up to 5 service cities and 5 zip codes',
                '10 leads a month (extra leads are saved for when you upgrade)',
                'LeadCrazy branding on your page',
            ],
        ],
        self::PRO => [
            'name' => 'Pro',
            'price' => 19,
            'tagline' => 'The full page, hosted on LeadCrazy and listed on MonsterList.',
            'features' => [
                'Everything in Free, with unlimited leads',
                'Unlimited offers, photo gallery and customer testimonials',
                'Unlimited service cities and zip codes, stats and video',
                'Listed on MonsterList',
                'No LeadCrazy branding',
            ],
        ],
        self::PREMIUM => [
            'name' => 'Premium',
            'price' => 49,
            'tagline' => 'Everything in Pro, on your own website too.',
            'features' => [
                'Everything in Pro',
                'Embed the full page on your own website',
                'Or embed just the lead form',
                'Works on WordPress, Wix, Squarespace and plain HTML',
                'Leads from every site in one dashboard',
            ],
        ],
    ];

    public static function isValid(string $plan): bool
    {
        return in_array($plan, self::ALL, true);
    }

    public static function isPaid(string $plan): bool
    {
        return in_array($plan, self::PAID, true);
    }

    public static function name(string $plan): string
    {
        return self::DETAILS[$plan]['name'] ?? ucfirst($plan);
    }

    public static function price(string $plan): int
    {
        return self::DETAILS[$plan]['price'] ?? 0;
    }

    /** "$19/mo", or "Free". */
    public static function priceLabel(string $plan): string
    {
        $price = self::price($plan);
        return $price === 0 ? 'Free' : '$' . $price . '/mo';
    }

    /** Offers, gallery, testimonials, zip codes, stats. */
    public static function fullPage(string $plan): bool
    {
        return self::isPaid($plan);
    }

    /** Limits a plan puts on what the page shows, or null for none. @return array{offers:int,cities:int,zips:int}|null */
    public static function pageLimits(string $plan): ?array
    {
        return $plan === self::FREE
            ? ['offers' => self::FREE_OFFERS, 'cities' => self::FREE_CITIES, 'zips' => self::FREE_ZIPS]
            : null;
    }

    public static function canEmbed(string $plan): bool
    {
        return $plan === self::PREMIUM;
    }

    public static function onMonsterList(string $plan): bool
    {
        return self::isPaid($plan);
    }

    public static function showsBranding(string $plan): bool
    {
        return $plan === self::FREE;
    }

    public static function hasLeadCap(string $plan): bool
    {
        return $plan === self::FREE;
    }
}
