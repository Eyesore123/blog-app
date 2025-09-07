<?php
// Security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

// Database connection (adjust to your settings)
$pdo = new PDO('mysql:host=127.0.0.1;dbname=your_database;charset=utf8', 'db_user', 'db_pass', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updated_at'])) {
    foreach ($_POST['updated_at'] as $id => $newDate) {
        $id = (int)$id;
        $newDate = trim($newDate);

        if ($newDate === '') {
            // Set updated_at = created_at
            $stmt = $pdo->prepare("UPDATE posts SET updated_at = created_at WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            // Update to the specified date
            $stmt = $pdo->prepare("UPDATE posts SET updated_at = ? WHERE id = ?");
            $stmt->execute([$newDate, $id]);
        }
    }
    $message = "✅ Updated post timestamps successfully!";
}

// Fetch all posts
$stmt = $pdo->query("SELECT id, title, created_at, updated_at FROM posts ORDER BY id DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin: Edit Post Dates</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">🛠 Edit Post Timestamps</h1>

    <?php if (!empty($message)) : ?>
        <div class="bg-green-100 border border-green-400 p-4 rounded mb-6 text-green-800">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-6 rounded shadow space-y-4">
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border px-4 py-2">ID</th>
                    <th class="border px-4 py-2">Title</th>
                    <th class="border px-4 py-2">Created At</th>
                    <th class="border px-4 py-2">Updated At</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $post) : ?>
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2"><?= $post['id'] ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($post['title']) ?></td>
                    <td class="border px-4 py-2"><?= $post['created_at'] ?></td>
                    <td class="border px-4 py-2">
                        <input type="text" name="updated_at[<?= $post['id'] ?>]" 
                               value="<?= $post['updated_at'] !== $post['created_at'] ? $post['updated_at'] : '' ?>" 
                               placeholder="Leave blank = same as created_at"
                               class="w-full border px-2 py-1 rounded" />
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" 
                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
            💾 Save Changes
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="/admin-dashboard.php?token=<?= $validToken ?>" 
           class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
            ← Back to Dashboard
        </a>
    </div>
</div>
</body>
</html>
