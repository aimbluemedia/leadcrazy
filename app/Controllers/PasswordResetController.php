<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Audit;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Mailer;
use App\Support\PasswordReset;
use App\Support\RateLimiter;
use App\Support\Request;
use App\Support\View;

/**
 * Forgotten member passwords: ask for a link, get it by email, choose a new
 * password, and be signed in.
 *
 * The "check your email" answer is the same whether or not the address has an
 * account, so the form cannot be used to find out who is registered.
 */
final class PasswordResetController
{
    public function showForgot(): void
    {
        echo View::render('auth/forgot', [
            'title' => 'Forgot your password?',
            'sent' => View::flash('reset_sent'),
            'error' => View::flash('reset_error'),
            'email' => is_string($_GET['email'] ?? null) ? mb_substr($_GET['email'], 0, 254) : '',
        ]);
    }

    public function sendLink(): void
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $this->back('Your session expired. Please try again.');
        }
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->back('Enter the email address you sign in with.');
        }
        if (RateLimiter::tooManyAttempts('reset-ip:' . Request::ip(), 10, 3600)
            || RateLimiter::tooManyAttempts('reset-email:' . $email, 3, 3600)) {
            $this->back('Too many reset requests. Please wait an hour and try again.');
        }

        $issued = PasswordReset::issue($email);
        if ($issued !== null) {
            $name = trim((string) ($issued['user']['first_name'] ?? ''));
            $link = View::url('/members/reset/' . $issued['token']);
            $result = Mailer::send($email, 'Reset your LeadCrazy password',
                'Hi' . ($name !== '' ? ' ' . $name : '') . ",\n\n"
                . "Someone (hopefully you) asked to reset the password for your LeadCrazy account.\n\n"
                . "Choose a new password here:\n" . $link . "\n\n"
                . 'This link works once and expires in ' . PasswordReset::MINUTES . " minutes.\n\n"
                . "If you did not ask for this, ignore this email - your password stays the same.\n\n"
                . "- The LeadCrazy team\n");
            Audit::log('auth.reset_requested', 'user', (int) $issued['user']['id'], null, ['sent' => $result['ok']]);
            if (!$result['ok']) {
                $this->back('We could not send the email just now. Please try again in a few minutes.');
            }
        }

        $_SESSION['reset_sent'] = $email;
        Request::redirect('/members/forgot');
    }

    public function showReset(string $token): void
    {
        echo View::render('auth/reset', [
            'title' => 'Choose a new password',
            'token' => $token,
            'valid' => PasswordReset::find($token) !== null,
            'error' => View::flash('reset_error'),
            'min' => PasswordController::MIN_LENGTH,
        ]);
    }

    public function reset(): void
    {
        $token = (string) ($_POST['token'] ?? '');
        $back = '/members/reset/' . (preg_match('/^[a-f0-9]{64}$/', $token) ? $token : '');

        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $_SESSION['reset_error'] = 'Your session expired. Please try again.';
            Request::redirect($back);
        }
        $row = PasswordReset::find($token);
        if ($row === null) {
            Request::redirect($back);
        }

        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < PasswordController::MIN_LENGTH) {
            $_SESSION['reset_error'] = 'Use at least ' . PasswordController::MIN_LENGTH . ' characters.';
            Request::redirect($back);
        }
        if ($password !== (string) ($_POST['password_confirm'] ?? '')) {
            $_SESSION['reset_error'] = 'Those two passwords do not match.';
            Request::redirect($back);
        }

        $userId = (int) $row['user_id'];
        PasswordReset::consume((int) $row['id'], $userId);
        Auth::signIn($userId, 'auth.password_reset');
        Auth::setPassword($userId, $password);
        $_SESSION['members_flash'] = 'Your password has been changed and you are signed in.';
        Request::redirect('/members');
    }

    private function back(string $error): never
    {
        $_SESSION['reset_error'] = $error;
        Request::redirect('/members/forgot');
    }
}
