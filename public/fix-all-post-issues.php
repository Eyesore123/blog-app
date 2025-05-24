<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_POST['action'] === 'fix_all_issues') {
    echo "<h3>🔧 Fixing All Post Issues...</h3>";
    
    try {
        // 1. Add missing 'published' column (Laravel expects this)
        $pdo->exec("ALTER TABLE posts ADD COLUMN IF NOT EXISTS published BOOLEAN DEFAULT true");
        echo "✅ Added 'published' column<br>";
        
        // 2. Add missing 'topic' column if it doesn't exist properly
        $columns = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'posts'")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('topic', $columns)) {
            $pdo->exec("ALTER TABLE posts ADD COLUMN topic VARCHAR(255)");
            echo "✅ Added 'topic' column<br>";
        }
        
        // 3. Make sure user_id has a default for existing posts
        $pdo->exec("UPDATE posts SET user_id = 1 WHERE user_id IS NULL");
        echo "✅ Fixed null user_id values<br>";
        
        // 4. Create a PostgreSQL function to replace MySQL's YEAR() function
        $pdo->exec("
            CREATE OR REPLACE FUNCTION year(timestamp_val timestamp) 
            RETURNS integer AS \$\$
            BEGIN
                RETURN EXTRACT(YEAR FROM timestamp_val)::integer;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
        echo "✅ Created YEAR() function for PostgreSQL<br>";
        
        // 5. Test post creation with all required fields
        $stmt = $pdo->prepare("
            INSERT INTO posts (user_id, title, content, topic, published, slug, is_published, created_at, updated_at, published_at) 
            VALUES (?, ?, ?, ?, ?, ?, true, NOW(), NOW(), NOW()) 
            RETURNING id
        ");
        
        $testData = [
            1, // user_id
            'Test Post Fixed ' . date('H:i:s'),
            'This is a test post with all required fields.',
            'test-topic',
            true, // published
            'test-post-fixed-' . time()
        ];
        
        if ($stmt->execute($testData)) {
            $postId = $stmt->fetchColumn();
            echo "✅ Created test post with ID: $postId<br>";
        }
        
        echo "<br><strong>🎉 All issues fixed!</strong><br>";
        echo "Your Laravel app should now be able to create posts with images.<br>";
        
    } catch (PDOException $e) {
        echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

// Show current table structure
try {
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'posts' 
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll();
    
    echo "<h3>📋 Current Posts Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>";
    
    $requiredCols = ['published', 'topic', 'user_id'];
    foreach ($columns as $col) {
        $highlight = in_array($col['column_name'], $requiredCols) ? ' style="background: #fef3c7;"' : '';
        echo "<tr$highlight>";
        echo "<td>{$col['column_name']}</td>";
        echo "<td>{$col['data_type']}</td>";
        echo "<td>{$col['is_nullable']}</td>";
        echo "<td>" . ($col['column_default'] ?: 'None') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "❌ Error checking table: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html><head><title>Fix All Post Issues</title></head>
<body style="font-family: sans-serif; padding: 20px;">

<h1>🔧 Fix All Post Issues</h1>

<p><strong>Issues found in your logs:</strong></p>
<ul>
    <li>❌ Missing 'published' column (Laravel expects this)</li>
    <li>❌ YEAR() function doesn't exist in PostgreSQL</li>
    <li>❌ Possible null user_id values</li>
</ul>

<form method="POST">
    <button type="submit" name="action" value="fix_all_issues" style="background: #dc2626; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
        🚨 Fix All Issues Now
    </button>
</form>

<p><a href="/admin-dashboard.php?token=<?php echo $validToken; ?>">← Back to Dashboard</a></p>

</body></html>
