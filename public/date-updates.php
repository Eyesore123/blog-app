<?php
// Simple admin security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Update Post Dates</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
<div class='max-w-5xl mx-auto'>
    <h1 class='text-2xl font-bold mb-6'>🗓 Update Post Dates</h1>";

// Get database connection from DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) {
    echo "<p class='text-red-600'>DATABASE_URL is not set!</p></div></body></html>";
    exit;
}

$dbParts = parse_url($databaseUrl);
$host = $dbParts['host'] ?? '';
$port = $dbParts['port'] ?? 3306;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updated_at'])) {
        foreach ($_POST['updated_at'] as $id => $newDate) {
            $id = intval($id);
            if ($newDate) {
                $stmt = $pdo->prepare("UPDATE posts SET updated_at = :updated_at WHERE id = :id");
                $stmt->execute([
                    ':updated_at' => $newDate,
                    ':id' => $id,
                ]);
            }
        }
        echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                ✅ Updated post dates successfully.
              </div>";
    }

    // Fetch all posts
    $stmt = $pdo->query("SELECT id, title, created_at, updated_at FROM posts ORDER BY id DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<form method='POST' class='bg-white p-6 rounded-lg shadow'>
            <table class='w-full table-auto'>
                <thead>
                    <tr class='bg-gray-200'>
                        <th class='p-2 border'>ID</th>
                        <th class='p-2 border'>Title</th>
                        <th class='p-2 border'>Updated At</th>
                    </tr>
                </thead>
                <tbody>";

    foreach ($posts as $post) {
        $displayValue = ($post['updated_at'] !== $post['created_at']) ? $post['updated_at'] : '';
        echo "<tr class='hover:bg-gray-50'>
                <td class='p-2 border'>{$post['id']}</td>
                <td class='p-2 border'>{$post['title']}</td>
                <td class='p-2 border'>
                    <input type='datetime-local' name='updated_at[{$post['id']}]' value='{$displayValue}' class='border p-1 rounded w-full' />
                </td>
              </tr>";
    }

    echo "</tbody>
        </table>
        <button type='submit' class='mt-4 bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors'>💾 Save Changes</button>
    </form>";

} catch (PDOException $e) {
    echo "<p class='text-red-600'>Database error: " . $e->getMessage() . "</p>";
}

echo "<div class='mt-6'>
        <a href='/admin-dashboard.php?token={$validToken}' class='px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors'>
            ← Back to Dashboard
        </a>
      </div>
</div>
</body>
</html>";
?>
