<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RewriteAssetUrls
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        if ($response->headers->get('Content-Type') === 'text/html; charset=UTF-8') {
            $content = $response->getContent();
            
            // Replace /assets/ with /build/assets/ in URLs
            $content = str_replace('"/assets/', '"/build/assets/', $content);
            $content = str_replace("'/assets/", "'/build/assets/", $content);
            $content = str_replace('(/assets/', '(/build/assets/', $content);
            
            $response->setContent($content);
        }
        
        return $response;
    }
}
