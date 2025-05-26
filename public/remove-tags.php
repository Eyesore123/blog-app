<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_POST['action'] === 'remove_tags_content') {
    try {
        // Truncate post_tag pivot table
        $pdo->exec("
            TRUNCATE TABLE post_tag RESTART IDENTITY
        ");
        echo "✅ Truncated post_tag pivot table<br>";
        
        // Truncate tags table
        $pdo->exec("
            TRUNCATE TABLE tags RESTART IDENTITY
        ");
        echo "✅ Truncated tags table<br>";
        
        echo "<br><strong>✅ All tags content removed successfully!</strong><br>";
        echo "Your Laravel app will no longer have tags-related content.<br>";
        
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
<html><head><title>Remove Tags Content</title></head>
<body style="font-family: sans-serif; padding: 20px;">
<h1>🏷️ Remove Tags Content</h1>
<p>Your Laravel app will no longer have tags-related content after running this script.</p>
<form method="POST">
    <button type="submit" name="action" value="remove_tags_content" style="background: #10b981; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer;">
        Remove Tags Content
    </button>
</form>
</body></html>