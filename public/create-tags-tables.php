<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_POST['action'] === 'create_tables') {
    try {
        // Create tags table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tags (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                slug VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");
        echo "✅ Created tags table<br>";
        
        // Create post_tag pivot table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS post_tag (
                id BIGSERIAL PRIMARY KEY,
                post_id BIGINT NOT NULL,
                tag_id BIGINT NOT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW(),
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
                UNIQUE(post_id, tag_id)
            )
        ");
        echo "✅ Created post_tag pivot table<br>";
        
        // Create some sample tags
        $sampleTags = [
            ['Laravel', 'laravel'],
            ['PHP', 'php'],
            ['JavaScript', 'javascript'],
            ['React', 'react'],
            ['Tutorial', 'tutorial'],
            ['Web Development', 'web-development']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?) ON CONFLICT (slug) DO NOTHING");
        foreach ($sampleTags as $tag) {
            $stmt->execute($tag);
        }
        echo "✅ Created sample tags<br>";
        
        echo "<br><strong>✅ All tables created successfully!</strong><br>";
        echo "Your Laravel app should now work without tag-related errors.<br>";
        
    } catch (PDOException $e) {
        echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

// Check existing tables
try {
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>📋 Current Tables:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        $highlight = in_array($table, ['tags', 'post_tag']) ? ' style="color: green; font-weight: bold;"' : '';
        echo "<li$highlight>$table</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "❌ Error checking tables: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html><head><title>Create Tags Tables</title></head>
<body style="font-family: sans-serif; padding: 20px;">
<h1>🏷️ Create Tags Tables</h1>
<p>Your Laravel app needs tags and post_tag tables to work properly.</p>
<form method="POST">
    <button type="submit" name="action" value="create_tables" style="background: #10b981; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer;">
        Create Tags Tables
    </button>
</form>
</body></html>
