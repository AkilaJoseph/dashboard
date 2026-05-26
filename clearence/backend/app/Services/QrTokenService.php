<?php

namespace App\Services;

use App\Models\Clearance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Issues and verifies short-lived HS256 JWTs for QR-code identity scanning.
 *
 * Tokens are signed with VERIFICATION_JWT_SECRET (separate from APP_KEY so that
 * rotating the app key does not invalidate outstanding QR tokens, and vice-versa).
 *
 * Token lifetime: 5 minutes — scanned nonces (jti) are stored in the cache for
 * that same window to prevent replay attacks.
 *
 * Audience: "qr-scan" — distinct from any future API JWT audience.
 *
 * No external library is used.  HS256 = HMAC-SHA256, which PHP ships natively.
 * The JWT structure is standard RFC 7519.
 *
 * .env entry needed:
 *   VERIFICATION_JWT_SECRET=<32+ random bytes as a hex or base64 string>
 *   Run: php artisan tinker --execute="echo bin2hex(random_bytes(32));"
 *   Then add:  VERIFICATION_JWT_SECRET=<output>
 */
class QrTokenService
{
    private const ALG      = 'HS256';
    private const AUDIENCE = 'qr-scan';
    private const TTL      = 300; // 5 minutes in seconds

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Issue a signed QR token for a student + clearance pair.
     *
     * @return array{token: string, expires_at: string}
     */
    public function issue(User $student, Clearance $clearance): array
    {
        $now = time();
        $exp = $now + self::TTL;

        $payload = [
            'iss'          => config('app.url'),
            'aud'          => self::AUDIENCE,
            'iat'          => $now,
            'exp'          => $exp,
            'jti'          => Str::uuid()->toString(),
            'sub'          => (string) $student->id,
            'clearance_id' => $clearance->id,
            'student_id'   => $student->student_id ?? '',
            'name'         => $student->name,
        ];

        return [
            'token'      => $this->encode($payload),
            'expires_at' => date('c', $exp),
        ];
    }

    /**
     * Verify a QR token and return its payload.
     *
     * Throws RuntimeException with a user-safe message on any failure.
     * Stores the jti in cache on first use to prevent replay attacks.
     *
     * @return object  stdClass decoded payload
     */
    public function verify(string $token): object
    {
        $secret = $this->secret();

        // 1. Structural split
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token format.');
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;

        // 2. Signature check
        $expected = $this->sign("{$headerB64}.{$payloadB64}", $secret);
        if (! hash_equals($expected, $sigB64)) {
            throw new RuntimeException('Token signature is invalid.');
        }

        // 3. Decode header (verify alg)
        $header = json_decode($this->base64UrlDecode($headerB64));
        if (($header->alg ?? '') !== self::ALG) {
            throw new RuntimeException('Unsupported token algorithm.');
        }

        // 4. Decode payload
        $payload = json_decode($this->base64UrlDecode($payloadB64));
        if (! $payload) {
            throw new RuntimeException('Token payload is unreadable.');
        }

        // 5. Expiry
        if (($payload->exp ?? 0) < time()) {
            throw new RuntimeException('QR code has expired. Please refresh the page.');
        }

        // 6. Audience
        if (($payload->aud ?? '') !== self::AUDIENCE) {
            throw new RuntimeException('Token audience mismatch.');
        }

        // 7. Replay-attack prevention — nonce (jti) must not have been used before
        $jti      = $payload->jti ?? '';
        $cacheKey = 'qr_nonce:' . $jti;

        if (! $jti || Cache::has($cacheKey)) {
            throw new RuntimeException('QR code has already been scanned. Ask the student to refresh.');
        }

        // Burn the nonce for the remainder of the token's lifetime
        $ttlRemaining = max(1, ($payload->exp - time()));
        Cache::put($cacheKey, true, $ttlRemaining);

        return $payload;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function secret(): string
    {
        $secret = config('app.verification_jwt_secret')
            ?? env('VERIFICATION_JWT_SECRET');

        if (empty($secret)) {
            throw new RuntimeException(
                'VERIFICATION_JWT_SECRET is not set. ' .
                'Run: php artisan tinker --execute="echo bin2hex(random_bytes(32));" ' .
                'and add VERIFICATION_JWT_SECRET=<output> to your .env file.'
            );
        }

        return $secret;
    }

    private function encode(array $payload): string
    {
        $secret = $this->secret();

        $header     = $this->base64UrlEncode(json_encode(['alg' => self::ALG, 'typ' => 'JWT']));
        $payloadB64 = $this->base64UrlEncode(json_encode($payload));
        $sig        = $this->sign("{$header}.{$payloadB64}", $secret);

        return "{$header}.{$payloadB64}.{$sig}";
    }

    private function sign(string $data, string $secret): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $data, $secret, true));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
