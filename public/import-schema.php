<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "<!DOCTYPE html><html><head><title>Import Schema</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>📥 Import Database Schema</h1>";

// Show current tables
echo "<h3>📋 Current Tables in Production:</h3>";
try {
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p>No tables found</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
}

// Import SQL if provided
if ($_POST['action'] === 'import_sql' && !empty($_POST['sql'])) {
    echo "<h3>🔄 Importing SQL:</h3>";
    
    $sql = $_POST['sql'];
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            $success++;
            echo "<div style='color: green; margin: 5px 0;'>✅ " . substr($statement, 0, 100) . "...</div>";
        } catch (PDOException $e) {
            $errors++;
            echo "<div style='color: red; margin: 5px 0;'>❌ " . substr($statement, 0, 100) . "...<br>";
            echo "&nbsp;&nbsp;&nbsp;Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    echo "<div style='background: " . ($errors > 0 ? '#fef3c7' : '#d1fae5') . "; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>Results:</strong> $success successful, $errors errors";
    echo "</div>";
}

// Quick table creation templates
if ($_POST['action'] === 'create_standard_tables') {
    echo "<h3>🔧 Creating Standard Blog Tables:</h3>";
    
    $tables = [
        'comments' => "
            CREATE TABLE IF NOT EXISTS comments (
                id SERIAL PRIMARY KEY,
                post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
                user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                parent_id INTEGER REFERENCES comments(id) ON DELETE CASCADE,
                deleted BOOLEAN NOT NULL DEFAULT FALSE,
                guest_name VARCHAR(255),
                edited BOOLEAN NOT NULL DEFAULT FALSE
            )",
        'tags' => "
            CREATE TABLE IF NOT EXISTS tags (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                slug VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
        'post_tags' => "
            CREATE TABLE IF NOT EXISTS post_tags (
                id SERIAL PRIMARY KEY,
                post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
                tag_id INTEGER NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(post_id, tag_id)
            )",
        'categories' => "
            CREATE TABLE IF NOT EXISTS categories (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                description TEXT,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )"
    ];
    
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Created table: $tableName<br>";
        } catch (PDOException $e) {
            echo "❌ Error creating $tableName: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    }
    
    // Add indexes
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_comments_post_id ON comments(post_id)",
        "CREATE INDEX IF NOT EXISTS idx_comments_user_id ON comments(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_post_tags_post_id ON post_tags(post_id)",
        "CREATE INDEX IF NOT EXISTS idx_post_tags_tag_id ON post_tags(tag_id)"
    ];
    
    foreach ($indexes as $indexSql) {
        try {
            $pdo->exec($indexSql);
        } catch (PDOException $e) {
            // Ignore index errors
        }
    }
    
    echo "<div style='background: #d1fae5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Standard blog tables created with indexes!";
    echo "</div>";
}

?>

<h3>📥 Import Options:</h3>

<div style="background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <h4>🚀 Quick Standard Tables:</h4>
    <form method="POST">
        <button type="submit" name="action" value="create_standard_tables" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
            📝 Create Standard Blog Tables (comments, tags, categories)
        </button>
    </form>
</div>

<div style="background: #fef3c7; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <h4>📋 Import Custom SQL:</h4>
    <p>Paste your SQL schema or data here:</p>
    <form method="POST">
        <textarea name="sql" rows="15" style="width: 100%; font-family: monospace; padding: 10px;" placeholder="Paste your SQL here...

Example:
CREATE TABLE my_table (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

INSERT INTO my_table (name) VALUES ('test');"></textarea>
        <br><br>
        <button type="submit" name="action" value="import_sql" style="background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
            🔄 Execute SQL
        </button>
    </form>
</div>

<h3>💡 How to Export from Local:</h3>
<div style="background: #f3f4f6; padding: 15px; border-radius: 5px;">
    <p><strong>PostgreSQL:</strong></p>
    <code>pg_dump -h localhost -U username -d database_name --schema-only > schema.sql</code>
    
    <p><strong>MySQL:</strong></p>
    <code>mysqldump -u username -p database_name --no-data > schema.sql</code>
    
    <p><strong>Laravel Migrations:</strong></p>
    <code>php artisan schema:dump</code>
    
    <p>Then copy the output and paste it in the textarea above!</p>
</div>

<p><a href="/admin-dashboard.php?token=<?php echo $validToken; ?>">← Back to Dashboard</a></p>

</body></html>
