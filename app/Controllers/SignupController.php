<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Accounts;
use App\Support\Audit;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Plans;
use App\Support\RateLimiter;
use App\Support\Request;
use App\Support\Validator;
use App\Support\View;

/**
 * Self-serve signup. Creates the login, the business account and an empty
 * page, then sends the member to the intake form. A paid plan is charged at
 * the end of intake, not here: nobody should pay before telling us what their
 * business is.
 */
final class SignupController
{
    public function show(): void
    {
        if (Auth::account() !== null) {
            Request::redirect('/members');
        }

        $plan = (string) ($_GET['plan'] ?? Plans::FREE);

        echo View::render('auth/signup', [
            'title' => 'Create your free lead page',
            'plan' => Plans::isValid($plan) ? $plan : Plans::FREE,
            'old' => $_SESSION['signup_old'] ?? [],
            'error' => View::flash('signup_error'),
        ]);
        unset($_SESSION['signup_old']);
    }

    public function store(): void
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $this->fail('Your session expired. Please try again.');
        }
        if (RateLimiter::tooManyAttempts('signup:' . Request::ip(), 5, 3600)) {
            $this->fail('Too many signups from your connection. Please try again in an hour.');
        }

        $v = new Validator($_POST);
        $business = $v->required('business_name', 'Enter your business name.', 160);
        $first = $v->required('first_name', 'Enter your first name.', 80);
        $last = $v->value('last_name', 80);
        $email = $v->email('email', 'Enter a valid email address.');
        $phone = $v->required('phone', 'Enter a phone number.', 40);
        $plan = $v->inList('plan', Plans::ALL, 'Choose a plan.');
        $password = (string) ($_POST['password'] ?? '');

        if ($v->fails()) {
            $this->fail((string) $v->firstError());
        }
        if (strlen($password) < PasswordController::MIN_LENGTH) {
            $this->fail('Use a password of at least ' . PasswordController::MIN_LENGTH . ' characters.');
        }
        if (empty($_POST['agree'])) {
            $this->fail('Please agree to the terms to continue.');
        }
        if (Accounts::emailTaken((string) $email)) {
            $this->fail('There is already an account with that email. Sign in instead, or use another address.');
        }

        $created = Accounts::create(
            ['business' => (string) $business, 'first' => (string) $first, 'last' => $last, 'email' => (string) $email,
             'password' => $password, 'plan' => (string) $plan],
            ['phone' => $phone, 'public_email' => $email],
        );
        ['user_id' => $userId, 'account_id' => $accountId, 'slug' => $slug] = $created;

        Auth::signIn($userId);
        Audit::log('account.created', 'account', $accountId, null, ['plan' => $plan, 'slug' => $slug]);

        $_SESSION['members_flash'] = 'Welcome to LeadCrazy! Tell us about your business and we will build your page.';
        Request::redirect('/members/intake');
    }

    private function fail(string $message): never
    {
        $old = $_POST;
        unset($old['password'], $old['_csrf']);
        $_SESSION['signup_old'] = $old;
        $_SESSION['signup_error'] = $message;
        Request::redirect('/members/signup');
    }
}
