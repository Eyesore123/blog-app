<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Http\Middleware\AdminMiddleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ContentSecurityPolicy;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Http\Middleware\CustomThrottleRequests;
use App\Http\Middleware\RewriteAssetUrls;
use App\Http\Middleware\HttpsRedirect;
use App\Http\Middleware\HandleCors;
use App\Http\Middleware\CheckCookieContent;
use App\Http\Middleware\RedirectOldDomain;
use App\Http\Middleware\AssignAnonId;

class Kernel extends HttpKernel
{
    protected $middlewareGroups = [
    'web' => [
        RedirectOldDomain::class,
        EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        AssignAnonId::class,
        ContentSecurityPolicy::class,
        HttpsRedirect::class,
        RewriteAssetUrls::class,
        CheckCookieContent::class,
    ],
        
        'api' => [
            HandleCors::class,
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'throttle' => CustomThrottleRequests::class,
        'comment-post' => \App\Http\Middleware\CommentPostRateLimiter::class,
        'csp' => ContentSecurityPolicy::class,
        'cors' => HandleCors::class,
    ];
}
