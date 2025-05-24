<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { 
    http_response_code(404); 
    exit; 
}

echo "<!DOCTYPE html><html><head><title>Fix Manifest</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>📋 Fix Vite Manifest</h1>";

$manifestPath = '/app/public/build/manifest.json';
$buildDir = '/app/public/build';

// Delete the old manifest
if (file_exists($manifestPath)) {
    unlink($manifestPath);
    echo "<p>✅ Deleted old manifest.json</p>";
}

// Run npm build to regenerate everything
echo "<h3>🔨 Rebuilding Assets...</h3>";
echo "<pre style='background: #f3f4f6; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";

chdir('/app');
$output = shell_exec('npm run build 2>&1');
echo htmlspecialchars($output);

echo "</pre>";

// Check if manifest was recreated
if (file_exists($manifestPath)) {
    echo "<p>✅ New manifest.json created</p>";
    $manifest = json_decode(file_get_contents($manifestPath), true);
    echo "<h4>New Manifest Contents:</h4>";
    echo "<pre style='background: #f0f9ff; padding: 15px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
    echo htmlspecialchars(json_encode($manifest, JSON_PRETTY_PRINT));
    echo "</pre>";
} else {
    echo "<p>❌ Failed to create new manifest.json</p>";
}

echo "<p><a href='/'>🏠 Test Your Site</a></p>";
echo "<p><a href='/admin-dashboard.php?token={$_GET['token']}'>← Back to Dashboard</a></p>";
echo "</body></html>";
?>
