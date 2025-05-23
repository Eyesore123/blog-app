<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Check if Vite manifest exists
        if (!File::exists(public_path('build/manifest.json'))) {
            Log::error('Vite manifest not found at: ' . public_path('build/manifest.json'));
        }
    }
}
