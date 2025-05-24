<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { 
    http_response_code(404); 
    exit; 
}

echo "<!DOCTYPE html><html><head><title>Laravel Logs</title></head>";
echo "<body style='font-family: monospace; padding: 20px;'>";
echo "<h1>📋 Laravel Logs</h1>";

$logFile = '/app/storage/logs/laravel.log';

if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    
    // Get last 50 lines
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -100);
    
    echo "<div style='background: #1a1a1a; color: #00ff00; padding: 20px; border-radius: 5px; overflow-x: auto;'>";
    echo "<h3>Last 100 lines:</h3>";
    echo "<pre>" . htmlspecialchars(implode("\n", $recentLines)) . "</pre>";
    echo "</div>";
    
    // Show errors only
    $errorLines = array_filter($lines, function($line) {
        return strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false;
    });
    
    if (!empty($errorLines)) {
        echo "<div style='background: #2d1b1b; color: #ff6b6b; padding: 20px; border-radius: 5px; margin-top: 20px;'>";
        echo "<h3>Recent Errors:</h3>";
        echo "<pre>" . htmlspecialchars(implode("\n", array_slice($errorLines, -20))) . "</pre>";
        echo "</div>";
    }
} else {
    echo "<p>Log file not found at: $logFile</p>";
    
    // Check if storage/logs directory exists
    $logDir = '/app/storage/logs';
    if (is_dir($logDir)) {
        echo "<p>Log directory exists. Files in directory:</p>";
        $files = scandir($logDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "<p>- $file</p>";
            }
        }
    } else {
        echo "<p>Log directory doesn't exist: $logDir</p>";
    }
}

echo "<p><a href='/admin-dashboard.php?token={$_GET['token']}'>← Back to Dashboard</a></p>";
echo "</body></html>";
?>
