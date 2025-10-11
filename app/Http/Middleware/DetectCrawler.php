<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectCrawler
{
    protected $crawlers = [
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandex', 'sogou', 'exabot', 'facebot', 'ia_archiver'
    ];

    public function handle(Request $request, Closure $next)
    {
        $userAgent = strtolower($request->header('User-Agent', ''));

        $isCrawler = false;
        foreach ($this->crawlers as $crawler) {
            if (str_contains($userAgent, $crawler)) {
                $isCrawler = true;
                break;
            }
        }

        $request->attributes->set('isCrawler', $isCrawler);

        return $next($request);
    }
}
