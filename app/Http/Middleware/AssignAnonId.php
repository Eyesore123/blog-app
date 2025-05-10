<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AssignAnonId
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() && !$request->session()->has('anonId')) {
            $anonId = 'Anon' . random_int(1000, 9999);
            $request->session()->put('anonId', $anonId);
        }

        return $next($request);
    }
}
