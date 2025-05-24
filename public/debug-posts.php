<?php
// Security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug Posts</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>🐛 Debug Posts & Storage</h1>";

// Database connection
$databaseUrl = getenv('DATABASE_URL');
$pdo = null;

if ($databaseUrl) {
    $dbParts = parse_url($databaseUrl);
    $dbHost = $dbParts['host'];
    $dbPort = $dbParts['port'] ?? 5432;
    $dbName = ltrim($dbParts['path'], '/');
    $dbUser = $dbParts['user'];
    $dbPass = $dbParts['pass'];
    
    try {
        $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-green-800'>✅ Database Connected</h3>
              </div>";
        
    } catch (PDOException $e) {
        echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-red-800'>❌ Database Connection Failed</h3>
                <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
              </div>";
        exit;
    }
}

// Check if posts table exists
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'posts'");
    $postsTableExists = $stmt->fetchColumn() > 0;
    
    if ($postsTableExists) {
        echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-green-800'>✅ Posts Table Exists</h3>
              </div>";
        
        // Get posts count and recent posts
        $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
        $postCount = $stmt->fetchColumn();
        
        echo "<div class='bg-blue-100 border border-blue-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-blue-800'>📊 Posts Statistics</h3>
                <p class='text-blue-700'>Total posts in database: <strong>$postCount</strong></p>
              </div>";
        
        if ($postCount > 0) {
            $stmt = $pdo->query("SELECT id, title, slug, content, image_path, created_at FROM posts ORDER BY created_at DESC LIMIT 5");
            $recentPosts = $stmt->fetchAll();
            
            echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
                    <h3 class='text-lg font-semibold mb-4'>📝 Recent Posts</h3>
                    <div class='overflow-x-auto'>
                        <table class='w-full text-sm'>
                            <thead>
                                <tr class='border-b'>
                                    <th class='text-left p-2'>ID</th>
                                    <th class='text-left p-2'>Title</th>
                                    <th class='text-left p-2'>Slug</th>
                                    <th class='text-left p-2'>Image</th>
                                    <th class='text-left p-2'>Created</th>
                                </tr>
                            </thead>
                            <tbody>";
            
            foreach ($recentPosts as $post) {
                $imageStatus = $post['image_path'] ? 
                    '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Has Image</span>' : 
                    '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">No Image</span>';
                
                echo "<tr class='border-b'>
                        <td class='p-2'>{$post['id']}</td>
                        <td class='p-2'>" . htmlspecialchars(substr($post['title'], 0, 30)) . "...</td>
                        <td class='p-2'>" . htmlspecialchars($post['slug']) . "</td>
                        <td class='p-2'>$imageStatus</td>
                        <td class='p-2'>" . date('M j, Y H:i', strtotime($post['created_at'])) . "</td>
                      </tr>";
            }
            
            echo "      </tbody>
                        </table>
                    </div>
                  </div>";
        }
        
    } else {
        echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-red-800'>❌ Posts Table Missing</h3>
                <p class='text-red-700'>The posts table doesn't exist in the database.</p>
              </div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-red-800'>❌ Database Query Failed</h3>
            <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

// Check storage directories
echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
        <h3 class='text-lg font-semibold mb-4'>📁 Storage Information</h3>";

$storagePaths = [
    'Laravel Storage' => '/app/storage/app/public',
    'Public Storage' => '/app/public/storage',
    'Public Directory' => '/app/public',
    'Current Directory' => __DIR__
];

foreach ($storagePaths as $name => $path) {
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    
    $status = $exists ? 
        ($writable ? 
            '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">✅ Exists & Writable</span>' : 
            '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">⚠️ Exists but Not Writable</span>'
        ) : 
        '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">❌ Does Not Exist</span>';
    
    echo "<div class='flex justify-between items-center p-2 border-b'>
            <div>
                <strong>$name</strong><br>
                <code class='text-xs text-gray-600'>$path</code>
            </div>
            <div>$status</div>
          </div>";
}

// Check if storage link exists
$storageLink = '/app/public/storage';
$linkExists = is_link($storageLink);
$linkTarget = $linkExists ? readlink($storageLink) : 'N/A';

echo "<div class='mt-4 p-4 bg-gray-50 rounded'>
        <h4 class='font-semibold mb-2'>🔗 Storage Link Status</h4>
        <p><strong>Link exists:</strong> " . ($linkExists ? 'Yes' : 'No') . "</p>";
if ($linkExists) {
    echo "<p><strong>Points to:</strong> <code>$linkTarget</code></p>";
}
echo "</div>";

echo "</div>";

// Environment variables check
echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
        <h3 class='text-lg font-semibold mb-4'>🔧 Environment Variables</h3>";

$envVars = [
    'APP_ENV' => getenv('APP_ENV'),
    'APP_DEBUG' => getenv('APP_DEBUG'),
    'FILESYSTEM_DISK' => getenv('FILESYSTEM_DISK'),
    'AWS_BUCKET' => getenv('AWS_BUCKET'),
    'DATABASE_URL' => getenv('DATABASE_URL') ? 'Set (hidden)' : 'Not set'
];

foreach ($envVars as $key => $value) {
    $displayValue = $value ?: 'Not set';
    echo "<div class='flex justify-between items-center p-2 border-b'>
            <strong>$key</strong>
            <code class='text-sm'>$displayValue</code>
          </div>";
}

echo "</div>";

?>

        <div class='mt-6 text-center'>
            <a href='/admin-dashboard.php?token=<?php echo $validToken; ?>' 
               class='px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors'>
                ← Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>";
?>
