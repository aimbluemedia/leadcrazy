<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Stripe subscriptions for the $19 and $49 plans, without an SDK.
 *
 * Same design as PromoMonster's Billing: there is no Composer on the host, the
 * app uses five endpoints and one signature check, and form-encoding via
 * http_build_query() is all Stripe needs. Nothing outside this file and
 * BillingController mentions Stripe.
 *
 * This only ever WRITES accounts.plan. What a plan includes is Plans' job, so
 * if Stripe is switched off every account keeps working on its last plan.
 */
final class Billing
{
    private const API = 'https://api.stripe.com/v1/';

    /** Seconds a webhook's own timestamp may be out by before it is refused. */
    public const TOLERANCE = 300;

    public static function key(): string
    {
        return trim((string) Config::get('stripe.secret_key', ''));
    }

    public static function webhookSecret(): string
    {
        return trim((string) Config::get('stripe.webhook_secret', ''));
    }

    /** The Stripe Price id for a plan (price_...). */
    public static function price(string $plan): string
    {
        return trim((string) Config::get('stripe.prices.' . $plan, ''));
    }

    /** Reverse lookup. An unknown Price is never guessed at. */
    public static function planForPrice(string $priceId): ?string
    {
        if ($priceId === '') {
            return null;
        }
        foreach (Plans::PAID as $plan) {
            if (self::price($plan) === $priceId) {
                return $plan;
            }
        }
        return null;
    }

    public static function canCharge(string $plan): bool
    {
        return self::key() !== '' && Plans::isPaid($plan) && self::price($plan) !== '';
    }

    public static function live(): bool
    {
        foreach (Plans::PAID as $plan) {
            if (self::canCharge($plan)) {
                return true;
            }
        }
        return false;
    }

    public static function testMode(): bool
    {
        return str_starts_with(self::key(), 'sk_test_');
    }

    /**
     * True while Stripe is still collecting on this account. Superadmin must not
     * drop such an account to Free by hand: the plan would change here while the
     * card kept being charged there. Cancellation happens at Stripe and comes
     * back as a webhook.
     *
     * @param array<string,mixed> $account
     */
    public static function hasLiveSubscription(array $account): bool
    {
        return trim((string) ($account['stripe_subscription_id'] ?? '')) !== ''
            && in_array((string) ($account['stripe_status'] ?? ''),
                ['active', 'trialing', 'past_due', 'unpaid', 'incomplete'], true);
    }

    /* ------------------------------------------------------------ checkout */

    /**
     * @param array<string,mixed> $account
     * @return array{url:?string,error:?string}
     */
    public static function checkout(array $account, string $plan, string $email): array
    {
        if (!self::canCharge($plan)) {
            return ['url' => null, 'error' => 'Card payments are not switched on for that plan yet.'];
        }

        $accountId = (int) ($account['id'] ?? 0);
        $customer = self::customer($account, $email);
        if ($customer['id'] === null) {
            return ['url' => null, 'error' => $customer['error']];
        }

        $params = [
            'mode' => 'subscription',
            'customer' => $customer['id'],
            'client_reference_id' => (string) $accountId,
            'line_items' => [['price' => self::price($plan), 'quantity' => 1]],
            'success_url' => View::url('/members/billing/return?session={CHECKOUT_SESSION_ID}'),
            'cancel_url' => View::url('/members/billing'),
            'subscription_data' => ['metadata' => ['account_id' => (string) $accountId, 'plan' => $plan]],
            'allow_promotion_codes' => 'true',
        ];

        [$status, $body, $error] = self::call('POST', 'checkout/sessions', $params);
        if ($error !== null) {
            return ['url' => null, 'error' => $error];
        }

        // A customer deleted in the Stripe dashboard, or a switch between test
        // and live keys, leaves an id on file Stripe no longer knows.
        if ($status === 400 && str_contains((string) ($body['error']['message'] ?? ''), 'No such customer')) {
            Database::run('UPDATE accounts SET stripe_customer_id = NULL WHERE id = :id', ['id' => $accountId]);
            $retry = self::customer(['id' => $accountId, 'business_name' => $account['business_name'] ?? ''], $email);
            if ($retry['id'] === null) {
                return ['url' => null, 'error' => $retry['error']];
            }
            $params['customer'] = $retry['id'];
            [$status, $body, $error] = self::call('POST', 'checkout/sessions', $params);
            if ($error !== null) {
                return ['url' => null, 'error' => $error];
            }
        }

        $url = (string) ($body['url'] ?? '');
        if ($status !== 200 || $url === '') {
            return ['url' => null, 'error' => self::messageFrom($status, $body)];
        }

        return ['url' => $url, 'error' => null];
    }

