<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AssignAnonId
{
    public function handle(Request $request, Closure $next)
    {
        // If user is not logged in and no anonId cookie exists, generate one
        if (!$request->user() && !$request->cookie('anonId')) {
            $anonId = 'Anon' . random_int(1000, 9999);
            // Set cookie for 1 year
            cookie()->queue('anonId', $anonId, 525600); // minutes in 1 year
        }

        return $next($request);
    }
}
