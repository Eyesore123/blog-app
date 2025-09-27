<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$databaseUrl = getenv('DATABASE_URL');
$dbParts = parse_url($databaseUrl);
$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$pdo = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Handle deletion
if ($_POST['action'] === 'delete_comment' && !empty($_POST['comment_id'])) {
    $commentId = (int) $_POST['comment_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        echo "✅ Comment ID $commentId deleted successfully.<br>";
    } catch (PDOException $e) {
        echo "❌ Error deleting comment: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

// Fetch all comments
try {
    $comments = $pdo->query("
        SELECT c.id, c.content, c.created_at, c.user_id, u.name AS user_name, p.title AS post_title
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN posts p ON c.post_id = p.id
        ORDER BY c.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error fetching comments: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin: Manage Comments</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f9fafb; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #111827; color: #fff; }
        tr:nth-child(even) { background: #f3f4f6; }
        button { background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<h1>🗑️ Manage Comments</h1>
<p>View all comments and remove any unwanted comment individually.</p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Author</th>
            <th>Post</th>
            <th>Content</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?= htmlspecialchars($comment['id']) ?></td>
                <td><?= htmlspecialchars($comment['user_name'] ?? 'Guest') ?></td>
                <td><?= htmlspecialchars($comment['post_title'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($comment['content']) ?></td>
                <td><?= htmlspecialchars($comment['created_at']) ?></td>
                <td>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="action" value="delete_comment">
                        <input type="hidden" name="comment_id" value="<?= htmlspecialchars($comment['id']) ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this comment?');">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
