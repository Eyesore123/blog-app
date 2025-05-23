<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $cspHeader = "default-src 'self' https:; " .
                     "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; " .
                     "style-src 'self' 'unsafe-inline' https:; " .
                     "img-src 'self' data: https:; " .
                     "font-src 'self' https:; " .
                     "connect-src 'self' https:; " .
                     "frame-ancestors 'self'; " .
                     "upgrade-insecure-requests;";
        
        $response->headers->set('Content-Security-Policy', $cspHeader);
        
        return $response;
    }
}
