<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpsRedirect
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If we're in production and not already using HTTPS
        if (env('APP_ENV') !== 'local' && !$request->secure()) {
            // Redirect to the same URL but with HTTPS
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
