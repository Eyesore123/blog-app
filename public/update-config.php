<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Get the DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo "<p>DATABASE_URL is not set!</p>";
    exit;
}

echo "<h1>Updating Database Configuration</h1>";

// Parse the DATABASE_URL
$dbParts = parse_url($databaseUrl);
$host = $dbParts['host'] ?? '';
$port = $dbParts['port'] ?? 5432;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

// Update the database.php configuration file
$databaseConfigPath = __DIR__ . '/../config/database.php';

if (!file_exists($databaseConfigPath)) {
    echo "<p>Database configuration file not found at: $databaseConfigPath</p>";
    exit;
}

$databaseConfig = file_get_contents($databaseConfigPath);

// Create a backup of the original file
file_put_contents($databaseConfigPath . '.bak', $databaseConfig);
echo "<p>Created backup of database.php at: {$databaseConfigPath}.bak</p>";

// Update the default database connection
$databaseConfig = preg_replace(
    "/'default' => env\('DB_CONNECTION', '.*?'\),/",
    "'default' => env('DB_CONNECTION', 'pgsql'),",
    $databaseConfig
);

// Write the updated configuration
if (file_put_contents($databaseConfigPath, $databaseConfig)) {
    echo "<p>Database configuration updated successfully.</p>";
} else {
    echo "<p>Failed to update database configuration. Check permissions.</p>";
}

// Create a bootstrap/cache directory if it doesn't exist
$cacheDir = __DIR__ . '/../bootstrap/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
    echo "<p>Created cache directory at: $cacheDir</p>";
}

// Clear the configuration cache
echo "<h2>Clearing Configuration Cache</h2>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan config:clear', $output, $return_var);
echo "<pre>";
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

// Create a temporary script to set the database connection at runtime
$bootstrapPath = __DIR__ . '/../bootstrap/app.php';
$bootstrapContent = file_exists($bootstrapPath) ? file_get_contents($bootstrapPath) : '';

// Create a backup of the original file
file_put_contents($bootstrapPath . '.bak', $bootstrapContent);
echo "<p>Created backup of app.php at: {$bootstrapPath}.bak</p>";

// Add code to set the database connection at runtime
$insertPosition = strpos($bootstrapContent, 'return $app;');
if ($insertPosition !== false) {
    $newContent = substr($bootstrapContent, 0, $insertPosition);
    $newContent .= <<<'EOD'
// Force PostgreSQL connection
$app->afterBootstrapping(Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class, function ($app) {
    $databaseUrl = getenv('DATABASE_URL');
    if ($databaseUrl) {
        $dbParts = parse_url($databaseUrl);
        $host = $dbParts['host'] ?? '';
        $port = $dbParts['port'] ?? 5432;
        $database = ltrim($dbParts['path'] ?? '', '/');
        $username = $dbParts['user'] ?? '';
        $password = $dbParts['pass'] ?? '';
        
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.host' => $host,
            'database.connections.pgsql.port' => $port,
            'database.connections.pgsql.database' => $database,
            'database.connections.pgsql.username' => $username,
            'database.connections.pgsql.password' => $password,
        ]);
    }
});

EOD;
    $newContent .= substr($bootstrapContent, $insertPosition);
    
    if (file_put_contents($bootstrapPath, $newContent)) {
        echo "<p>Bootstrap file updated successfully.</p>";
    } else {
        echo "<p>Failed to update bootstrap file. Check permissions.</p>";
    }
} else {
    echo "<p>Could not find insertion point in bootstrap file.</p>";
}

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
