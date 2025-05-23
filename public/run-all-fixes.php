<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Run All Fixes</h1>";

// Define the fix scripts to run
$fixScripts = [
    'fix-app-blade-dd.php',
    'fix-users-table.php',
    'fix-user-model.php',
    'fix-admin-controller.php',
    'fix-admin-view.php'
];

// Run each fix script
foreach ($fixScripts as $script) {
    echo "<h2>Running $script</h2>";
    
    $scriptPath = __DIR__ . '/' . $script;
    if (file_exists($scriptPath)) {
        // Include the script
        ob_start();
        include $scriptPath;
        $output = ob_get_clean();
        
        // Extract just the body content
        $bodyStart = strpos($output, '<body>');
        $bodyEnd = strpos($output, '</body>');
        
        if ($bodyStart !== false && $bodyEnd !== false) {
            $bodyContent = substr($output, $bodyStart + 6, $bodyEnd - $bodyStart - 6);
            echo $bodyContent;
        } else {
            echo $output;
        }
    } else {
        echo "<p>Script not found: $scriptPath</p>";
    }
    
    echo "<hr>";
}

echo "<h2>Clear Cache</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan config:clear', $output, $return_var);
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<h2>Clear View Cache</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan view:clear', $output, $return_var);
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<h2>Clear Route Cache</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan route:clear', $output, $return_var);
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<p>All fixes have been applied. <a href='/admin'>Go to admin dashboard</a></p>";
