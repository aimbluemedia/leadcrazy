<?php

declare(strict_types=1);

namespace App\Support\Toutify;

use App\Support\Config;

/**
 * LeadCrazy as an OpenID Connect client of Toutify, the parent site that holds
 * every member's login.
 *
 * Settings come from the environment (TOUTIFY_ISSUER, TOUTIFY_CLIENT_ID,
 * TOUTIFY_CLIENT_SECRET, TOUTIFY_WEBHOOK_SECRET) or, where the host cannot set
 * environment variables, from the 'toutify' block in app/config.php. Never from
 * the code.
 *
 * Discovery and the JWKS are cached in storage/cache for an hour; an unknown
 * key id refetches the JWKS once, so a key rotation on Toutify needs nothing
 * here.
 */
final class Client
{
    public const SCOPE = 'openid profile email phone toutify';
    private const CACHE_SECONDS = 3600;
    private const CLOCK_SKEW = 60;
    private const HTTP_TIMEOUT = 10;

    public static function issuer(): string
    {
        return rtrim(self::setting('TOUTIFY_ISSUER', 'issuer'), '/');
    }

    public static function clientId(): string
    {
        return self::setting('TOUTIFY_CLIENT_ID', 'client_id');
    }

    public static function webhookSecret(): string
    {
        return self::setting('TOUTIFY_WEBHOOK_SECRET', 'webhook_secret');
    }

    public static function configured(): bool
    {
        return self::issuer() !== '' && self::clientId() !== '' && self::setting('TOUTIFY_CLIENT_SECRET', 'client_secret') !== '';
    }

    /** LeadCrazy's own address, e.g. https://leadcrazy.com (no trailing slash). */
    public static function site(): string
    {
        return rtrim((string) Config::get('app_url', ''), '/');
    }

    public static function redirectUri(): string
    {
        return self::site() . '/auth/toutify/callback';
    }

    /* ------------------------------------------------------------ sign-in */

    /**
     * The authorize URL plus what the callback needs to check it: state, nonce
     * and the PKCE verifier.
     *
     * @return array{url:string,state:string,nonce:string,verifier:string}
     */
    public static function authorization(bool $signup): array
    {
        $state = Jwt::b64encode(random_bytes(24));
        $nonce = Jwt::b64encode(random_bytes(24));
        $verifier = Jwt::b64encode(random_bytes(48));

        $params = [
            'response_type' => 'code',
            'client_id' => self::clientId(),
            'redirect_uri' => self::redirectUri(),
            'scope' => self::SCOPE,
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => Jwt::b64encode(hash('sha256', $verifier, true)),
            'code_challenge_method' => 'S256',
        ];
        if ($signup) {
            $params['prompt'] = 'create';
        }

        $endpoint = (string) self::discovery()['authorization_endpoint'];
        return [
            'url' => $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . http_build_query($params, '', '&', PHP_QUERY_RFC3986),
            'state' => $state,
            'nonce' => $nonce,
            'verifier' => $verifier,
        ];
    }

    /**
     * Code for tokens (client_secret_basic, with the PKCE verifier), then the
     * ID token checked, then UserInfo fetched for the same subject.
     *
     * @return array{id_token:string,claims:array<string,mixed>,userinfo:array<string,mixed>}
     */
    public static function finish(string $code, string $verifier, string $nonce): array
    {
        $tokens = self::post((string) self::discovery()['token_endpoint'], [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => self::redirectUri(),
            'code_verifier' => $verifier,
        ], [
            'Authorization: Basic ' . base64_encode(
                rawurlencode(self::clientId()) . ':' . rawurlencode(self::setting('TOUTIFY_CLIENT_SECRET', 'client_secret'))
            ),
        ]);

        $idToken = (string) ($tokens['id_token'] ?? '');
        $accessToken = (string) ($tokens['access_token'] ?? '');
        if ($idToken === '' || $accessToken === '') {
            throw new \RuntimeException('token response is missing id_token or access_token');
        }

        $claims = self::verifyIdToken($idToken, $nonce);
        $userinfo = self::userinfo($accessToken);
        if (($userinfo['sub'] ?? null) !== $claims['sub']) {
            throw new \RuntimeException('UserInfo subject does not match the ID token');
        }

        return ['id_token' => $idToken, 'claims' => $claims, 'userinfo' => $userinfo];
    }

