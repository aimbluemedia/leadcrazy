<?php

declare(strict_types=1);

namespace App\Support\Toutify;

/**
 * Just enough JWT for OpenID Connect: split, decode, and check an RS* or ES*
 * signature against a key from a JWKS. Claim checks (issuer, audience, expiry,
 * nonce) are the caller's job, because they differ between an ID token and a
 * logout token.
 *
 * Written by hand because the host has no Composer; uses only ext-openssl.
 * "none" and HMAC algorithms are refused outright: a token from Toutify is
 * always signed with one of its published keys.
 */
final class Jwt
{
    private const ALGS = [
        'RS256' => ['RSA', OPENSSL_ALGO_SHA256, 0],
        'RS384' => ['RSA', OPENSSL_ALGO_SHA384, 0],
        'RS512' => ['RSA', OPENSSL_ALGO_SHA512, 0],
        'ES256' => ['EC', OPENSSL_ALGO_SHA256, 32],
        'ES384' => ['EC', OPENSSL_ALGO_SHA384, 48],
        'ES512' => ['EC', OPENSSL_ALGO_SHA512, 66],
    ];

    /**
     * @return array{header:array<string,mixed>,claims:array<string,mixed>,signed:string,signature:string}
     * @throws \RuntimeException on anything malformed
     */
    public static function parse(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \RuntimeException('token is not a JWS');
        }
        $header = json_decode(self::b64decode($parts[0]), true);
        $claims = json_decode(self::b64decode($parts[1]), true);
        if (!is_array($header) || !is_array($claims)) {
            throw new \RuntimeException('token header or claims are not JSON objects');
        }
        return ['header' => $header, 'claims' => $claims, 'signed' => $parts[0] . '.' . $parts[1], 'signature' => self::b64decode($parts[2])];
    }

    /**
     * Verifies the signature with the matching key from $jwks and returns the
     * parsed token.
     *
     * @param array<string,mixed> $jwks  {"keys": [...]}
     * @return array{header:array<string,mixed>,claims:array<string,mixed>,signed:string,signature:string}
     */
    public static function verify(string $jwt, array $jwks): array
    {
        $token = self::parse($jwt);
        $alg = (string) ($token['header']['alg'] ?? '');
        if (!isset(self::ALGS[$alg])) {
            throw new \RuntimeException('unsupported signing algorithm: ' . $alg);
        }
        [$kty, $hash, $size] = self::ALGS[$alg];

        $key = self::findKey($jwks, $kty, $alg, isset($token['header']['kid']) ? (string) $token['header']['kid'] : null);
        if ($key === null) {
            throw new UnknownKey('no matching signing key');
        }

        $signature = $token['signature'];
        if ($kty === 'EC') {
            if (strlen($signature) !== 2 * $size) {
                throw new \RuntimeException('bad ECDSA signature length');
            }
            $signature = self::ecdsaToDer($signature, $size);
        }

        $pem = self::jwkToPem($key);
        $ok = openssl_verify($token['signed'], $signature, $pem, $hash);
        if ($ok !== 1) {
            throw new \RuntimeException('signature does not verify');
        }
        return $token;
    }

    /** @param array<string,mixed> $jwks @return array<string,mixed>|null */
    private static function findKey(array $jwks, string $kty, string $alg, ?string $kid): ?array
    {
        $candidates = [];
        foreach ((array) ($jwks['keys'] ?? []) as $key) {
            if (!is_array($key) || ($key['kty'] ?? '') !== $kty) {
                continue;
            }
            if (isset($key['use']) && $key['use'] !== 'sig') {
                continue;
            }
            if (isset($key['alg']) && $key['alg'] !== $alg) {
                continue;
            }
            if ($kid !== null) {
                if (($key['kid'] ?? null) === $kid) {
                    return $key;
                }
                continue;
            }
            $candidates[] = $key;
        }
        // No kid in the token: only safe when there is exactly one candidate.
        return count($candidates) === 1 ? $candidates[0] : null;
    }

    /** @param array<string,mixed> $jwk */
    public static function jwkToPem(array $jwk): string
    {
        if (($jwk['kty'] ?? '') === 'RSA') {
            $n = self::b64decode((string) ($jwk['n'] ?? ''));
            $e = self::b64decode((string) ($jwk['e'] ?? ''));
            if ($n === '' || $e === '') {
                throw new \RuntimeException('RSA key is missing n or e');
            }
            $rsa = self::der(0x30, self::derInt($n) . self::derInt($e));
            $algo = self::der(0x30, self::der(0x06, "\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01") . "\x05\x00");
            $spki = self::der(0x30, $algo . self::der(0x03, "\x00" . $rsa));
        } elseif (($jwk['kty'] ?? '') === 'EC') {
            $curves = [
                'P-256' => ["\x2a\x86\x48\xce\x3d\x03\x01\x07", 32],
                'P-384' => ["\x2b\x81\x04\x00\x22", 48],
                'P-521' => ["\x2b\x81\x04\x00\x23", 66],
            ];
            $crv = (string) ($jwk['crv'] ?? '');
            if (!isset($curves[$crv])) {
                throw new \RuntimeException('unsupported curve: ' . $crv);
            }
            [$oid, $len] = $curves[$crv];
            $x = str_pad(self::b64decode((string) ($jwk['x'] ?? '')), $len, "\x00", STR_PAD_LEFT);
            $y = str_pad(self::b64decode((string) ($jwk['y'] ?? '')), $len, "\x00", STR_PAD_LEFT);
            $algo = self::der(0x30, self::der(0x06, "\x2a\x86\x48\xce\x3d\x02\x01") . self::der(0x06, $oid));
            $spki = self::der(0x30, $algo . self::der(0x03, "\x00\x04" . $x . $y));
        } else {
            throw new \RuntimeException('unsupported key type');
        }
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    public static function b64decode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $decoded = base64_decode($data . str_repeat('=', (4 - strlen($data) % 4) % 4), true);
        if ($decoded === false) {
            throw new \RuntimeException('bad base64url');
        }
        return $decoded;
    }

    public static function b64encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /** JOSE ECDSA signatures are r||s; OpenSSL wants a DER SEQUENCE of two INTEGERs. */
    private static function ecdsaToDer(string $sig, int $size): string
    {
        return self::der(0x30, self::derInt(substr($sig, 0, $size)) . self::derInt(substr($sig, $size)));
    }

    private static function derInt(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '' || (ord($bytes[0]) & 0x80)) {
            $bytes = "\x00" . $bytes;
        }
        return self::der(0x02, $bytes);
    }

    private static function der(int $tag, string $value): string
    {
        $len = strlen($value);
        if ($len < 0x80) {
            $encoded = chr($len);
        } else {
            $bytes = ltrim(pack('N', $len), "\x00");
            $encoded = chr(0x80 | strlen($bytes)) . $bytes;
        }
        return chr($tag) . $encoded . $value;
    }
}
