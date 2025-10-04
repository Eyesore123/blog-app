<?php
// Simple token check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    die("Unauthorized");
}

// Connect to Postgres
$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) die("DATABASE_URL is not set!");

$dbParts = parse_url($databaseUrl);
$host = $dbParts['host'] ?? '';
$port = $dbParts['port'] ?? 5432;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

$conn = pg_connect("host=$host port=$port dbname=$database user=$username password=$password");
if (!$conn) die("Connection failed: " . pg_last_error());

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    $title = pg_escape_string($conn, $_POST['title'] ?? '');
    $content = pg_escape_string($conn, $_POST['content'] ?? '');

    if ($action === 'add') {
        pg_query($conn, "INSERT INTO news (title, content, created_at, updated_at) VALUES ('$title', '$content', NOW(), NOW())");
    } elseif ($action === 'update' && $id) {
        pg_query($conn, "UPDATE news SET title='$title', content='$content', updated_at=NOW() WHERE id=$id");
    } elseif ($action === 'delete' && $id) {
        pg_query($conn, "DELETE FROM news WHERE id=$id");
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($providedToken));
    exit;
}

// Fetch all news
$result = pg_query($conn, "SELECT * FROM news ORDER BY created_at DESC");
$newsList = pg_fetch_all($result) ?: [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage News</title>
<style>
body { font-family: sans-serif; margin: 2rem; background: #f0f0f0; }
.container { max-width: 900px; margin: auto; padding: 1rem; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
input, textarea { width: 100%; padding: 0.5rem; font-size: 1rem; margin-bottom: 0.5rem; }
textarea { min-height: 80px; }
button { padding: 0.5rem 1rem; font-size: 1rem; background: #0074D9; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-right: 0.5rem; }
button:hover { background: #005bb5; }
.news-item { padding: 0.5rem 0; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
form.inline { display: inline; }
h2,h3 { margin-top: 1rem; }
</style>
</head>
<body>
<div class="container">
    <h2>Manage News</h2>

    <h3>Add New News Item</h3>
    <form method="post">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="content" placeholder="Content" required></textarea>
        <button type="submit">Add</button>
    </form>

    <h3>Existing News</h3>
    <?php if (!$newsList): ?>
        <p>No news items yet.</p>
    <?php else: ?>
        <?php foreach ($newsList as $item): ?>
            <div class="news-item">
                <form method="post" class="inline">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>
                    <textarea name="content" required><?= htmlspecialchars($item['content']) ?></textarea>
                    <button type="submit">Update</button>
                </form>

                <form method="post" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <button type="submit" onclick="return confirm('Delete this news item?')">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
