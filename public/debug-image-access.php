<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$imageFile = $_GET['image'] ?? '6EU357QBWvmGXiXNxoOARYsjE9cc5Iyat9eEhXPk.jpg';

echo "<!DOCTYPE html><html><head><title>Debug Image Access</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>🔍 Debug Image Access</h1>";

echo "<h3>🎯 Testing Image: <code>$imageFile</code></h3>";

// Check all possible locations
$locations = [
    'storage/app/public/uploads/' => __DIR__ . '/../storage/app/public/uploads/' . $imageFile,
    'storage/app/public/' => __DIR__ . '/../storage/app/public/' . $imageFile,
    'public/storage/uploads/' => __DIR__ . '/storage/uploads/' . $imageFile,
    'public/storage/' => __DIR__ . '/storage/' . $imageFile,
    'public/uploads/' => __DIR__ . '/uploads/' . $imageFile,
];

echo "<h3>📁 File Location Check:</h3>";
foreach ($locations as $desc => $path) {
    $exists = file_exists($path);
    $readable = $exists && is_readable($path);
    $size = $exists ? filesize($path) : 0;
    
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid " . ($exists ? '#10b981' : '#ef4444') . "; border-radius: 5px;'>";
    echo "<strong>$desc</strong><br>";
    echo "Path: <code>$path</code><br>";
    echo "Exists: " . ($exists ? '✅ Yes' : '❌ No') . "<br>";
    if ($exists) {
        echo "Readable: " . ($readable ? '✅ Yes' : '❌ No') . "<br>";
        echo "Size: " . number_format($size) . " bytes<br>";
        echo "Modified: " . date('Y-m-d H:i:s', filemtime($path)) . "<br>";
    }
    echo "</div>";
}

// Check symlink status
echo "<h3>🔗 Symlink Status:</h3>";
$symlinkPath = __DIR__ . '/storage';
if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        $target = readlink($symlinkPath);
        $targetExists = file_exists($target);
        echo "<div style='padding: 10px; background: " . ($targetExists ? '#d1fae5' : '#fee2e2') . "; border-radius: 5px;'>";
        echo "✅ Symlink exists<br>";
        echo "Target: <code>$target</code><br>";
        echo "Target exists: " . ($targetExists ? '✅ Yes' : '❌ No') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='padding: 10px; background: #fee2e2; border-radius: 5px;'>";
        echo "❌ /public/storage exists but is NOT a symlink<br>";
        echo "</div>";
    }
} else {
    echo "<div style='padding: 10px; background: #fee2e2; border-radius: 5px;'>";
    echo "❌ Symlink /public/storage does not exist<br>";
    echo "</div>";
}

// Test web access
echo "<h3>🌐 Web Access Test:</h3>";
$webPaths = [
    "/storage/uploads/$imageFile",
    "/storage/$imageFile", 
    "/uploads/$imageFile"
];

foreach ($webPaths as $webPath) {
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<strong>URL:</strong> <a href='$webPath' target='_blank'>$webPath</a><br>";
    echo "<button onclick=\"testImageLoad('$webPath')\">🧪 Test Load</button>";
    echo "<div id='result-" . md5($webPath) . "'></div>";
    echo "</div>";
}

// List all files in uploads directory
echo "<h3>📋 All Files in Uploads Directory:</h3>";
$uploadsDir = __DIR__ . '/../storage/app/public/uploads';
if (file_exists($uploadsDir)) {
    $files = array_diff(scandir($uploadsDir), ['.', '..']);
    if (empty($files)) {
        echo "<p>No files found in uploads directory</p>";
    } else {
        echo "<ul>";
        foreach ($files as $file) {
            $isTarget = ($file === $imageFile);
            $style = $isTarget ? 'background: #fef3c7; font-weight: bold;' : '';
            echo "<li style='$style'>$file " . ($isTarget ? '← TARGET FILE' : '') . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>❌ Uploads directory does not exist</p>";
}

?>

<script>
function testImageLoad(url) {
    const resultId = 'result-' + btoa(url).replace(/[^a-zA-Z0-9]/g, '');
    const resultDiv = document.getElementById('result-' + CryptoJS.MD5(url).toString()) || 
                     document.querySelector(`[id*="${url.replace(/[^a-zA-Z0-9]/g, '')}"]`);
    
    const img = new Image();
    img.onload = function() {
        if (resultDiv) resultDiv.innerHTML = '<span style="color: green;">✅ Image loads successfully (' + this.naturalWidth + 'x' + this.naturalHeight + ')</span>';
    };
    img.onerror = function() {
        if (resultDiv) resultDiv.innerHTML = '<span style="color: red;">❌ Image failed to load</span>';
    };
    img.src = url;
}

// Auto-test the main URL
window.onload = function() {
    testImageLoad('/storage/uploads/<?php echo $imageFile; ?>');
};
</script>

<h3>🔧 Quick Fixes:</h3>
<form method="POST" action="/storage-fix.php?token=<?php echo $validToken; ?>">
    <button type="submit" name="action" value="fix_storage" style="background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;">
        🔗 Fix Storage Symlink
    </button>
</form>

<p><strong>💡 Common Issues:</strong></p>
<ul>
    <li>Symlink pointing to wrong directory</li>
    <li>File uploaded to wrong location</li>
    <li>Permissions issue</li>
    <li>Web server not serving symlinked files</li>
</ul>

<p><a href="/image-browser.php?token=<?php echo $validToken; ?>">📸 Browse All Images</a> | 
   <a href="/admin-dashboard.php?token=<?php echo $validToken; ?>">← Dashboard</a></p>

</body></html>
