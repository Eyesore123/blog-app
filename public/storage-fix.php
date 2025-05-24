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
    <title>Fix Storage</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>🔧 Fix Storage Issues</h1>";

if ($_POST['action'] ?? '' === 'fix_storage') {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>🔧 Fixing Storage...</h3>
          </div>";
    
    // Create storage symlink
    $publicStorage = '/app/public/storage';
    $storagePublic = '/app/storage/app/public';
    
    // Remove existing if it exists
    if (file_exists($publicStorage)) {
        if (is_link($publicStorage)) {
            unlink($publicStorage);
            echo "<div class='bg-blue-100 border border-blue-400 p-2 rounded mb-2'>
                    🗑️ Removed existing symlink
                  </div>";
        } else {
            echo "<div class='bg-red-100 border border-red-400 p-2 rounded mb-2'>
                    ❌ /public/storage exists but is not a symlink
                  </div>";
        }
    }
    
    // Create symlink
    if (symlink($storagePublic, $publicStorage)) {
        echo "<div class='bg-green-100 border border-green-400 p-2 rounded mb-2'>
                ✅ Created storage symlink successfully
              </div>";
    } else {
        echo "<div class='bg-red-100 border border-red-400 p-2 rounded mb-2'>
                ❌ Failed to create storage symlink
              </div>";
    }
    
    // Create necessary directories
    $dirs = [
        '/app/storage/app/public/posts',
        '/app/storage/app/public/images'
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "<div class='bg-green-100 border border-green-400 p-2 rounded mb-2'>
                        ✅ Created directory: $dir
                      </div>";
            } else {
                echo "<div class='bg-red-100 border border-red-400 p-2 rounded mb-2'>
                        ❌ Failed to create directory: $dir
                      </div>";
            }
        } else {
            echo "<div class='bg-blue-100 border border-blue-400 p-2 rounded mb-2'>
                    ℹ️ Directory already exists: $dir
                  </div>";
        }
    }
    
    echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-green-800'>✅ Storage Fixed!</h3>
            <p class='text-green-700'>Storage symlink created and directories set up.</p>
          </div>";
}

if ($_POST['action'] ?? '' === 'clear_cache') {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>🧹 Clearing Caches...</h3>
          </div>";
    
    // Clear Laravel caches
    $commands = [
        'cd /app && php artisan config:clear',
        'cd /app && php artisan cache:clear',
        'cd /app && php artisan view:clear',
        'cd /app && php artisan route:clear'
    ];
    
    foreach ($commands as $cmd) {
        $output = shell_exec($cmd . ' 2>&1');
        $cmdName = explode(' ', $cmd)[3] ?? 'command';
        echo "<div class='bg-blue-100 border border-blue-400 p-2 rounded mb-2'>
                ✅ Cleared $cmdName cache
              </div>";
    }
    
    echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-green-800'>✅ Caches Cleared!</h3>
          </div>";
}

?>

        <div class='grid grid-cols-1 md:grid-cols-2 gap-6'>
            <div class='bg-white p-6 rounded-lg shadow'>
                <h3 class='text-lg font-semibold mb-4'>🔗 Fix Storage Symlink</h3>
                <p class='text-gray-600 mb-4'>Creates the symlink so uploaded images can be accessed publicly.</p>
                
                <form method='POST'>
                    <button type='submit' name='action' value='fix_storage'
                            class='bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors'>
                        🔧 Fix Storage
                    </button>
                </form>
            </div>
            
            <div class='bg-white p-6 rounded-lg shadow'>
                <h3 class='text-lg font-semibold mb-4'>🧹 Clear All Caches</h3>
                <p class='text-gray-600 mb-4'>Clears Laravel caches that might prevent updates.</p>
                
                <form method='POST'>
                    <button type='submit' name='action' value='clear_cache'
                            class='bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition-colors'>
                        🧹 Clear Caches
                    </button>
                </form>
            </div>
        </div>
        
        <div class='mt-6 text-center'>
            <a href='/debug-posts.php?token=<?php echo $validToken; ?>' 
               class='px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors mr-4'>
                🔍 Check Status Again
            </a>
            <a href='/admin-dashboard.php?token=<?php echo $validToken; ?>' 
               class='px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors'>
                ← Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
