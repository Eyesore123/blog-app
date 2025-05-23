<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    
    {

        Log::info('ContentSecurityPolicy middleware applied.');
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', "
            default-src 'self' https: data:;
            script-src 'self' 'unsafe-inline' 'unsafe-eval' https:;
            style-src 'self' 'unsafe-inline' https:;
            img-src 'self' https: data:;
            font-src 'self' https: data:;
            connect-src 'self' https:;
            frame-src 'self' https:;
            object-src 'none';
            base-uri 'self';
            form-action 'self';
            frame-ancestors 'self';
            upgrade-insecure-requests;
        ");

        return $response;
    }
}