    /** @return array<string,mixed> */
    public static function verifyIdToken(string $idToken, string $nonce): array
    {
        $claims = self::verified($idToken)['claims'];
        self::checkCommon($claims);

        if (!isset($claims['exp']) || (int) $claims['exp'] < time() - self::CLOCK_SKEW) {
            throw new \RuntimeException('ID token has expired');
        }
        $aud = (array) $claims['aud'];
        if (count($aud) > 1 && ($claims['azp'] ?? null) !== self::clientId()) {
            throw new \RuntimeException('ID token azp is not this client');
        }
        if (!is_string($claims['nonce'] ?? null) || !hash_equals($nonce, $claims['nonce'])) {
            throw new \RuntimeException('ID token nonce does not match');
        }
        if (!is_string($claims['sub'] ?? null) || $claims['sub'] === '') {
            throw new \RuntimeException('ID token has no subject');
        }
        return $claims;
    }

    /**
     * A back-channel logout token: signed by Toutify, for this client, typ
     * logout+jwt, carrying the back-channel logout event and no nonce.
     *
     * @return array<string,mixed>
     */
    public static function verifyLogoutToken(string $token): array
    {
        $parsed = self::verified($token);
        $typ = strtolower((string) ($parsed['header']['typ'] ?? ''));
        if ($typ !== 'logout+jwt' && $typ !== 'application/logout+jwt') {
            throw new \RuntimeException('logout token typ is not logout+jwt');
        }

        $claims = $parsed['claims'];
        self::checkCommon($claims);
        if (isset($claims['exp']) && (int) $claims['exp'] < time() - self::CLOCK_SKEW) {
            throw new \RuntimeException('logout token has expired');
        }
        $events = $claims['events'] ?? null;
        if (!is_array($events) || !array_key_exists('http://schemas.openid.net/event/backchannel-logout', $events)) {
            throw new \RuntimeException('logout token has no back-channel logout event');
        }
        if (array_key_exists('nonce', $claims)) {
            throw new \RuntimeException('logout token must not carry a nonce');
        }
        if (!is_string($claims['sub'] ?? null) && !is_string($claims['sid'] ?? null)) {
            throw new \RuntimeException('logout token has neither sub nor sid');
        }
        return $claims;
    }

    /** Toutify's end_session URL, to finish signing out there too. */
    public static function endSessionUrl(?string $idToken): string
    {
        $endpoint = (string) (self::discovery()['end_session_endpoint'] ?? self::issuer() . '/');
        $params = ['post_logout_redirect_uri' => self::site() . '/', 'state' => Jwt::b64encode(random_bytes(16))];
        if ($idToken !== null && $idToken !== '') {
            $params = ['id_token_hint' => $idToken] + $params;
        } else {
            $params = ['client_id' => self::clientId()] + $params;
        }
        return $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /* ------------------------------------------------------------ helpers */

    /** @param array<string,mixed> $claims */
    private static function checkCommon(array $claims): void
    {
        if (($claims['iss'] ?? null) !== self::issuer()) {
            throw new \RuntimeException('token issuer is not Toutify');
        }
        if (!in_array(self::clientId(), (array) ($claims['aud'] ?? []), true)) {
            throw new \RuntimeException('token audience is not this client');
        }
        if (!isset($claims['iat']) || (int) $claims['iat'] > time() + self::CLOCK_SKEW) {
            throw new \RuntimeException('token iat is missing or in the future');
        }
    }

    /** @return array{header:array<string,mixed>,claims:array<string,mixed>,signed:string,signature:string} */
    private static function verified(string $jwt): array
    {
        try {
            return Jwt::verify($jwt, self::jwks(false));
        } catch (UnknownKey) {
            return Jwt::verify($jwt, self::jwks(true));
        }
    }

    /** @return array<string,mixed> */
    private static function userinfo(string $accessToken): array
    {
        $response = self::request('GET', (string) self::discovery()['userinfo_endpoint'], null, ['Authorization: Bearer ' . $accessToken]);
        if (str_contains(strtolower($response['type']), 'application/jwt')) {
            $claims = self::verified(trim($response['body']))['claims'];
            self::checkCommon($claims + ['iat' => time()]);
            return $claims;
        }
        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            throw new \RuntimeException('UserInfo did not return JSON');
        }
        return $data;
    }

