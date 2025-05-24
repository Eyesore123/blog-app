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
    <title>Fix Posts Table</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>🔧 Fix Posts Table Structure</h1>";

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

// Check current table structure
echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
        <h3 class='text-lg font-semibold mb-4'>📋 Current Posts Table Structure</h3>";

try {
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'posts' 
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll();
    
    if ($columns) {
        echo "<div class='overflow-x-auto mb-4'>
                <table class='w-full text-sm border'>
                    <thead>
                        <tr class='bg-gray-50'>
                            <th class='text-left p-3 border'>Column</th>
                            <th class='text-left p-3 border'>Type</th>
                            <th class='text-left p-3 border'>Nullable</th>
                            <th class='text-left p-3 border'>Default</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        $existingColumns = [];
        foreach ($columns as $column) {
            $existingColumns[] = $column['column_name'];
            echo "<tr>
                    <td class='p-3 border font-medium'>{$column['column_name']}</td>
                    <td class='p-3 border'>{$column['data_type']}</td>
                    <td class='p-3 border'>{$column['is_nullable']}</td>
                    <td class='p-3 border'>" . ($column['column_default'] ?: 'None') . "</td>
                  </tr>";
        }
        
        echo "      </tbody>
                </table>
              </div>";
        
        // Check what columns are missing
        $requiredColumns = [
            'id' => 'bigint PRIMARY KEY',
            'title' => 'varchar(255) NOT NULL',
            'slug' => 'varchar(255) UNIQUE NOT NULL',
            'content' => 'text',
            'excerpt' => 'text',
            'image_path' => 'varchar(255)',
            'is_published' => 'boolean DEFAULT true',
            'published_at' => 'timestamp',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp'
        ];
        
        $missingColumns = array_diff(array_keys($requiredColumns), $existingColumns);
        
        if (!empty($missingColumns)) {
            echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
                    <h4 class='font-bold text-yellow-800'>⚠️ Missing Columns</h4>
                    <ul class='list-disc list-inside text-yellow-700 mt-2'>";
            
            foreach ($missingColumns as $column) {
                echo "<li><strong>$column</strong> - {$requiredColumns[$column]}</li>";
            }
            
            echo "    </ul>
                  </div>";
        } else {
            echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                    <h4 class='font-bold text-green-800'>✅ All required columns present</h4>
                  </div>";
        }
        
    } else {
        echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-red-800'>❌ Posts table not found</h3>
              </div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-red-800'>❌ Error checking table structure</h3>
            <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

echo "</div>";

// Fix table structure
if ($_POST['action'] ?? '' === 'fix_table') {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>🔧 Fixing Posts Table...</h3>
          </div>";
    
    try {
        // Get current columns again
        $stmt = $pdo->query("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'posts'
        ");
        $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $alterStatements = [];
        
        // Add missing columns
        if (!in_array('slug', $existingColumns)) {
            $alterStatements[] = "ALTER TABLE posts ADD COLUMN slug VARCHAR(255)";
            $alterStatements[] = "UPDATE posts SET slug = LOWER(REPLACE(REPLACE(title, ' ', '-'), '''', '')) WHERE slug IS NULL";
            $alterStatements[] = "ALTER TABLE posts ALTER COLUMN slug SET NOT NULL";
            $alterStatements[] = "CREATE UNIQUE INDEX IF NOT EXISTS posts_slug_unique ON posts(slug)";
        }
        
        if (!in_array('excerpt', $existingColumns)) {
            $alterStatements[] = "ALTER TABLE posts ADD COLUMN excerpt TEXT";
        }
        
        if (!in_array('image_path', $existingColumns)) {
            $alterStatements[] = "ALTER TABLE posts ADD COLUMN image_path VARCHAR(255)";
        }
        
        if (!in_array('is_published', $existingColumns)) {
            $alterStatements[] = "ALTER TABLE posts ADD COLUMN is_published BOOLEAN DEFAULT true";
        }
        
        if (!in_array('published_at', $existingColumns)) {
            $alterStatements[] = "ALTER TABLE posts ADD COLUMN published_at TIMESTAMP";
            $alterStatements[] = "UPDATE posts SET published_at = created_at WHERE published_at IS NULL";
        }
        
        // Execute all statements
        foreach ($alterStatements as $sql) {
            try {
                $pdo->exec($sql);
                echo "<div class='bg-green-100 border border-green-400 p-2 rounded mb-2'>
                        ✅ Executed: " . htmlspecialchars($sql) . "
                      </div>";
            } catch (PDOException $e) {
                echo "<div class='bg-red-100 border border-red-400 p-2 rounded mb-2'>
                        ❌ Failed: " . htmlspecialchars($sql) . "<br>
                        Error: " . htmlspecialchars($e->getMessage()) . "
                      </div>";
            }
        }
        
        if (empty($alterStatements)) {
            echo "<div class='bg-blue-100 border border-blue-400 p-4 rounded mb-4'>
                    <h3 class='font-bold text-blue-800'>ℹ️ No changes needed</h3>
                    <p class='text-blue-700'>All required columns already exist.</p>
                  </div>";
        } else {
            echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                    <h3 class='font-bold text-green-800'>✅ Table structure updated!</h3>
                    <p class='text-green-700'>Posts table now has all required columns.</p>
                  </div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-red-800'>❌ Error fixing table</h3>
                <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
              </div>";
    }
}

// Test post creation after fix
if ($_POST['action'] ?? '' === 'test_post') {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>🧪 Testing Post Creation...</h3>
          </div>";
    
    try {
        $title = 'Test Post ' . date('Y-m-d H:i:s');
        $slug = 'test-post-' . time();
        $content = 'This is a test post created after fixing the table structure.';
        
        $stmt = $pdo->prepare("
            INSERT INTO posts (title, slug, content, is_published, created_at, updated_at, published_at) 
            VALUES (?, ?, ?, true, NOW(), NOW(), NOW()) 
            RETURNING id
        ");
        
        if ($stmt->execute([$title, $slug, $content])) {
            $postId = $stmt->fetchColumn();
            echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                    <h3 class='font-bold text-green-800'>✅ Test Post Created Successfully!</h3>
                    <p class='text-green-700'>Post ID: $postId</p>
                    <p class='text-green-700'>Title: $title</p>
                    <p class='text-green-700'>Slug: $slug</p>
                  </div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-red-800'>❌ Test Post Creation Failed</h3>
                <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
              </div>";
    }
}

?>

        <div class='bg-white p-6 rounded-lg shadow mb-6'>
            <h3 class='text-lg font-semibold mb-4'>🔧 Actions</h3>
            <div class='space-x-4'>
                <form method='POST' class='inline'>
                    <button type='submit' name='action' value='fix_table'
                            class='bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors'>
                        🔧 Fix Table Structure
                    </button>
                </form>
                
                <form method='POST' class='inline'>
                    <button type='submit' name='action' value='test_post'
                            class='bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 transition-colors'>
                        🧪 Test Post Creation
                    </button>
                </form>
            </div>
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
</html>";
?>
