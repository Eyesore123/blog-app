<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Railway Cache Environment Variables</h1>";

echo "<p>This script will help you update your Railway environment variables for cache configuration.</p>";

echo "<h2>Current Environment Variables</h2>";
echo "<pre>";
$currentVars = [
    'CACHE_DRIVER' => getenv('CACHE_DRIVER'),
    'SESSION_DRIVER' => getenv('SESSION_DRIVER'),
    'QUEUE_CONNECTION' => getenv('QUEUE_CONNECTION'),
];
print_r($currentVars);
echo "</pre>";

echo "<h2>Instructions</h2>";
echo "<p>Follow these steps to update your Railway environment variables:</p>";
echo "<ol>";
echo "<li>Go to your Railway dashboard</li>";
echo "<li>Select your project</li>";
echo "<li>Click on your web service (blog-app)</li>";
echo "<li>Go to the 'Variables' tab</li>";
echo "<li>Add or update the following variables:";
echo "<ul>";
echo "<li>CACHE_DRIVER=file</li>";
echo "<li>SESSION_DRIVER=file</li>";
echo "<li>QUEUE_CONNECTION=sync</li>";
echo "</ul>";
echo "</li>";
echo "<li>Click 'Save Changes'</li>";
echo "<li>Redeploy your application</li>";
echo "</ol>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
