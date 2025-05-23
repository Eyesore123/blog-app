<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Direct Cache Fix</h1>";

// Create a direct cache configuration override
$cachePath = __DIR__ . '/../app/Providers/CacheOverrideServiceProvider.php';
$cacheContent = <<<EOD
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class CacheOverrideServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Force file cache driver
        Config::set('cache.default', 'file');
        Config::set('session.driver', 'file');
        Config::set('queue.default', 'sync');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
EOD;

// Write the service provider file
if (file_put_contents($cachePath, $cacheContent)) {
    echo "<p>Cache override service provider created at: $cachePath</p>";
} else {
    echo "<p>Failed to create cache override service provider. Check permissions.</p>";
}

// Register the service provider in config/app.php
$appConfigPath = __DIR__ . '/../config/app.php';
if (file_exists($appConfigPath)) {
    $appConfigContent = file_get_contents($appConfigPath);
    
    // Create a backup
    $backupPath = $appConfigPath . '.backup-' . date('Y-m-d-H-i-s');
    file_put_contents($backupPath, $appConfigContent);
    echo "<p>Created backup of app.php config file at: $backupPath</p>";
    
    // Find the providers array
    $providersPos = strpos($appConfigContent, "'providers' => [");
    if ($providersPos !== false) {
        $endOfProvidersArrayPos = strpos($appConfigContent, "],", $providersPos);
        if ($endOfProvidersArrayPos !== false) {
            // Insert our service provider
            $newContent = substr($appConfigContent, 0, $endOfProvidersArrayPos);
            $newContent .= "        App\\Providers\\CacheOverrideServiceProvider::class,\n        ";
            $newContent .= substr($appConfigContent, $endOfProvidersArrayPos);
            
            // Write the modified app.php config
            if (file_put_contents($appConfigPath, $newContent)) {
                echo "<p>app.php config file updated to include the cache override service provider.</p>";
            } else {
                echo "<p>Failed to update app.php config file. Check permissions.</p>";
            }
        } else {
            echo "<p>Could not find the end of providers array in app.php config file.</p>";
        }
    } else {
        echo "<p>Could not find the providers array in app.php config file.</p>";
    }
} else {
    echo "<p>app.php config file not found.</p>";
}

// Create a direct cache fix in AppServiceProvider
$appServiceProviderPath = __DIR__ . '/../app/Providers/AppServiceProvider.php';
if (file_exists($appServiceProviderPath)) {
    $appServiceProviderContent = file_get_contents($appServiceProviderPath);
    
    // Create a backup
    $backupPath = $appServiceProviderPath . '.backup-' . date('Y-m-d-H-i-s');
    file_put_contents($backupPath, $appServiceProviderContent);
    echo "<p>Created backup of AppServiceProvider.php at: $backupPath</p>";
    
    // Find the register method
    $registerPos = strpos($appServiceProviderContent, "public function register");
    if ($registerPos !== false) {
        $registerBodyPos = strpos($appServiceProviderContent, "{", $registerPos);
        if ($registerBodyPos !== false) {
            // Insert our cache override code
            $newContent = substr($appServiceProviderContent, 0, $registerBodyPos + 1);
            $newContent .= "\n        // Force file cache driver\n";
            $newContent .= "        \$this->app['config']->set('cache.default', 'file');\n";
            $newContent .= "        \$this->app['config']->set('session.driver', 'file');\n";
            $newContent .= "        \$this->app['config']->set('queue.default', 'sync');\n";
            $newContent .= substr($appServiceProviderContent, $registerBodyPos + 1);
            
            // Write the modified AppServiceProvider
            if (file_put_contents($appServiceProviderPath, $newContent)) {
                echo "<p>AppServiceProvider.php updated to include cache override code.</p>";
            } else {
                echo "<p>Failed to update AppServiceProvider.php. Check permissions.</p>";
            }
        } else {
            echo "<p>Could not find the register method body in AppServiceProvider.php.</p>";
        }
    } else {
        echo "<p>Could not find the register method in AppServiceProvider.php.</p>";
    }
} else {
    echo "<p>AppServiceProvider.php not found.</p>";
}

// Create a direct cache fix in index.php
$indexPath = __DIR__ . '/../public/index.php';
if (file_exists($indexPath)) {
    $indexContent = file_get_contents($indexPath);
    
    // Create a backup
    $backupPath = $indexPath . '.backup-' . date('Y-m-d-H-i-s');
    file_put_contents($backupPath, $indexContent);
    echo "<p>Created backup of index.php at: $backupPath</p>";
    
    // Find the position to insert our code
    $appPos = strpos($indexContent, "\$app = require_once __DIR__.'/../bootstrap/app.php';");
    if ($appPos !== false) {
        // Insert our cache override code
        $newContent = substr($indexContent, 0, $appPos);
        $newContent .= "// Force file cache driver\n";
        $newContent .= "putenv('CACHE_DRIVER=file');\n";
        $newContent .= "putenv('SESSION_DRIVER=file');\n";
        $newContent .= "putenv('QUEUE_CONNECTION=sync');\n\n";
        $newContent .= substr($indexContent, $appPos);
        
        // Write the modified index.php
        if (file_put_contents($indexPath, $newContent)) {
            echo "<p>index.php updated to include cache override code.</p>";
        } else {
            echo "<p>Failed to update index.php. Check permissions.</p>";
        }
    } else {
        echo "<p>Could not find the app initialization in index.php.</p>";
    }
} else {
    echo "<p>index.php not found.</p>";
}

echo "<h2>Clear Cache</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan cache:clear', $output, $return_var);
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<h2>Clear Config Cache</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan config:clear', $output, $return_var);
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
