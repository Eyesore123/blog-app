<?php
// Simple security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Post-Deployment Setup</h1>";

// Clear all caches
$commands = [
    'config:clear',
    'cache:clear', 
    'route:clear',
    'view:clear',
    'migrate --force' // Run migrations
];

foreach ($commands as $command) {
    echo "<p>Running: php artisan $command</p>";
    $output = [];
    $return_var = 0;
    exec("cd " . __DIR__ . "/../ && php artisan $command 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "<p>✅ Success</p>";
    } else {
        echo "<p>❌ Failed: " . implode(', ', $output) . "</p>";
    }
}

echo "<p>✅ Post-deployment setup completed!</p>";
echo "<p><a href='/'>Back to homepage</a></p>";
