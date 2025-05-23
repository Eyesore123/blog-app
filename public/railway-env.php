<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Railway Environment Variables</h1>";

echo "<p>This script will help you update your Railway environment variables.</p>";

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

echo "<h2>Instructions</h2>";
echo "<p>Follow these steps to update your Railway environment variables:</p>";
echo "<ol>";
echo "<li>Go to your Railway dashboard</li>";
echo "<li>Select your project</li>";
echo "<li>Click on your web service (blog-app)</li>";
echo "<li>Go to the 'Variables' tab</li>";
echo "<li>Remove the following variables if they exist:";
echo "<ul>";
echo "<li>DB_CONNECTION=sqlite</li>";
echo "<li>DB_DATABASE=database/database.sqlite</li>";
echo "</ul>";
echo "</li>";
echo "<li>Add or update the following variables:";
echo "<ul>";
echo "<li>DB_CONNECTION=pgsql</li>";
echo "<li>DB_HOST=$host</li>";
echo "<li>DB_PORT=$port</li>";
echo "<li>DB_DATABASE=$database</li>";
echo "<li>DB_USERNAME=$username</li>";
echo "<li>DB_PASSWORD=[your password]</li>";
echo "</ul>";
echo "</li>";
echo "<li>Click 'Save Changes'</li>";
echo "<li>Redeploy your application</li>";
echo "</ol>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
