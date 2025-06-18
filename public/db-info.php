<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Display PHP information
echo "<h1>PHP Information</h1>";
echo "<h2>PHP Version</h2>";
echo PHP_VERSION;

echo "<h2>PDO Drivers</h2>";
echo "<pre>";
print_r(PDO::getAvailableDrivers());
echo "</pre>";

echo "<h2>Extensions</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

echo "<h2>Environment Variables</h2>";
echo "<pre>";
$env_vars = [];
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'DB_') === 0 || strpos($key, 'DATABASE') === 0 || strpos($key, 'PG') === 0) {
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
