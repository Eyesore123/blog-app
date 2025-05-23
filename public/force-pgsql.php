<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Forcing PostgreSQL Configuration</h1>";

// Get the DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo "<p>DATABASE_URL is not set!</p>";
    exit;
}

// Parse the DATABASE_URL
$dbParts = parse_url($databaseUrl);
$host = $dbParts['host'] ?? '';
$port = $dbParts['port'] ?? 5432;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

// Create a direct database configuration file
$configPath = __DIR__ . '/../config/database.php';
$backupPath = $configPath . '.original';

// Backup the original file if it hasn't been backed up yet
if (!file_exists($backupPath) && file_exists($configPath)) {
    copy($configPath, $backupPath);
    echo "<p>Original database.php backed up to database.php.original</p>";
}

// Create a new database.php file with hardcoded PostgreSQL configuration
$newConfig = <<<EOD
<?php

return [
    'default' => 'pgsql',
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => '{$host}',
            'port' => {$port},
            'database' => '{$database}',
            'username' => '{$username}',
            'password' => '{$password}',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
    ],
    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', 'laravel_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
EOD;

// Write the new configuration file
if (file_put_contents($configPath, $newConfig)) {
    echo "<p>Database configuration updated with hardcoded PostgreSQL settings.</p>";
} else {
    echo "<p>Failed to update database configuration. Check permissions.</p>";
}

// Create a direct cache configuration file
$cacheConfigPath = __DIR__ . '/../config/cache.php';
$cacheBackupPath = $cacheConfigPath . '.original';

// Backup the original file if it hasn't been backed up yet
if (!file_exists($cacheBackupPath) && file_exists($cacheConfigPath)) {
    copy($cacheConfigPath, $cacheBackupPath);
    echo "<p>Original cache.php backed up to cache.php.original</p>";
}

// Define the base storage path
$basePath = __DIR__ . '/../storage';

// Create a new cache.php file with file driver as default
$newCacheConfig = <<<EOD
<?php

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_DRIVER', 'file'),
    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'file' => [
            'driver' => 'file',
            'path' => '{$basePath}/framework/cache/data',
            'lock_path' => '{$basePath}/framework/cache/data',
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
    ],
    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),
];
EOD;

// Write the new cache configuration file
if (file_put_contents($cacheConfigPath, $newCacheConfig)) {
    echo "<p>Cache configuration updated to use file driver by default.</p>";
} else {
    echo "<p>Failed to update cache configuration. Check permissions.</p>";
}

// Create the storage directories if they don't exist
$cachePath = $basePath . '/framework/cache/data';
if (!is_dir($cachePath)) {
    if (mkdir($cachePath, 0755, true)) {
        echo "<p>Created cache directory at: $cachePath</p>";
    } else {
        echo "<p>Failed to create cache directory. Check permissions.</p>";
    }
}

// Create a .env file with the correct database configuration
$envPath = __DIR__ . '/../.env';
$envContent = file_exists($envPath) ? file_get_contents($envPath) : '';

// Update or add the database configuration
$envLines = [
    'DB_CONNECTION=pgsql',
    "DB_HOST=$host",
    "DB_PORT=$port",
    "DB_DATABASE=$database",
    "DB_USERNAME=$username",
    "DB_PASSWORD=$password",
    'CACHE_DRIVER=file'
];

foreach ($envLines as $line) {
    $key = substr($line, 0, strpos($line, '='));
    
    // Check if the key exists in the .env file
    if (preg_match('/^' . preg_quote($key) . '=/m', $envContent)) {
        // Replace the existing line
        $envContent = preg_replace('/^' . preg_quote($key) . '=.*/m', $line, $envContent);
    } else {
        // Add the line if it doesn't exist
        $envContent .= "\n" . $line;
    }
}

// Write the updated .env file
if (file_put_contents($envPath, $envContent)) {
    echo "<p>.env file updated with PostgreSQL and file cache configuration.</p>";
} else {
    echo "<p>Failed to update .env file. Check permissions.</p>";
}

echo "<p>Done. <a href='/check-config.php'>Check Configuration</a></p>";
