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
    <title>Debug Post Creation</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>🐛 Debug Post Creation</h1>";

// Test post creation directly
if ($_POST['action'] ?? '' === 'test_post') {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>🧪 Testing Post Creation...</h3>
          </div>";
    
    // Database connection
    $databaseUrl = getenv('DATABASE_URL');
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
            
            // Test insert
            $title = 'Test Post ' . date('Y-m-d H:i:s');
            $slug = 'test-post-' . time();
            $content = 'This is a test post created directly via debug tool.';
            
            $stmt = $pdo->prepare("
                INSERT INTO posts (title, slug, content, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW()) 
                RETURNING id
            ");
            
            if ($stmt->execute([$title, $slug, $content])) {
                $postId = $stmt->fetchColumn();
                echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                        <h3 class='font-bold text-green-800'>✅ Test Post Created!</h3>
                        <p class='text-green-700'>Post ID: $postId</p>
                        <p class='text-green-700'>Title: $title</p>
                        <p class='text-green-700'>Slug: $slug</p>
                      </div>";
            } else {
                echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                        <h3 class='font-bold text-red-800'>❌ Failed to create test post</h3>
                      </div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                    <h3 class='font-bold text-red-800'>❌ Database Error</h3>
                    <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                  </div>";
        }
    }
}

// Check Laravel logs for post creation errors
echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
        <h3 class='text-lg font-semibold mb-4'>📋 Laravel Logs (Post Related)</h3>";

$logPath = '/app/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $logContent = file_get_contents($logPath);
    $lines = explode("\n", $logContent);
    
    // Filter for post-related logs
    $postLines = array_filter($lines, function($line) {
        return stripos($line, 'post') !== false || 
               stripos($line, 'create') !== false ||
               stripos($line, 'store') !== false ||
               stripos($line, 'error') !== false;
    });
    
    $recentPostLines = array_slice($postLines, -10);
    
    if (!empty($recentPostLines)) {
        echo "<div class='bg-gray-100 p-4 rounded text-xs overflow-x-auto max-h-60 overflow-y-auto'>
                <pre>" . htmlspecialchars(implode("\n", $recentPostLines)) . "</pre>
              </div>";
    } else {
        echo "<div class='text-gray-500'>No post-related logs found</div>";
    }
} else {
    echo "<div class='text-gray-500'>No log file found</div>";
}

echo "</div>";

// Check posts table structure
echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
        <h3 class='text-lg font-semibold mb-4'>🗃️ Posts Table Structure</h3>";

$databaseUrl = getenv('DATABASE_URL');
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
        
        $stmt = $pdo->query("
            SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = 'posts' 
            ORDER BY ordinal_position
        ");
        $columns = $stmt->fetchAll();
        
        if ($columns) {
            echo "<div class='overflow-x-auto'>
                    <table class='w-full text-sm'>
                        <thead>
                            <tr class='border-b'>
                                <th class='text-left p-2'>Column</th>
                                <th class='text-left p-2'>Type</th>
                                <th class='text-left p-2'>Nullable</th>
                                <th class='text-left p-2'>Default</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            foreach ($columns as $column) {
                echo "<tr class='border-b'>
                        <td class='p-2 font-medium'>{$column['column_name']}</td>
                        <td class='p-2'>{$column['data_type']}</td>
                        <td class='p-2'>{$column['is_nullable']}</td>
                        <td class='p-2'>" . ($column['column_default'] ?: 'None') . "</td>
                      </tr>";
            }
            
            echo "      </tbody>
                        </table>
                    </div>";
        } else {
            echo "<div class='text-red-600'>❌ Posts table not found</div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='text-red-600'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    echo "<div class='text-red-600'>❌ No database URL configured</div>";
}

echo "</div>";

?>

        <div class='bg-white p-6 rounded-lg shadow mb-6'>
            <h3 class='text-lg font-semibold mb-4'>🧪 Test Actions</h3>
            <form method='POST'>
                <button type='submit' name='action' value='test_post'
                        class='bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors'>
                    🧪 Create Test Post
                </button>
            </form>
            <p class='text-sm text-gray-600 mt-2'>This will create a test post directly in the database to verify the connection works.</p>
        </div>
        
        <div class='text-center'>
            <a href='/debug-posts.php?token=<?php echo $validToken; ?>' 
               class='px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors mr-4'>
                📊 Check Posts Status
            </a>
            <a href='/admin-dashboard.php?token=<?php echo $validToken; ?>' 
               class='px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors'>
                ← Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>