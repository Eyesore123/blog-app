<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { 
    http_response_code(404); 
    exit; 
}

echo "<!DOCTYPE html><html><head><title>Migrate Images</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>📦 Migrate Images</h1>";

$oldDir = '/app/storage/app/public/images';
$newDir = '/app/storage/app/public/uploads';

if (is_dir($oldDir)) {
    $files = scandir($oldDir);
    $moved = 0;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $oldPath = $oldDir . '/' . $file;
        $newPath = $newDir . '/' . $file;
        
        if (is_file($oldPath)) {
            if (rename($oldPath, $newPath)) {
                echo "<p>✅ Moved: $file</p>";
                $moved++;
            } else {
                echo "<p>❌ Failed to move: $file</p>";
            }
        }
    }
    
    echo "<p><strong>Total files moved: $moved</strong></p>";
    
    // Remove empty images directory
    if ($moved > 0) {
        rmdir($oldDir);
        echo "<p>✅ Removed empty images directory</p>";
    }
} else {
    echo "<p>No images directory found to migrate</p>";
}

echo "<p><a href='/image-browser.php?token={$_GET['token']}'>→ Go to Image Browser</a></p>";
echo "<p><a href='/admin-dashboard.php?token={$_GET['token']}'>← Back to Dashboard</a></p>";
echo "</body></html>";
?>
