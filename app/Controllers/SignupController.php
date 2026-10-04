<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Audit;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Plans;
use App\Support\RateLimiter;
use App\Support\Request;
use App\Support\Slug;
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
        if (Database::first('SELECT id FROM users WHERE email = :e', ['e' => $email]) !== null) {
            $this->fail('There is already an account with that email. Sign in instead, or use another address.');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::run(
                'INSERT INTO users (email, password_hash, first_name, last_name, password_changed_at)
                 VALUES (:email, :hash, :first, :last, NOW())',
                ['email' => $email, 'hash' => password_hash($password, PASSWORD_DEFAULT), 'first' => $first, 'last' => $last],
            );
            $userId = (int) $pdo->lastInsertId();

            $slug = Slug::unique((string) $business);
            Database::run(
                'INSERT INTO accounts (business_name, slug, plan, requested_plan, signup_ip)
                 VALUES (:name, :slug, :plan, :requested, :ip)',
                [
                    'name' => $business,
                    'slug' => $slug,
                    'plan' => Plans::FREE,
                    'requested' => Plans::isPaid((string) $plan) ? $plan : null,
                    'ip' => Request::ip(),
                ],
            );
            $accountId = (int) $pdo->lastInsertId();

            Database::run(
                "INSERT INTO account_users (account_id, user_id, role) VALUES (:a, :u, 'owner')",
                ['a' => $accountId, 'u' => $userId],
            );
            Database::run(
                'INSERT INTO pages (account_id, phone, public_email) VALUES (:a, :phone, :email)',
                ['a' => $accountId, 'phone' => $phone, 'email' => $email],
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

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
