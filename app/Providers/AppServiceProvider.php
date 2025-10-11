<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

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
        // Force HTTPS in production
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        // Share global data with Inertia
        Inertia::share([
            'flash' => fn () => [
                'alert' => session('alert'),
            ],

            'isCrawler' => fn () => request()->attributes->get('isCrawler', false),

            // If you later add cookie consent or other globals, include them here
            // 'cookieConsent' => fn () => app('cookieConsent'),
        ]);

        // Register custom Blade directive for Vite
        Blade::directive('viteCustom', function ($expression) {
            return "<?php echo \App\Helpers\ViteHelper::viteAssets($expression); ?>";
        });

        // Copy .vite manifest if it exists but the main one doesn't
        $manifestPath = public_path('build/manifest.json');
        $altManifestPath = public_path('build/.vite/manifest.json');

        if (!file_exists($manifestPath) && file_exists($altManifestPath)) {
            File::copy($altManifestPath, $manifestPath);
        }
    }
}
