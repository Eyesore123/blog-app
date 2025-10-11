<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Security headers that analyzers look for
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' https://blog.joniputkinen.com 'unsafe-inline' 'unsafe-eval'; " .
            "script-src-elem 'self' https://blog.joniputkinen.com 'unsafe-inline' 'unsafe-eval'; " .
            "style-src 'self' 'unsafe-inline' https://blog.joniputkinen.com; " .
            "connect-src 'self' https://blog.joniputkinen.com https://joniputkinen.com; " .
            "img-src 'self' https://blog.joniputkinen.com https://joniputkinen.com; " .
            "object-src 'none'; " .
            "frame-src 'none'; " .
            "upgrade-insecure-requests"
        );
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
