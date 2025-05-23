<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Update Environment Variables</h1>";

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

echo "<h2>Current Environment Variables</h2>";
echo "<pre>";
$currentVars = [
    'DB_CONNECTION' => getenv('DB_CONNECTION'),
    'DB_HOST' => getenv('DB_HOST'),
    'DB_PORT' => getenv('DB_PORT'),
    'DB_DATABASE' => getenv('DB_DATABASE'),
    'DB_USERNAME' => getenv('DB_USERNAME'),
    'DB_PASSWORD' => getenv('DB_PASSWORD') ? '******' : null,
    'DATABASE_URL' => getenv('DATABASE_URL') ? '******' : null,
];
print_r($currentVars);
echo "</pre>";

echo "<h2>Updating Environment Variables</h2>";

// Update environment variables
putenv('DB_CONNECTION=pgsql');
putenv('DB_HOST=' . $host);
putenv('DB_PORT=' . $port);
putenv('DB_DATABASE=' . $database);
putenv('DB_USERNAME=' . $username);
putenv('DB_PASSWORD=' . $password);

// Remove SQLite specific variables
putenv('DB_DATABASE_SQLITE=');

echo "<p>Environment variables updated in memory.</p>";

// Update .env file
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Create a backup
    $backupPath = $envPath . '.backup-' . date('Y-m-d-H-i-s');
    file_put_contents($backupPath, $envContent);
    echo "<p>Created backup of .env file at: $backupPath</p>";
    
    // Update DB_CONNECTION
    $envContent = preg_replace('/^DB_CONNECTION=.*$/m', 'DB_CONNECTION=pgsql', $envContent);
    
    // Update DB_HOST
    $envContent = preg_replace('/^DB_HOST=.*$/m', 'DB_HOST=' . $host, $envContent);
    
    // Update DB_PORT
    $envContent = preg_replace('/^DB_PORT=.*$/m', 'DB_PORT=' . $port, $envContent);
    
    // Update DB_DATABASE
    $envContent = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $database, $envContent);
    
    // Update DB_USERNAME
    $envContent = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $username, $envContent);
    
    // Update DB_PASSWORD
    $envContent = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . $password, $envContent);
    
    // Remove any SQLite specific variables
    $envContent = preg_replace('/^DB_DATABASE_SQLITE=.*$/m', '', $envContent);
    
    // Write the updated .env file
    if (file_put_contents($envPath, $envContent)) {
        echo "<p>.env file updated successfully.</p>";
    } else {
        echo "<p>Failed to update .env file. Check permissions.</p>";
    }
} else {
    echo "<p>.env file not found. Creating a new one.</p>";
    
    // Create a new .env file
    $newEnvContent = <<<EOD
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://blog-app-production-16c2.up.railway.app

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=$host
DB_PORT=$port
DB_DATABASE=$database
DB_USERNAME=$username
DB_PASSWORD=$password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
EOD;
    
    if (file_put_contents($envPath, $newEnvContent)) {
        echo "<p>New .env file created successfully.</p>";
    } else {
        echo "<p>Failed to create .env file. Check permissions.</p>";
    }
}

echo "<h2>Updated Environment Variables</h2>";
echo "<pre>";
$updatedVars = [
    'DB_CONNECTION' => getenv('DB_CONNECTION'),
    'DB_HOST' => getenv('DB_HOST'),
    'DB_PORT' => getenv('DB_PORT'),
    'DB_DATABASE' => getenv('DB_DATABASE'),
    'DB_USERNAME' => getenv('DB_USERNAME'),
    'DB_PASSWORD' => getenv('DB_PASSWORD') ? '******' : null,
    'DATABASE_URL' => getenv('DATABASE_URL') ? '******' : null,
];
print_r($updatedVars);
echo "</pre>";

echo "<h2>Clear Configuration Cache</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan config:clear', $output, $return_var);
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