    /** @param array<string,mixed> $account @return array{url:?string,error:?string} */
    public static function portal(array $account): array
    {
        $customerId = trim((string) ($account['stripe_customer_id'] ?? ''));
        if ($customerId === '') {
            return ['url' => null, 'error' => 'There is no billing account to manage yet.'];
        }

        [$status, $body, $error] = self::call('POST', 'billing_portal/sessions', [
            'customer' => $customerId,
            'return_url' => View::url('/members/billing'),
        ]);
        if ($error !== null) {
            return ['url' => null, 'error' => $error];
        }

        $url = (string) ($body['url'] ?? '');
        if ($status !== 200 || $url === '') {
            return ['url' => null, 'error' => self::messageFrom($status, $body)];
        }

        return ['url' => $url, 'error' => null];
    }

    /**
     * The browser's return trip from checkout. Applies the subscription now
     * rather than waiting on the webhook, which may race the page or not be set
     * up yet. The session id comes from a query string, so it must belong to
     * this account before it changes anything.
     *
     * @param array<string,mixed> $account
     * @return array{plan:?string,error:?string}
     */
    public static function finish(array $account, string $sessionId): array
    {
        $accountId = (int) ($account['id'] ?? 0);
        if (self::key() === '' || $sessionId === '' || $accountId <= 0) {
            return ['plan' => null, 'error' => null];
        }

        [$status, $session, $error] = self::call(
            'GET',
            'checkout/sessions/' . rawurlencode($sessionId),
            ['expand' => ['subscription']],
        );
        if ($error !== null || $status !== 200) {
            return ['plan' => null, 'error' => $error ?? self::messageFrom($status, $session)];
        }

        if ((string) ($session['client_reference_id'] ?? '') !== (string) $accountId) {
            return ['plan' => null, 'error' => null];
        }
        $onFile = trim((string) ($account['stripe_customer_id'] ?? ''));
        if ($onFile !== '' && self::idOf($session['customer'] ?? null) !== $onFile) {
            return ['plan' => null, 'error' => null];
        }

        $subscription = is_array($session['subscription'] ?? null) ? $session['subscription'] : null;
        if ($subscription === null) {
            return ['plan' => null, 'error' => null];
        }

        return ['plan' => self::applySubscription($accountId, $subscription), 'error' => null];
    }

    /* ------------------------------------------------------------- webhook */

