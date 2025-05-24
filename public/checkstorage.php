<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { 
    http_response_code(404); 
    exit; 
}

echo "<!DOCTYPE html><html><head><title>Storage Check</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>📁 Storage Directory Check</h1>";

$directories = [
    '/app/storage/app/public',
    '/app/storage/app/public/images',
    '/app/storage/app/public/uploads',
    '/app/public/storage'
];

foreach ($directories as $dir) {
    echo "<h3>$dir</h3>";
    if (is_dir($dir)) {
        echo "<p style='color: green;'>✅ Directory exists</p>";
        
        $files = scandir($dir);
        $fileCount = count($files) - 2; // exclude . and ..
        echo "<p>Files: $fileCount</p>";
        
        if ($fileCount > 0) {
            echo "<ul>";
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $fullPath = $dir . '/' . $file;
                    $size = is_file($fullPath) ? filesize($fullPath) : 'dir';
                    echo "<li>$file ($size bytes)</li>";
                }
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>❌ Directory does not exist</p>";
    }
}

// Check symlink
echo "<h3>Symlink Status</h3>";
$symlinkPath = '/app/public/storage';
if (is_link($symlinkPath)) {
    $target = readlink($symlinkPath);
    echo "<p>✅ Symlink exists: $symlinkPath → $target</p>";
} else {
    echo "<p>❌ Symlink does not exist</p>";
}

echo "<p><a href='/admin-dashboard.php?token={$_GET['token']}'>← Back to Dashboard</a></p>";
echo "</body></html>";
?>
