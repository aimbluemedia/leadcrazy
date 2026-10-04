<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Config;
use App\Support\Database;
use App\Support\Pages;
use App\Support\Plans;
use App\Support\View;

/**
 * GET /feed/monsterlist -- every live $19 and $49 page, as JSON, for MonsterList
 * to import. MonsterList links each listing back to the page here; the lead
 * still lands in the member's LeadCrazy dashboard.
 *
 * Format (stable; add fields, never rename them):
 *   { "generated_at": ISO-8601, "count": n, "businesses": [ {
 *       "id", "slug", "name", "url", "plan", "headline", "subheadline",
 *       "city", "state", "phone", "logo", "image", "rating", "projects",
 *       "services": [], "locations": [], "zipcodes": [],
 *       "offers": [ { "title", "expires_on" } ], "updated_at" } ] }
 */
final class FeedController
{
    public function monsterList(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        $key = trim((string) Config::get('monsterlist.feed_key', ''));
        $given = (string) ($_GET['key'] ?? ($_SERVER['HTTP_X_FEED_KEY'] ?? ''));
        if ($key !== '' && !hash_equals($key, $given)) {
            http_response_code(403);
            echo json_encode(['error' => 'A valid feed key is required.']);
            return;
        }

        header('Cache-Control: public, max-age=300');

        $rows = Database::all(
            "SELECT a.id, a.slug, a.business_name, a.plan, a.updated_at AS account_updated,
                    p.headline, p.subheadline, p.city, p.state, p.phone, p.logo_path, p.hero_path,
                    p.stat_rating, p.stat_projects, p.services, p.locations, p.zipcodes,
                    p.updated_at AS page_updated
               FROM accounts a
               JOIN pages p ON p.account_id = a.id
              WHERE a.page_status = 'live' AND a.plan IN ('pro','premium')
           ORDER BY a.business_name"
        );

        $offers = [];
        foreach (Database::all(
            "SELECT o.account_id, o.title, o.expires_on FROM offers o
               JOIN accounts a ON a.id = o.account_id
              WHERE o.is_active = 1 AND (o.expires_on IS NULL OR o.expires_on >= :today)
                AND a.page_status = 'live' AND a.plan IN ('pro','premium')
           ORDER BY o.sort_order, o.id",
            ['today' => date('Y-m-d')],
        ) as $o) {
            $offers[(int) $o['account_id']][] = ['title' => $o['title'], 'expires_on' => $o['expires_on']];
        }

        $abs = static fn (?string $path): ?string => $path ? View::url($path) : null;
        $out = [];
        foreach ($rows as $r) {
            if (!Plans::onMonsterList((string) $r['plan'])) {
                continue;
            }
            $out[] = [
                'id' => (int) $r['id'],
                'slug' => $r['slug'],
                'name' => $r['business_name'],
                'url' => Pages::url((string) $r['slug']),
                'plan' => $r['plan'],
                'headline' => $r['headline'],
                'subheadline' => $r['subheadline'],
                'city' => $r['city'],
                'state' => $r['state'],
                'phone' => $r['phone'],
                'logo' => $abs($r['logo_path']),
                'image' => $abs($r['hero_path']),
                'rating' => $r['stat_rating'],
                'projects' => $r['stat_projects'],
                'services' => Pages::list($r['services']),
                'locations' => Pages::list($r['locations']),
                'zipcodes' => Pages::list($r['zipcodes']),
                'offers' => $offers[(int) $r['id']] ?? [],
                'updated_at' => date(DATE_ATOM, strtotime(max((string) $r['page_updated'], (string) $r['account_updated'])) ?: time()),
            ];
        }

        echo json_encode([
            'generated_at' => date(DATE_ATOM),
            'count' => count($out),
            'businesses' => $out,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
