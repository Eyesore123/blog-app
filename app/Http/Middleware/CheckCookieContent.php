<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCookieContent
{
    public function handle(Request $request, Closure $next)
    {
        $consent = $request->cookie('cookie_consent', null);
        app()->instance('cookie_consent', $consent);

        return $next($request);
    }
}