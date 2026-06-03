<?php

namespace Botble\Marketplace\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleFeedGuestProductPost
{
    protected const MAX_ATTEMPTS = 1;

    protected const DECAY_SECONDS = 1;

    public function handle(Request $request, Closure $next): Response
    {
        $key = 'feed-guest-product:ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return $this->tooManyAttemptsResponse($key);
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return $next($request);
    }

    protected function tooManyAttemptsResponse(string $key): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'message' => __('Please wait :seconds second(s) before posting another product.', [
                'seconds' => max(1, $retryAfter),
            ]),
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => self::MAX_ATTEMPTS,
        ]);
    }
}
