<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Auth;
use App\Support\Plans;
use App\Support\Request;
use App\Support\View;

/**
 * Signing up. Accounts live on Toutify, LeadCrazy's parent site, so /signup
 * shows Toutify's embedded sign-up form; when the member finishes on Toutify
 * they come back signed in (see ToutifyController) and LeadCrazy creates
 * their business account then.
 *
 * A chosen plan (?plan=pro) is remembered in the session and becomes the
 * account's requested plan.
 */
final class SignupController
{
    /** GET /signup */
    public function show(): void
    {
        if (Auth::account() !== null) {
            Request::redirect('/members');
        }
        $plan = (string) ($_GET['plan'] ?? '');
        if (Plans::isValid($plan)) {
            $_SESSION['signup_plan'] = $plan;
        }

        echo View::page('signup', [
            'title' => 'Get your free lead page - LeadCrazy',
            'description' => 'Create your free LeadCrazy account and get the Million Dollar Lead Form for your business.',
            'plan' => $_SESSION['signup_plan'] ?? Plans::FREE,
        ]);
    }

    /** GET /members/signup and POST /members/signup: old addresses for the same page. */
    public function legacy(): void
    {
        $plan = (string) ($_GET['plan'] ?? '');
        Request::redirect('/signup' . (Plans::isValid($plan) ? '?plan=' . rawurlencode($plan) : ''));
    }
}
