<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

class CustomThrottle extends ThrottleRequests
{
    protected function buildResponse(Request $request, $key, $maxAttempts)
    {
        // Customize the response here
        $response = response()->json([
            'message' => 'You have exceeded the maximum number of comment posts.',
            'retry_after' => $this->limiter->availableIn($key),
        ], 429);

        // Optionally, you can add headers like 'Retry-After'
        $response->headers->set('Retry-After', $this->limiter->availableIn($key));

        return $response;
    }
}
