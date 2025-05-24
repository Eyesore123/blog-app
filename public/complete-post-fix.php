<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_POST['action'] === 'fix_all') {
    // 1. Create user if none exists
    $userCheck = $pdo->query("SELECT id FROM users LIMIT 1")->fetch();
    if (!$userCheck) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW()) RETURNING id");
        $stmt->execute(['Admin', 'admin@blog.com', password_hash('admin123', PASSWORD_DEFAULT)]);
        $userId = $stmt->fetchColumn();
        echo "✅ Created user ID: $userId<br>";
    } else {
        $userId = $userCheck['id'];
        echo "✅ Using existing user ID: $userId<br>";
    }
    
    // 2. Fix posts table
    $columns = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'posts'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('user_id', $columns)) {
        $pdo->exec("ALTER TABLE posts ADD COLUMN user_id BIGINT");
        $pdo->exec("UPDATE posts SET user_id = $userId WHERE user_id IS NULL");
        $pdo->exec("ALTER TABLE posts ALTER COLUMN user_id SET NOT NULL");
        echo "✅ Added user_id column<br>";
    }
    
    // 3. Test post creation WITH user_id
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, slug, content, is_published, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, true, NOW(), NOW(), NOW()) RETURNING id");
    $testSlug = 'test-post-' . time();
    $stmt->execute([$userId, 'Test Post Fixed', $testSlug, 'This post was created after fixing the table.']);
    $postId = $stmt->fetchColumn();
    echo "✅ Created test post ID: $postId<br>";
    echo "<br><strong>Login credentials:</strong><br>Email: admin@blog.com<br>Password: admin123<br>";
}
?>
<!DOCTYPE html>
<html><head><title>Complete Post Fix</title></head>
<body style="font-family: sans-serif; padding: 20px;">
<h1>🔧 Complete Post Fix</h1>
<form method="POST">
    <button type="submit" name="action" value="fix_all" style="background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer;">
        Fix Everything Now
    </button>
</form>
</body></html>
