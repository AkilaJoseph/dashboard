<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    // How long to cache idempotency records (24 hours)
    private const TTL_HOURS = 24;

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Idempotency-Key');

        // No header → pass straight through; online form submissions are unaffected
        if (!$key || !Auth::check()) {
            return $next($request);
        }

        // Normalise: truncate to column length to prevent DB errors on malformed input
        $key = substr(trim($key), 0, 64);

        $userId  = Auth::id();
        $record  = IdempotencyKey::where('key', $key)
                                  ->where('user_id', $userId)
                                  ->first();

        // ── Cache hit ─────────────────────────────────────────────────────────
        if ($record && !$record->isExpired()) {
            return response()->json([
                'ok'      => true,
                'replayed'=> true,
                'message' => 'Request already processed.',
                'status'  => $record->response_status,
            ], 200);
        }

        // Expired record: delete it so the request goes through fresh
        if ($record && $record->isExpired()) {
            $record->delete();
        }

        // ── Fresh request ─────────────────────────────────────────────────────
        $response = $next($request);

        // Only cache successful responses (2xx or 3xx redirects)
        if ($response->getStatusCode() < 400) {
            IdempotencyKey::create([
                'key'             => $key,
                'user_id'         => $userId,
                'response_status' => $response->getStatusCode(),
                'response_body'   => $response->isRedirect()
                                        ? $response->headers->get('Location')
                                        : null,
                'created_at'      => now(),
                'expires_at'      => now()->addHours(self::TTL_HOURS),
            ]);
        }

        return $response;
    }
}
