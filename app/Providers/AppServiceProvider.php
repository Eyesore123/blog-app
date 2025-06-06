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
         if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

            Inertia::share([
            'flash' => function () {
                return [
                    'alert' => session('alert'),
                ];
            },
        ]);

        // Register custom Blade directive
        Blade::directive('viteCustom', function ($expression) {
            return "<?php echo \App\Helpers\ViteHelper::viteAssets($expression); ?>";
        });
        
        // Check if the manifest exists in the .vite directory and copy it if needed
        $manifestPath = public_path('build/manifest.json');
        $altManifestPath = public_path('build/.vite/manifest.json');
        
        if (!file_exists($manifestPath) && file_exists($altManifestPath)) {
            File::copy($altManifestPath, $manifestPath);
        }
    }
}
