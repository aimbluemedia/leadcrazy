<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;

/** The LeadCrazy marketing pages. */
final class PageController
{
    public function home(): void
    {
        echo View::page('home', [
            'title' => 'LeadCrazy - The Million Dollar Lead Form',
            'description' => 'A lead page and quote form built for local service businesses. Free to start, $19 hosted on LeadCrazy and MonsterList, $49 on your own website.',
            'current' => 'home',
        ]);
    }

    public function pricing(): void
    {
        echo View::page('pricing', [
            'title' => 'Pricing - LeadCrazy',
            'description' => 'Free, $19/month or $49/month. Cancel any time.',
            'current' => 'pricing',
        ]);
    }

    public function privacy(): void
    {
        echo View::page('privacy', ['title' => 'Privacy - LeadCrazy']);
    }

    public function terms(): void
    {
        echo View::page('terms', ['title' => 'Terms - LeadCrazy']);
    }
}
