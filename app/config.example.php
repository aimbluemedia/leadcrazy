<?php

declare(strict_types=1);

/**
 * Copy to app/config.php and fill in. config.php is gitignored -- never commit
 * real credentials.
 */
return [
    'app_name' => 'LeadCrazy',
    'app_url'  => 'https://leadcrazy.com',
    'debug'    => false,
    'timezone' => 'America/Phoenix',

    // Signs the lead form token. Generate once and keep it:
    //   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
    // Changing it only invalidates lead forms open in a browser at that moment.
    'app_key' => '',

    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'leadcrazy',
        'username' => '',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    // Shown on the site and in the members area for help and page changes.
    'support_email' => 'support@leadcrazy.com',

    // Cloudflare Turnstile -- the "I am not a robot" check on every lead form.
    // Create a widget at dash.cloudflare.com > Turnstile. Add leadcrazy.com as
    // a hostname; for $49 embeds, either list each member's domain or leave the
    // hostname check off in Cloudflare (the form itself is served from
    // leadcrazy.com inside an iframe, so leadcrazy.com alone is usually enough).
    // Leave both empty to run without it (honeypot + timing + rate limit only).
    'turnstile' => [
        'site_key'   => '',
        'secret_key' => '',
    ],

    // Stripe, for the $19 and $49 monthly plans.
    //
    // 1. Create two Products in Stripe, each with a monthly recurring Price:
    //    "LeadCrazy Pro" $19/month and "LeadCrazy Premium" $49/month.
    // 2. Paste each Price id (price_...) below.
    // 3. Add a webhook endpoint: https://leadcrazy.com/webhooks/stripe with
    //    events checkout.session.completed and customer.subscription.*
    //    and paste its signing secret (whsec_...).
    // 4. Turn on the Customer Portal (Settings > Billing > Customer portal).
    //
    // With no secret_key, paid signups are recorded as requests and superadmin
    // sets the plan by hand.
    'stripe' => [
        'secret_key'     => '',
        'webhook_secret' => '',
        'prices' => [
            'pro'     => '',   // $19/mo
            'premium' => '',   // $49/mo
        ],
    ],

    // The JSON feed MonsterList reads: /feed/monsterlist?key=...
    // Set a long random key and give the same value to MonsterList. Empty
    // means the feed is open to anyone, which is acceptable (every page in it
    // is public anyway) but invites scraping.
    'monsterlist' => [
        'feed_key' => '',
        'site_url' => 'https://monsterlist.com',
    ],
];
