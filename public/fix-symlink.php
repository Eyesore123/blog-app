<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

if ($_POST['action'] === 'fix_storage') {
    echo "<h3>🔧 Fixing Storage Symlink...</h3>";
    
    // Remove existing symlink if it exists
    $symlinkPath = __DIR__ . '/storage';
    if (file_exists($symlinkPath)) {
        if (is_link($symlinkPath)) {
            unlink($symlinkPath);
            echo "✅ Removed old storage symlink<br>";
        } else {
            echo "⚠️ /public/storage exists but is not a symlink<br>";
        }
    }
    
    // Create the correct symlink
    $targetPath = __DIR__ . '/../storage/app/public';
    
    // Make sure target directory exists
    if (!file_exists($targetPath)) {
        mkdir($targetPath, 0755, true);
        echo "✅ Created storage/app/public directory<br>";
    }
    
    // Create uploads subdirectory
    $uploadsPath = $targetPath . '/uploads';
    if (!file_exists($uploadsPath)) {
        mkdir($uploadsPath, 0755, true);
        echo "✅ Created storage/app/public/uploads directory<br>";
    }
    
    // Create the symlink
    if (symlink($targetPath, $symlinkPath)) {
        echo "✅ Created storage symlink: public/storage → storage/app/public<br>";
    } else {
        echo "❌ Failed to create storage symlink<br>";
    }
    
    // Test by creating a test file
    $testFile = $uploadsPath . '/test-image.txt';
    if (file_put_contents($testFile, 'Test file for symlink verification')) {
        echo "✅ Created test file: $testFile<br>";
        
        // Check if accessible via web
        $webPath = '/storage/uploads/test-image.txt';
        echo "🌐 Test URL: <a href='$webPath' target='_blank'>$webPath</a><br>";
        
        // Check if file exists via symlink
        $symlinkFile = __DIR__ . '/storage/uploads/test-image.txt';
        if (file_exists($symlinkFile)) {
            echo "✅ File accessible via symlink<br>";
        } else {
            echo "❌ File NOT accessible via symlink<br>";
        }
    }
    
    echo "<br><strong>🎉 Storage symlink fixed!</strong><br>";
    echo "Images should now be accessible at /storage/uploads/filename.jpg<br>";
}

// Check current storage setup
echo "<h3>📁 Current Storage Setup:</h3>";

$paths = [
    'public/storage' => __DIR__ . '/storage',
    'storage/app/public' => __DIR__ . '/../storage/app/public',
    'storage/app/public/uploads' => __DIR__ . '/../storage/app/public/uploads'
];

foreach ($paths as $label => $path) {
    $exists = file_exists($path);
    $isLink = is_link($path);
    $writable = $exists && is_writable($path);
    
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<strong>$label:</strong><br>";
    echo "Path: <code>$path</code><br>";
    echo "Exists: " . ($exists ? '✅' : '❌') . "<br>";
    echo "Type: " . ($isLink ? '🔗 Symlink' : '📁 Directory') . "<br>";
    echo "Writable: " . ($writable ? '✅' : '❌') . "<br>";
    
    if ($isLink) {
        $target = readlink($path);
        echo "Target: <code>$target</code><br>";
    }
    echo "</div>";
}

// Check for existing uploaded files
$uploadsDir = __DIR__ . '/../storage/app/public/uploads';
if (file_exists($uploadsDir)) {
    $files = array_diff(scandir($uploadsDir), ['.', '..']);
    if (!empty($files)) {
        echo "<h3>📸 Uploaded Files:</h3>";
        foreach ($files as $file) {
            $webPath = "/storage/uploads/$file";
            echo "<div>$file - <a href='$webPath' target='_blank'>Test Link</a></div>";
        }
    }
}

?>

<!DOCTYPE html>
<html><head><title>Fix Storage Symlink</title></head>
<body style="font-family: sans-serif; padding: 20px;">

<h1>🔗 Fix Storage Symlink</h1>

<p><strong>Issue:</strong> Laravel stores images in <code>storage/app/public/uploads/</code> but serves them from <code>/storage/uploads/</code></p>
<p><strong>Solution:</strong> Create proper symlink from <code>public/storage</code> → <code>storage/app/public</code></p>

<form method="POST">
    <button type="submit" name="action" value="fix_storage" style="background: #3b82f6; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
        🔧 Fix Storage Symlink
    </button>
</form>

<p><strong>After fixing, your images should be accessible at:</strong><br>
<code>https://blog.joniputkinen.com/storage/uploads/filename.jpg</code></p>

</body></html>
