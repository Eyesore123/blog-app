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

echo "<h1>Database Configuration Check</h1>";

// Check environment variables
echo "<h2>Environment Variables</h2>";
echo "<pre>";
$env_vars = [];
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'DB_') === 0 || strpos($key, 'DATABASE') === 0) {
        // Mask passwords and sensitive information
        if (strpos($key, 'PASSWORD') !== false || strpos($key, 'URL') !== false) {
            $env_vars[$key] = '******';
        } else {
            $env_vars[$key] = $value;
        }
    }
}
print_r($env_vars);
echo "</pre>";

// Create a .env file with the correct database configuration
$envPath = __DIR__ . '/../.env';
$envContent = file_exists($envPath) ? file_get_contents($envPath) : '';

// Parse the DATABASE_URL
$dbParts = parse_url($databaseUrl);
$host = $dbParts['host'] ?? '';
$port = $dbParts['port'] ?? 5432;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

// Update the database configuration
$envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=pgsql', $envContent);
$envContent = preg_replace('/DB_HOST=.*/', "DB_HOST=$host", $envContent);
$envContent = preg_replace('/DB_PORT=.*/', "DB_PORT=$port", $envContent);
$envContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE=$database", $envContent);
$envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME=$username", $envContent);
$envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD=$password", $envContent);

// If the configuration doesn't exist, add it
if (!preg_match('/DB_CONNECTION=/', $envContent)) {
    $envContent .= "\nDB_CONNECTION=pgsql";
}
if (!preg_match('/DB_HOST=/', $envContent)) {
    $envContent .= "\nDB_HOST=$host";
}
if (!preg_match('/DB_PORT=/', $envContent)) {
    $envContent .= "\nDB_PORT=$port";
}
if (!preg_match('/DB_DATABASE=/', $envContent)) {
    $envContent .= "\nDB_DATABASE=$database";
}
if (!preg_match('/DB_USERNAME=/', $envContent)) {
    $envContent .= "\nDB_USERNAME=$username";
}
if (!preg_match('/DB_PASSWORD=/', $envContent)) {
    $envContent .= "\nDB_PASSWORD=$password";
}

// Write the updated .env file
if (file_put_contents($envPath, $envContent)) {
    echo "<p>.env file updated successfully.</p>";
} else {
    echo "<p>Failed to update .env file. Check permissions.</p>";
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

// Check the current database configuration
echo "<h2>Current Database Configuration</h2>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan db:show', $output, $return_var);
echo "<pre>";
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
