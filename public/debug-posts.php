<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "<!DOCTYPE html><html><head><title>Debug Posts</title></head><body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>🐛 Debug Post Creation</h1>";

// Get first user ID
$firstUserId = null;
try {
    $stmt = $pdo->query("SELECT id FROM users ORDER BY id LIMIT 1");
    $user = $stmt->fetch();
    if ($user) {
        $firstUserId = $user['id'];
        echo "<div style='background: #dbeafe; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ Found User ID: $firstUserId";
        echo "</div>";
    } else {
        echo "<div style='background: #fef3c7; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "⚠️ No users found - need to create one first";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div style='background: #fee2e2; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ Error checking users: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

// Test post creation
if ($_POST['action'] === 'test_post' && $firstUserId) {
    echo "<h3>🧪 Testing Post Creation...</h3>";
    
    try {
        $title = 'Test Post ' . date('Y-m-d H:i:s');
        $slug = 'test-post-' . time();
        $content = 'This is a test post created directly via debug tool.';
        
        // FIXED: Include user_id in the INSERT
        $stmt = $pdo->prepare("
            INSERT INTO posts (user_id, title, slug, content, is_published, created_at, updated_at, published_at) 
            VALUES (?, ?, ?, ?, true, NOW(), NOW(), NOW()) 
            RETURNING id
        ");
        
        if ($stmt->execute([$firstUserId, $title, $slug, $content])) {
            $postId = $stmt->fetchColumn();
            echo "<div style='background: #d1fae5; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Post Created Successfully!<br>";
            echo "Post ID: $postId<br>";
            echo "User ID: $firstUserId<br>";
            echo "Title: $title<br>";
            echo "Slug: $slug";
            echo "</div>";
        }
        
    } catch (PDOException $e) {
        echo "<div style='background: #fee2e2; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Database Error<br>";
        echo "Error: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

// Create user if needed
if ($_POST['action'] === 'create_user') {
    echo "<h3>👤 Creating User...</h3>";
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, created_at, updated_at) 
            VALUES (?, ?, ?, NOW(), NOW()) 
            RETURNING id
        ");
        
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        if ($stmt->execute(['Admin User', 'admin@blog.com', $password])) {
            $userId = $stmt->fetchColumn();
            $firstUserId = $userId;
            echo "<div style='background: #d1fae5; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ User Created!<br>";
            echo "User ID: $userId<br>";
            echo "Email: admin@blog.com<br>";
            echo "Password: admin123";
            echo "</div>";
        }
        
    } catch (PDOException $e) {
        echo "<div style='background: #fee2e2; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Error creating user: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

?>

<h3>🔧 Actions:</h3>
<form method="POST" style="margin: 20px 0;">
    <?php if (!$firstUserId): ?>
    <button type="submit" name="action" value="create_user" style="background: #f59e0b; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;">
        Create User First
    </button>
    <?php endif; ?>
    
    <?php if ($firstUserId): ?>
    <button type="submit" name="action" value="test_post" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
        Test Post Creation
    </button>
    <?php endif; ?>
</form>

</body></html>
