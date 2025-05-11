<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;

class CommentPostRateLimiter
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next)
    {
        $key = $this->resolveKey($request);

        if ($this->limiter->tooManyAttempts($key, 10)) {
            return response()->json([
                'message' => 'You have exceeded the limit of 10 comments per day. Please try again tomorrow.',
                'retry_after' => $this->limiter->availableIn($key),
            ], 429);
        }

        $this->limiter->hit($key, 86400); // Set expiration to 1 day

        return $next($request);
    }

    protected function resolveKey(Request $request)
    {
        return 'comment-post|' . $request->user()->id; // or any other unique identifier
    }
}