    /** Null when the Stripe-Signature header checks out, else the reason. */
    public static function verify(string $payload, ?string $header, string $secret, ?int $now = null): ?string
    {
        if ($secret === '') {
            return 'no signing secret is configured';
        }
        if ($header === null || trim($header) === '') {
            return 'no signature header';
        }

        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) !== 2) {
                continue;
            }
            if ($pair[0] === 't') {
                $timestamp = ctype_digit($pair[1]) ? (int) $pair[1] : null;
            } elseif ($pair[0] === 'v1') {
                $signatures[] = $pair[1];
            }
        }

        if ($timestamp === null || $signatures === []) {
            return 'incomplete signature header';
        }
        if (abs(($now ?? time()) - $timestamp) > self::TOLERANCE) {
            return 'signature outside the tolerance window';
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($signatures as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return null;
            }
        }
        return 'signature does not match';
    }

    /** @param array<string,mixed> $event */
    public static function handle(array $event): string
    {
        $type = (string) ($event['type'] ?? '');
        $object = is_array($event['data']['object'] ?? null) ? $event['data']['object'] : [];

        switch ($type) {
            case 'checkout.session.completed':
                $accountId = self::accountFor($object, (int) ($object['client_reference_id'] ?? 0));
                $subscriptionId = self::idOf($object['subscription'] ?? null);
                if ($accountId === null || $subscriptionId === '') {
                    return 'checkout session we cannot place';
                }
                [$status, $subscription] = self::call('GET', 'subscriptions/' . rawurlencode($subscriptionId));
                if ($status !== 200) {
                    throw new \RuntimeException('could not read subscription ' . $subscriptionId);
                }
                return 'account ' . $accountId . ' -> ' . (self::applySubscription($accountId, $subscription) ?? 'unchanged');

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
            case 'customer.subscription.paused':
            case 'customer.subscription.resumed':
                $accountId = self::accountFor($object, (int) ($object['metadata']['account_id'] ?? 0));
                if ($accountId === null) {
                    return $type . ' for an account we do not have';
                }
                if ($type === 'customer.subscription.deleted') {
                    $object['status'] = 'canceled';
                }
                return 'account ' . $accountId . ' -> ' . (self::applySubscription($accountId, $object) ?? 'unchanged');

            default:
                return 'ignored ' . $type;
        }
    }

    /** True the first time an event id is seen. */
    public static function firstSighting(string $eventId, string $type): bool
    {
        if ($eventId === '') {
            return true;
        }
        try {
            Database::run('INSERT INTO stripe_events (id, type) VALUES (:id, :type)', ['id' => $eventId, 'type' => $type]);
            return true;
        } catch (\PDOException $e) {
            return ($e->errorInfo[0] ?? '') !== '23000';
        }
    }

    /**
     * Writes a subscription onto its account. Returns the plan it set, or null
     * when the plan was left alone.
     *
     * @param array<string,mixed> $subscription
     */
    public static function applySubscription(int $accountId, array $subscription): ?string
    {
        $state = (string) ($subscription['status'] ?? '');
        $price = self::priceOf($subscription);
        $plan = self::planForPrice($price);

        $fields = [
            'stripe_subscription_id' => self::idOf($subscription['id'] ?? null) ?: null,
            'stripe_price_id' => $price !== '' ? $price : null,
            'stripe_status' => $state !== '' ? $state : null,
            'plan_renews_at' => self::renewalFrom($subscription),
        ];
        $customerId = self::idOf($subscription['customer'] ?? null);
        if ($customerId !== '') {
            $fields['stripe_customer_id'] = $customerId;
        }

        switch ($state) {
            case 'active':
            case 'trialing':
                // An unrecognised Price leaves the plan alone rather than guessing.
                if ($plan !== null) {
                    $fields['plan'] = $plan;
                }
                break;
            case 'canceled':
            case 'incomplete_expired':
            case 'paused':
                $fields['plan'] = Plans::FREE;
                $fields['plan_renews_at'] = null;
                break;
            // past_due / unpaid: Stripe is still retrying the card. Keep the
            // plan; a page going dark over one failed charge loses the member
            // leads while their bank sorts itself out.
        }

        $sets = [];
        $params = ['id' => $accountId];
        foreach ($fields as $column => $value) {
            $sets[] = $column . ' = :' . $column;
            $params[$column] = $value;
        }
        if (array_key_exists('plan', $fields)) {
            $sets[] = 'requested_plan = NULL';
            $sets[] = 'plan_changed_at = NOW()';
        }
        Database::run('UPDATE accounts SET ' . implode(', ', $sets) . ' WHERE id = :id', $params);

        return $fields['plan'] ?? null;
    }

    /* ------------------------------------------------------------- helpers */

    /** @param array<string,mixed> $object */
    private static function accountFor(array $object, int $claimed): ?int
    {
        $customerId = self::idOf($object['customer'] ?? null);
        if ($customerId !== '') {
            $row = Database::first('SELECT id FROM accounts WHERE stripe_customer_id = :c LIMIT 1', ['c' => $customerId]);
            if ($row !== null) {
                return (int) $row['id'];
            }
        }
        if ($claimed > 0) {
            $row = Database::first('SELECT id FROM accounts WHERE id = :id', ['id' => $claimed]);
            if ($row !== null) {
                return (int) $row['id'];
            }
        }
        return null;
    }

    /** @param array<string,mixed> $account @return array{id:?string,error:?string} */
    private static function customer(array $account, string $email): array
    {
        $existing = trim((string) ($account['stripe_customer_id'] ?? ''));
        if ($existing !== '') {
            return ['id' => $existing, 'error' => null];
        }

        $accountId = (int) ($account['id'] ?? 0);
        [$status, $body, $error] = self::call('POST', 'customers', [
            'email' => $email,
            'name' => (string) ($account['business_name'] ?? ''),
            'metadata' => ['account_id' => (string) $accountId],
        ]);
        if ($error !== null) {
            return ['id' => null, 'error' => $error];
        }
        $id = self::idOf($body['id'] ?? null);
        if ($status !== 200 || $id === '') {
            return ['id' => null, 'error' => self::messageFrom($status, $body)];
        }

        Database::run('UPDATE accounts SET stripe_customer_id = :c WHERE id = :id', ['c' => $id, 'id' => $accountId]);
        return ['id' => $id, 'error' => null];
    }

    /** current_period_end moved from the subscription onto its items in newer API versions. @param array<string,mixed> $subscription */
    public static function renewalFrom(array $subscription): ?string
    {
        $end = $subscription['current_period_end'] ?? ($subscription['items']['data'][0]['current_period_end'] ?? null);
        if (!is_int($end) && !(is_string($end) && ctype_digit($end))) {
            return null;
        }
        return date('Y-m-d H:i:s', (int) $end);
    }

    /** @param array<string,mixed> $subscription */
    public static function priceOf(array $subscription): string
    {
        $item = $subscription['items']['data'][0] ?? null;
        if (!is_array($item)) {
            return '';
        }
        return self::idOf($item['price'] ?? null) ?: self::idOf($item['plan'] ?? null);
    }

    public static function idOf(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        return is_array($value) && is_string($value['id'] ?? null) ? $value['id'] : '';
    }

    /**
     * @param array<string,mixed> $params
     * @return array{0:int,1:array<string,mixed>,2:?string}
     */
    private static function call(string $method, string $path, array $params = []): array
    {
        $key = self::key();
        if ($key === '') {
            return [0, [], 'Card payments are not switched on.'];
        }

        $url = self::API . $path;
        $form = $params === [] ? '' : http_build_query($params);

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $method === 'GET' && $form !== '' ? $url . '?' . $form : $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $key,
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ];
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $form;
        }
        curl_setopt_array($ch, $options);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return [0, [], 'Could not reach Stripe: ' . $error];
        }
        $decoded = json_decode((string) $raw, true);
        return [$status, is_array($decoded) ? $decoded : [], null];
    }

    /** @param array<string,mixed> $body */
    private static function messageFrom(int $status, array $body): string
    {
        $type = (string) ($body['error']['type'] ?? '');
        $message = trim((string) ($body['error']['message'] ?? ''));
        if ($message !== '' && in_array($type, ['card_error', 'invalid_request_error'], true)) {
            return $message;
        }
        return 'Stripe could not start the payment just now (' . ($status ?: 'no response') . '). '
            . 'Nothing has been charged. Please try again in a minute.';
    }
}
