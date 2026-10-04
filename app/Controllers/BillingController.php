<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Audit;
use App\Support\Auth;
use App\Support\Billing;
use App\Support\Csrf;
use App\Support\Plans;
use App\Support\Request;

/**
 * Paying for Pro ($19) or Premium ($49). Three member actions and the Stripe
 * webhook. No guard in the constructor: the webhook has no session.
 */
final class BillingController
{
    public function start(): void
    {
        Auth::requireMember();
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $this->back('Your session expired. Please try again.');
        }

        $account = Auth::account() ?? [];
        $plan = (string) ($_POST['plan'] ?? '');
        if (!Plans::isPaid($plan)) {
            $this->back('That is not a plan you can subscribe to.');
        }

        // Already paying: plan changes go through the portal so Stripe prorates
        // instead of us opening a second subscription.
        if (Billing::hasLiveSubscription($account)) {
            $this->manage();
        }

        $this->checkout($account, $plan);
    }

    /**
     * Shared with the end of intake, which sends a member who chose a paid plan
     * at signup straight to checkout.
     *
     * @param array<string,mixed> $account
     */
    public function checkout(array $account, string $plan): never
    {
        if (!Billing::canCharge($plan)) {
            $this->back('Card payments are not switched on yet. Use "Request this plan" and we will set it up for you.');
        }

        $email = trim((string) (Auth::user()['email'] ?? ''));
        $result = Billing::checkout($account, $plan, $email);
        if ($result['url'] === null) {
            $this->back((string) $result['error']);
        }

        Audit::log('account.checkout_started', 'account', (int) $account['id'], ['plan' => $account['plan']], ['plan' => $plan]);
        Request::redirect($result['url']);
    }

    public function manage(): never
    {
        Auth::requireMember();
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            $this->back('Your session expired. Please try again.');
        }

        $result = Billing::portal(Auth::account() ?? []);
        if ($result['url'] === null) {
            $this->back((string) $result['error']);
        }
        Request::redirect($result['url']);
    }

    public function finish(): void
    {
        Auth::requireMember();
        $account = Auth::account() ?? [];
        $result = Billing::finish($account, (string) ($_GET['session'] ?? ''));

        if ($result['error'] !== null) {
            $this->back('Your payment went through, but we could not read it back just now. '
                . 'Reload in a minute -- nothing will be charged twice.');
        }
        if ($result['plan'] === null) {
            $this->back('Thanks! Stripe has not confirmed the payment yet. Reload this page in a few seconds.');
        }

        Audit::log('account.subscribed', 'account', (int) $account['id'], ['plan' => $account['plan']], ['plan' => $result['plan']]);
        $this->back('You are on ' . Plans::name($result['plan']) . '. Your receipt is on its way from Stripe.');
    }

    public function webhook(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $raw = file_get_contents('php://input') ?: '';
        $header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

        $why = Billing::verify($raw, is_string($header) ? $header : null, Billing::webhookSecret());
        if ($why !== null) {
            error_log('Stripe webhook rejected: ' . $why);
            http_response_code(400);
            echo "No\n";
            return;
        }

        $event = json_decode($raw, true);
        if (!is_array($event)) {
            http_response_code(400);
            echo "Expected JSON\n";
            return;
        }

        $id = (string) ($event['id'] ?? '');
        $type = (string) ($event['type'] ?? '');
        if (!Billing::firstSighting($id, $type)) {
            echo "Already handled\n";
            return;
        }

        try {
            $note = Billing::handle($event);
        } catch (\Throwable $e) {
            error_log('Stripe webhook ' . $id . ' (' . $type . ') failed: ' . $e->getMessage());
            // Forget the event so Stripe's retry is processed rather than
            // dropped as a duplicate.
            \App\Support\Database::run('DELETE FROM stripe_events WHERE id = :id', ['id' => $id]);
            http_response_code(500);
            echo "Retry\n";
            return;
        }

        Audit::log('billing.webhook', 'account', null, null, ['type' => $type, 'result' => $note]);
        echo "OK\n";
    }

    private function back(string $message): never
    {
        $_SESSION['members_flash'] = $message;
        Request::redirect('/members/billing');
    }
}
