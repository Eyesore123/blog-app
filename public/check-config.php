<?php
echo "<h1>Configuration Check</h1>";

// Check database configuration
echo "<h2>Database Configuration</h2>";
echo "<pre>";
try {
    $default = config('database.default');
    echo "Default connection: $default\n\n";
    
    $connections = config('database.connections');
    echo "Available connections:\n";
    foreach ($connections as $name => $config) {
        echo "- $name\n";
        if ($name === $default) {
            echo "  Driver: " . ($config['driver'] ?? 'not set') . "\n";
            echo "  Host: " . ($config['host'] ?? 'not set') . "\n";
            echo "  Database: " . ($config['database'] ?? 'not set') . "\n";
            echo "  Username: " . ($config['username'] ?? 'not set') . "\n";
            echo "  Password: " . (isset($config['password']) ? '******' : 'not set') . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error reading database configuration: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Check cache configuration
echo "<h2>Cache Configuration</h2>";
echo "<pre>";
try {
    $default = config('cache.default');
    echo "Default cache driver: $default\n\n";
    
    $stores = config('cache.stores');
    echo "Available cache stores:\n";
    foreach ($stores as $name => $config) {
        echo "- $name\n";
        if ($name === $default) {
            echo "  Driver: " . ($config['driver'] ?? 'not set') . "\n";
            if (isset($config['path'])) {
                echo "  Path: " . $config['path'] . "\n";
                echo "  Path exists: " . (is_dir($config['path']) ? 'Yes' : 'No') . "\n";
                echo "  Path writable: " . (is_writable($config['path']) ? 'Yes' : 'No') . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error reading cache configuration: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Check storage directories
echo "<h2>Storage Directories</h2>";
echo "<pre>";
$storagePaths = [
    'app' => storage_path('app'),
    'framework' => storage_path('framework'),
    'framework/cache' => storage_path('framework/cache'),
    'framework/cache/data' => storage_path('framework/cache/data'),
    'framework/sessions' => storage_path('framework/sessions'),
    'framework/views' => storage_path('framework/views'),
    'logs' => storage_path('logs'),
];

foreach ($storagePaths as $name => $path) {
    echo "$name:\n";
    echo "  Path: $path\n";
    echo "  Exists: " . (is_dir($path) ? 'Yes' : 'No') . "\n";
    echo "  Writable: " . (is_writable($path) ? 'Yes' : 'No') . "\n";
}
echo "</pre>";

// Check environment variables
echo "<h2>Environment Variables</h2>";
echo "<pre>";
$env_vars = [];
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'DB_') === 0 || strpos($key, 'DATABASE') === 0 || 
        strpos($key, 'CACHE') === 0 || strpos($key, 'APP_') === 0) {
        // Mask passwords and sensitive information
        if (strpos($key, 'PASSWORD') !== false || strpos($key, 'URL') !== false || 
            strpos($key, 'KEY') !== false || strpos($key, 'SECRET') !== false) {
            $env_vars[$key] = '******';
        } else {
            $env_vars[$key] = $value;
        }
    }
}
print_r($env_vars);
echo "</pre>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
