<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Request;
use App\Support\View;

/**
 * Choosing a password. Reached in two ways: forced, after superadmin set a
 * temporary one, or by choice from the members area.
 */
final class PasswordController
{
    public const MIN_LENGTH = 10;
    private const AREAS = ['superadmin', 'members'];

    public function show(string $area): void
    {
        $this->guard($area);

        echo View::render('auth/password', [
            'title' => 'Choose a password',
            'area' => $area,
            'forced' => Auth::mustChangePassword(),
            'error' => View::flash('password_error'),
            'min' => self::MIN_LENGTH,
        ]);
    }

    public function update(string $area): void
    {
        $this->guard($area);
        $back = "/{$area}/password";

        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $this->fail($back, 'Your session expired. Please try again.');
        }

        $user = Auth::user() ?? [];
        $forced = Auth::mustChangePassword();
        $current = (string) ($_POST['current_password'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        // Changing a password by choice needs the current one, so a session left
        // open on a shared computer cannot be used to take the account over.
        if (!$forced && !Auth::attemptPasswordOnly((int) $user['id'], $current)) {
            $this->fail($back, 'Your current password was not right.');
        }
        if (strlen($password) < self::MIN_LENGTH) {
            $this->fail($back, 'Use at least ' . self::MIN_LENGTH . ' characters.');
        }
        if ($password !== $confirm) {
            $this->fail($back, 'Those two passwords do not match.');
        }
        if (Auth::attemptPasswordOnly((int) $user['id'], $password)) {
            $this->fail($back, 'Choose a different password from the one you have now.');
        }

        Auth::setPassword((int) $user['id'], $password);
        $_SESSION[$area === 'superadmin' ? 'admin_flash' : 'members_flash'] = 'Password updated.';
        Request::redirect("/{$area}");
    }

    private function guard(string $area): void
    {
        if (!in_array($area, self::AREAS, true)) {
            throw new \InvalidArgumentException("Unknown area: {$area}");
        }
        $entitled = $area === 'superadmin' ? Auth::isStaff() : Auth::account() !== null;
        if (!$entitled) {
            Request::redirect("/{$area}/login");
        }
    }

    private function fail(string $back, string $message): never
    {
        $_SESSION['password_error'] = $message;
        Request::redirect($back);
    }
}
