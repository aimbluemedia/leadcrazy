<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Auth;
use App\Support\Config;
use App\Support\Csrf;
use App\Support\Request;
use App\Support\View;

/** Sign in and out of the members and superadmin areas. */
final class AuthController
{
    private const AREAS = ['superadmin', 'members'];

    public function showLogin(string $area): void
    {
        $this->assertArea($area);

        if ($area === 'superadmin' && Auth::isStaff()) {
            Request::redirect('/superadmin');
        }
        if ($area === 'members' && Auth::account() !== null) {
            Request::redirect('/members');
        }

        echo View::render('auth/login', [
            'title' => $area === 'superadmin' ? 'Superadmin sign in' : 'Member sign in',
            'area' => $area,
            'error' => View::flash('login_error'),
            'notice' => View::flash('login_notice'),
        ]);
    }

    public function login(string $area): void
    {
        $this->assertArea($area);
        $back = "/{$area}/login";

        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $this->fail($back, 'Your session expired. Please try again.');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->fail($back, 'Enter your email and password.');
        }
        if (Auth::lockedOut($email)) {
            $this->fail($back, 'Too many failed attempts. Try again in 15 minutes.');
        }
        if (!Auth::attempt($email, $password)) {
            // Same message for an unknown address and a wrong password, so the
            // form cannot be used to find out who has an account.
            $this->fail($back, 'Those details did not match.');
        }

        $entitled = $area === 'superadmin' ? Auth::isStaff() : Auth::account() !== null;
        if (!$entitled) {
            Auth::logout();
            $this->fail($back, $area === 'superadmin'
                ? 'That login does not have staff access.'
                : 'That login is not linked to a business account.');
        }

        Request::redirect("/{$area}");
    }

    public function logout(string $area): void
    {
        $this->assertArea($area);
        if (Csrf::check($_POST['_csrf'] ?? null)) {
            Auth::logout();
        }
        Request::redirect("/{$area}/login");
    }

    /**
     * There is no email sending in this version, so a forgotten password is
     * reset by a person: superadmin sets a temporary one, and the member must
     * change it on next sign-in.
     */
    public function forgot(): void
    {
        echo View::render('auth/forgot', [
            'title' => 'Forgot your password?',
            'support' => (string) Config::get('support_email', ''),
        ]);
    }

    private function assertArea(string $area): void
    {
        if (!in_array($area, self::AREAS, true)) {
            throw new \InvalidArgumentException("Unknown area: {$area}");
        }
    }

    private function fail(string $back, string $message): never
    {
        $_SESSION['login_error'] = $message;
        Request::redirect($back);
    }
}
