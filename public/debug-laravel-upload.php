<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "<!DOCTYPE html><html><head><title>Debug Laravel Upload</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>🔍 Debug Laravel Upload Process</h1>";

// Check recent posts with images
echo "<h3>📋 Recent Posts with Images:</h3>";
try {
    $stmt = $pdo->query("SELECT id, title, image_path, created_at FROM posts WHERE image_path IS NOT NULL ORDER BY created_at DESC LIMIT 10");
    $posts = $stmt->fetchAll();
    
    if (empty($posts)) {
        echo "<p>No posts with images found in database</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Image Path</th><th>File Exists?</th><th>Created</th></tr>";
        
        foreach ($posts as $post) {
            $imagePath = $post['image_path'];
            $fullPath = __DIR__ . '/../storage/app/public/' . $imagePath;
            $exists = file_exists($fullPath);
            $webPath = '/storage/' . $imagePath;
            
            echo "<tr>";
            echo "<td>{$post['id']}</td>";
            echo "<td>" . htmlspecialchars($post['title']) . "</td>";
            echo "<td><a href='$webPath' target='_blank'>$imagePath</a></td>";
            echo "<td>" . ($exists ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>{$post['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
}

// Check Laravel storage configuration
echo "<h3>📁 Laravel Storage Directories:</h3>";
$storagePaths = [
    'storage/app/public' => __DIR__ . '/../storage/app/public',
    'storage/app/public/uploads' => __DIR__ . '/../storage/app/public/uploads',
    'storage/logs' => __DIR__ . '/../storage/logs',
    'public/storage (symlink)' => __DIR__ . '/storage'
];

foreach ($storagePaths as $desc => $path) {
    $exists = file_exists($path);
    $writable = $exists && is_writable($path);
    $isLink = is_link($path);
    
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid " . ($exists ? '#10b981' : '#ef4444') . "; border-radius: 5px;'>";
    echo "<strong>$desc</strong><br>";
    echo "Path: <code>$path</code><br>";
    echo "Exists: " . ($exists ? '✅ Yes' : '❌ No') . "<br>";
    if ($exists) {
        echo "Writable: " . ($writable ? '✅ Yes' : '❌ No') . "<br>";
        echo "Type: " . ($isLink ? '🔗 Symlink' : '📁 Directory') . "<br>";
        if ($isLink) {
            echo "Target: " . readlink($path) . "<br>";
        }
    }
    echo "</div>";
}

// Check Laravel logs for upload errors
echo "<h3>📄 Recent Laravel Logs (Upload Related):</h3>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $uploadLines = [];
    
    foreach ($lines as $line) {
        if (stripos($line, 'upload') !== false || 
            stripos($line, 'image') !== false || 
            stripos($line, 'storage') !== false ||
            stripos($line, 'file') !== false) {
            $uploadLines[] = $line;
        }
    }
    
    if (empty($uploadLines)) {
        echo "<p>No upload-related log entries found</p>";
    } else {
        echo "<div style='background: #f3f4f6; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";
        echo "<pre>" . htmlspecialchars(implode("\n", array_slice($uploadLines, -20))) . "</pre>";
        echo "</div>";
    }
} else {
    echo "<p>❌ Laravel log file not found</p>";
}

// Test file upload simulation
if ($_POST['action'] === 'test_upload') {
    echo "<h3>🧪 Testing File Upload Simulation:</h3>";
    
    try {
        $uploadsDir = __DIR__ . '/../storage/app/public/uploads';
        
        // Create test file
        $testFileName = 'test-upload-' . time() . '.jpg';
        $testFilePath = $uploadsDir . '/' . $testFileName;
        $testContent = 'FAKE IMAGE DATA - ' . date('Y-m-d H:i:s');
        
        if (file_put_contents($testFilePath, $testContent)) {
            echo "✅ Successfully created test file: $testFileName<br>";
            echo "📁 File path: $testFilePath<br>";
            echo "🌐 Web URL: <a href='/storage/uploads/$testFileName' target='_blank'>/storage/uploads/$testFileName</a><br>";
            
            // Add to database
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, image_path, slug, created_at, updated_at) VALUES (1, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                'Test Upload Post',
                'This post tests the upload process',
                'uploads/' . $testFileName,
                'test-upload-' . time()
            ]);
            
            echo "✅ Added test post to database<br>";
        } else {
            echo "❌ Failed to create test file<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

?>

<h3>🔧 Actions:</h3>
<form method="POST" style="margin: 10px 0;">
    <button type="submit" name="action" value="test_upload" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
        🧪 Test File Upload Process
    </button>
</form>

<h3>💡 Diagnosis:</h3>
<div style="background: #fef3c7; padding: 15px; border-radius: 5px;">
    <p><strong>The issue:</strong> Your Laravel app is saving image paths to the database, but the actual image files are NOT being saved to disk.</p>
    <p><strong>Possible causes:</strong></p>
    <ul>
        <li>Laravel upload directory not writable</li>
        <li>File upload failing silently in Laravel code</li>
        <li>Wrong storage disk configuration</li>
        <li>File upload size limits</li>
    </ul>
</div>

<p><a href="/image-browser.php?token=<?php echo $validToken; ?>">📸 Browse Images</a> | 
   <a href="/admin-dashboard.php?token=<?php echo $validToken; ?>">← Dashboard</a></p>

</body></html>