    /** @return array<string,mixed> */
    public static function discovery(): array
    {
        $doc = self::cached('discovery', static fn () => self::getJson(self::issuer() . '/.well-known/openid-configuration'), false);
        if (($doc['issuer'] ?? null) !== self::issuer()) {
            throw new \RuntimeException('discovery issuer does not match TOUTIFY_ISSUER');
        }
        foreach (['authorization_endpoint', 'token_endpoint', 'userinfo_endpoint', 'jwks_uri'] as $key) {
            if (!is_string($doc[$key] ?? null) || !str_starts_with($doc[$key], 'https://')) {
                throw new \RuntimeException('discovery is missing ' . $key);
            }
        }
        return $doc;
    }

    /** @return array<string,mixed> */
    private static function jwks(bool $refresh): array
    {
        return self::cached('jwks', static fn () => self::getJson((string) self::discovery()['jwks_uri']), $refresh);
    }

    /** @param callable():array<string,mixed> $fetch @return array<string,mixed> */
    private static function cached(string $name, callable $fetch, bool $refresh): array
    {
        $dir = BASE_PATH . '/storage/cache';
        $file = $dir . '/toutify-' . $name . '-' . substr(hash('sha256', self::issuer()), 0, 12) . '.json';
        $fresh = is_file($file) && filemtime($file) > time() - self::CACHE_SECONDS;
        // A forced refresh is still limited to once a minute, so a stream of
        // tokens with a made-up kid cannot hammer Toutify through us.
        if ($fresh && (!$refresh || filemtime($file) > time() - 60)) {
            $data = json_decode((string) file_get_contents($file), true);
            if (is_array($data)) {
                return $data;
            }
        }
        $data = $fetch();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($file, json_encode($data), LOCK_EX);
        return $data;
    }

    /** @return array<string,mixed> */
    private static function getJson(string $url): array
    {
        $data = json_decode(self::request('GET', $url)['body'], true);
        if (!is_array($data)) {
            throw new \RuntimeException('expected JSON from ' . $url);
        }
        return $data;
    }

    /** @param array<string,string> $form @param list<string> $headers @return array<string,mixed> */
    private static function post(string $url, array $form, array $headers): array
    {
        $response = self::request('POST', $url, http_build_query($form, '', '&'), array_merge($headers, ['Content-Type: application/x-www-form-urlencoded']));
        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            throw new \RuntimeException('token endpoint did not return JSON');
        }
        return $data;
    }

    /** @param list<string> $headers @return array{status:int,type:string,body:string} */
    private static function request(string $method, string $url, ?string $body = null, array $headers = []): array
    {
        if (!str_starts_with($url, 'https://')) {
            throw new \RuntimeException('refusing a non-HTTPS request to ' . $url);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
            CURLOPT_TIMEOUT => self::HTTP_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'LeadCrazy-OIDC/1.0',
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $result = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $type = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new \RuntimeException('could not reach Toutify: ' . $error);
        }
        if ($status < 200 || $status >= 300) {
            $detail = json_decode((string) $result, true);
            $why = is_array($detail) ? trim(($detail['error'] ?? '') . ' ' . ($detail['error_description'] ?? '')) : '';
            throw new \RuntimeException('Toutify answered HTTP ' . $status . ($why !== '' ? ' (' . $why . ')' : '') . ' for ' . parse_url($url, PHP_URL_PATH));
        }
        return ['status' => $status, 'type' => $type, 'body' => (string) $result];
    }

    private static function setting(string $env, string $key): string
    {
        $value = getenv($env);
        if (is_string($value) && $value !== '') {
            return trim($value);
        }
        $value = $_SERVER[$env] ?? Config::get('toutify.' . $key, '');
        return is_string($value) ? trim($value) : '';
    }
}
