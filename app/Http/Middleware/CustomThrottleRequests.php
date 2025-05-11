<?php

use Illuminate\Cache\RateLimiter;

class CustomThrottleRequests extends BaseThrottleRequests
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    protected function buildResponse(Request $request, $key, $maxAttempts)
    {
        return response()->json([
            'message' => 'You have exceeded the limit of 10 comments per day. Please try again tomorrow.',
            'retry_after' => $this->limiter->availableIn($key),
        ], 429);
    }
}
