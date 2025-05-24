<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { 
    http_response_code(404); 
    exit; 
}

echo "<!DOCTYPE html><html><head><title>Fix Storage</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>🔧 Fix Storage Symlink</h1>";

$publicStoragePath = '/app/public/storage';
$storageAppPublicPath = '/app/storage/app/public';

// Remove existing symlink if it exists
if (is_link($publicStoragePath)) {
    unlink($publicStoragePath);
    echo "<p>✅ Removed existing symlink</p>";
}

// Create new symlink
if (symlink($storageAppPublicPath, $publicStoragePath)) {
    echo "<p>✅ Storage symlink created successfully</p>";
    echo "<p>Link: $publicStoragePath → $storageAppPublicPath</p>";
} else {
    echo "<p>❌ Failed to create storage symlink</p>";
}

// Check if it works
if (is_link($publicStoragePath) && readlink($publicStoragePath) === $storageAppPublicPath) {
    echo "<p>✅ Symlink is working correctly</p>";
} else {
    echo "<p>❌ Symlink verification failed</p>";
}

echo "<p><a href='/admin-dashboard.php?token={$_GET['token']}'>← Back to Dashboard</a></p>";
echo "</body></html>";
?>
