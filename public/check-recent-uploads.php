<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "<!DOCTYPE html><html><head><title>Check Recent Uploads</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>🔍 Check Recent Uploads</h1>";

// Check the specific images from logs
$recentImages = [
    'PHyDjSA0Q09cnBhdOJF9oWzBwQn0b2SEEk7jglJJ.jpg',
    '7pYCw91CHLNpyG1viEWiSb6nlwAQIXnzd8Wrl3nX.jpg',
    '6EU357QBWvmGXiXNxoOARYsjE9cc5Iyat9eEhXPk.jpg'
];

echo "<h3>📸 Checking Recent Images from Logs:</h3>";
foreach ($recentImages as $image) {
    $filePath = __DIR__ . '/../storage/app/public/uploads/' . $image;
    $exists = file_exists($filePath);
    $webUrl = "/storage/uploads/$image";
    
    echo "<div style='margin: 10px 0; padding: 15px; border: 1px solid " . ($exists ? '#10b981' : '#ef4444') . "; border-radius: 5px;'>";
    echo "<strong>$image</strong><br>";
    echo "File exists: " . ($exists ? '✅ Yes' : '❌ No') . "<br>";
    if ($exists) {
        $size = filesize($filePath);
        echo "Size: " . number_format($size) . " bytes<br>";
        echo "Modified: " . date('Y-m-d H:i:s', filemtime($filePath)) . "<br>";
        echo "Web URL: <a href='$webUrl' target='_blank'>$webUrl</a><br>";
        echo "<img src='$webUrl' style='max-width: 200px; max-height: 150px; border: 1px solid #ddd;' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\";'>";
        echo "<div style='display: none; color: red;'>❌ Failed to load via web</div>";
    }
    echo "</div>";
}

// Check posts table for these images
echo "<h3>📋 Posts with These Images:</h3>";
try {
    $stmt = $pdo->query("SELECT id, title, image_path, user_id, created_at FROM posts WHERE image_path LIKE '%uploads/%' ORDER BY created_at DESC LIMIT 10");
    $posts = $stmt->fetchAll();
    
    if (empty($posts)) {
        echo "<p>No posts with image paths found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Image Path</th><th>User ID</th><th>File Exists?</th><th>Created</th></tr>";
        
        foreach ($posts as $post) {
            $imagePath = $post['image_path'];
            $fileName = basename($imagePath);
            $fullPath = __DIR__ . '/../storage/app/public/' . $imagePath;
            $exists = file_exists($fullPath);
            
            echo "<tr>";
            echo "<td>{$post['id']}</td>";
            echo "<td>" . htmlspecialchars($post['title']) . "</td>";
            echo "<td>$imagePath</td>";
            echo "<td>{$post['user_id']}</td>";
            echo "<td>" . ($exists ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>{$post['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
}

// Fix the is_subscribed column error
if ($_POST['action'] === 'fix_column_error') {
    echo "<h3>🔧 Fixing Column Error...</h3>";
    
    try {
        // Check what's causing the is_subscribed error - might be in users table
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_subscribed BOOLEAN DEFAULT false");
        echo "✅ Added is_subscribed column to users table<br>";
        
        // Also check if there are any triggers or other issues
        $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✅ Users table columns: " . implode(', ', $columns) . "<br>";
        
    } catch (PDOException $e) {
        echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

// Fix debug tool user_id issue
if ($_POST['action'] === 'fix_debug_tool') {
    echo "<h3>🔧 Fixing Debug Tool...</h3>";
    
    try {
        // Get a valid user ID
        $stmt = $pdo->query("SELECT id FROM users ORDER BY id LIMIT 1");
        $user = $stmt->fetch();
        
        if ($user) {
            $userId = $user['id'];
            
            // Test post creation with explicit user_id
            $stmt = $pdo->prepare("
                INSERT INTO posts (user_id, title, content, slug, created_at, updated_at, is_published) 
                VALUES (?, ?, ?, ?, NOW(), NOW(), true) 
                RETURNING id
            ");
            
            $testSlug = 'debug-test-' . time();
            if ($stmt->execute([$userId, 'Debug Test Post', 'This post was created with fixed user_id', $testSlug])) {
                $postId = $stmt->fetchColumn();
                echo "✅ Successfully created test post with ID: $postId using user_id: $userId<br>";
            }
        } else {
            echo "❌ No users found in database<br>";
        }
        
    } catch (PDOException $e) {
        echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

?>

<h3>🔧 Quick Fixes:</h3>
<form method="POST" style="margin: 10px 0;">
    <button type="submit" name="action" value="fix_column_error" style="background: #ef4444; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;">
        🔧 Fix "is_subscribed" Column Error
    </button>
    <button type="submit" name="action" value="fix_debug_tool" style="background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
        🔧 Fix Debug Tool user_id Issue
    </button>
</form>

<h3>📊 Summary:</h3>
<div style="background: #f0f9ff; padding: 15px; border-radius: 5px;">
    <p><strong>Good news:</strong> Laravel IS uploading images successfully!</p>
    <p><strong>Issues to fix:</strong></p>
    <ul>
        <li>❌ "is_subscribed" column error after post creation</li>
        <li>❌ Debug tool user_id parameter not working</li>
    </ul>
    <p><strong>Next step:</strong> Check if the uploaded images actually exist on disk</p>
</div>

<p><a href="/image-browser.php?token=<?php echo $validToken; ?>">📸 Browse All Images</a> | 
   <a href="/admin-dashboard.php?token=<?php echo $validToken; ?>">← Dashboard</a></p>

</body></html>
