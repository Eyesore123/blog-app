<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectOldDomain
{
    public function handle(Request $request, Closure $next)
    {
        $oldDomain = 'blog-app-production-16c2.up.railway.app';
        $newDomain = 'https://blog.joniputkinen.com';

        // Redirect old domain traffic to new domain
        if ($request->getHost() === $oldDomain) {
            $newUrl = $newDomain . $request->getRequestUri();
            return redirect()->to($newUrl, 301); // permanent redirect
        }

        return $next($request);
    }
}
