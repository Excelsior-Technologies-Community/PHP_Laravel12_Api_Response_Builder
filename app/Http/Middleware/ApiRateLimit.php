<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    private int $maxAttempts = 60;

    private int $decaySeconds = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $key = 'api|' . $request->ip();

        if (RateLimiter::tooManyAttempts(
            $key,
            $this->maxAttempts
        )) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'status' => false,
                'message' => 'Too many requests. Please try again later.',
                'errors' => null,

                'meta' => [
                    'api_version' => 'v1',
                    'timestamp' => now()->toIso8601String(),
                    'request_id' => $request->header('X-Request-ID'),
                ],

                'rate_limit' => [
                    'limit' => $this->maxAttempts,
                    'remaining' => 0,
                    'retry_after_seconds' => $retryAfter,
                    'reset_at' => now()
                        ->addSeconds($retryAfter)
                        ->toIso8601String(),
                ],
            ], 429)
                ->header(
                    'X-RateLimit-Limit',
                    $this->maxAttempts
                )
                ->header(
                    'X-RateLimit-Remaining',
                    0
                )
                ->header(
                    'Retry-After',
                    $retryAfter
                );
        }

        RateLimiter::hit(
            $key,
            $this->decaySeconds
        );

        $remaining = RateLimiter::remaining(
            $key,
            $this->maxAttempts
        );

        $resetAfter = RateLimiter::availableIn($key);

        $request->attributes->set(
            'rate_limit_limit',
            $this->maxAttempts
        );

        $request->attributes->set(
            'rate_limit_remaining',
            $remaining
        );

        $request->attributes->set(
            'rate_limit_reset_at',
            now()
                ->addSeconds($resetAfter)
                ->toIso8601String()
        );

        $response = $next($request);

        $response->headers->set(
            'X-RateLimit-Limit',
            $this->maxAttempts
        );

        $response->headers->set(
            'X-RateLimit-Remaining',
            $remaining
        );

        $response->headers->set(
            'X-RateLimit-Reset',
            now()->addSeconds($resetAfter)->timestamp
        );

        return $response;
    }
}
