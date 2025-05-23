<?php
echo "<h1>Creating Storage Directories</h1>";

// Define the storage directories to create
$storagePaths = [
    'app' => storage_path('app'),
    'app/public' => storage_path('app/public'),
    'framework' => storage_path('framework'),
    'framework/cache' => storage_path('framework/cache'),
    'framework/cache/data' => storage_path('framework/cache/data'),
    'framework/sessions' => storage_path('framework/sessions'),
    'framework/views' => storage_path('framework/views'),
    'logs' => storage_path('logs'),
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
