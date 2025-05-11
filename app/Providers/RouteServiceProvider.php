<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();

        // Custom rate limiter for 10 comments per day
        RateLimiter::for('comment-post', function (Request $request) {
            return Limit::perDay(10)->by($request->user()?->id ?: $request->ip());
        });
    }

    // The configureRateLimiting method is no longer necessary for the comment-post limiter.
    // If you want to add more custom rate limiters, you can do so here.
}
