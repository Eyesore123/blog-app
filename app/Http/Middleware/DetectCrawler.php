<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectCrawler
{
    protected $bots = [
        'googlebot',
        'bingbot',
        'slurp',
        'duckduckbot',
        'baiduspider',
        'yandex',
        'sogou',
        'exabot',
        'facebot',
        'ia_archiver',
    ];

    public function handle(Request $request, Closure $next)
    {
        $userAgent = strtolower($request->userAgent());
        $isCrawler = collect($this->bots)->contains(fn($bot) => str_contains($userAgent, $bot));

        // Store crawler info in request for Inertia and views
        $request->attributes->set('isCrawler', $isCrawler);

        return $next($request);
    }
}
