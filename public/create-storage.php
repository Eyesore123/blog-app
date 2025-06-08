<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { 
    http_response_code(404); 
    exit; 
}

echo "<h1>Creating Storage Directories</h1>";

// Define the base storage path
$basePath = __DIR__ . '/../storage';

// Define the storage directories to create
$storagePaths = [
    'app' => $basePath . '/app',
    'app/public' => $basePath . '/app/public',
    'framework' => $basePath . '/framework',
    'framework/cache' => $basePath . '/framework/cache',
    'framework/cache/data' => $basePath . '/framework/cache/data',
    'framework/sessions' => $basePath . '/framework/sessions',
    'framework/views' => $basePath . '/framework/views',
    'logs' => $basePath . '/logs',
];

// Create each directory and set permissions
foreach ($storagePaths as $name => $path) {
    echo "<p>Creating $name directory at: $path</p>";
    
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "<p>✅ Directory created successfully.</p>";
        } else {
            echo "<p>❌ Failed to create directory. Check permissions.</p>";
        }
    } else {
        echo "<p>✅ Directory already exists.</p>";
    }
    
    // Set permissions
    if (chmod($path, 0755)) {
        echo "<p>✅ Permissions set to 0755.</p>";
    } else {
        echo "<p>❌ Failed to set permissions. Check ownership.</p>";
    }
}

echo "<p>Done. <a href='/check-config.php'>Check Configuration</a></p>";
