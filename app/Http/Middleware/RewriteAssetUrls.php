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
            
            // Force HTTPS for all URLs to your domain
            $appDomain = parse_url(config('app.url'), PHP_URL_HOST);
            if ($appDomain) {
                // Replace http:// with https:// for your domain
                $content = str_replace(
                    'http://' . $appDomain,
                    'https://' . $appDomain,
                    $content
                );
                
                // Also handle URLs without protocol (//domain.com)
                $content = str_replace(
                    'href="//',
                    'href="https://',
                    $content
                );
                $content = str_replace(
                    'src="//',
                    'src="https://',
                    $content
                );
                
                // Handle API calls and other fetch/axios requests
                $content = str_replace(
                    '"http://' . $appDomain . '/api/',
                    '"https://' . $appDomain . '/api/',
                    $content
                );
                $content = str_replace(
                    "'http://" . $appDomain . "/api/",
                    "'https://" . $appDomain . "/api/",
                    $content
                );
            }
            
            $response->setContent($content);
        }
        
        return $response;
    }
}
