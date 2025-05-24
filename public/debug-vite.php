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
    <title>Debug Vite Build</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>⚡ Debug Custom Vite Build</h1>";

// Check build files
$buildDir = '/app/public/build';
$manifestPath = $buildDir . '/manifest.json';
$altManifestPath = $buildDir . '/.vite/manifest.json';

echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
        <h3 class='text-lg font-semibold mb-4'>📁 Build Files Status (Custom Vite Helper)</h3>";

$buildExists = is_dir($buildDir);
$manifestExists = file_exists($manifestPath);
$altManifestExists = file_exists($altManifestPath);

echo "<div class='space-y-2'>
        <div class='flex justify-between items-center p-2 border-b'>
            <span>Build directory (/public/build)</span>
            <span class='" . ($buildExists ? 'text-green-600' : 'text-red-600') . "'>" . 
                ($buildExists ? '✅ Exists' : '❌ Missing') . "</span>
        </div>
        <div class='flex justify-between items-center p-2 border-b'>
            <span>Main manifest (/build/manifest.json)</span>
            <span class='" . ($manifestExists ? 'text-green-600' : 'text-red-600') . "'>" . 
                ($manifestExists ? '✅ Exists' : '❌ Missing') . "</span>
        </div>
        <div class='flex justify-between items-center p-2 border-b'>
            <span>Alt manifest (/build/.vite/manifest.json)</span>
            <span class='" . ($altManifestExists ? 'text-green-600' : 'text-red-600') . "'>" . 
                ($altManifestExists ? '✅ Exists' : '❌ Missing') . "</span>
        </div>
      </div>";

// Show which manifest is being used
if ($manifestExists) {
    echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
            <h4 class='font-bold text-green-800'>✅ Using Main Manifest</h4>
            <p class='text-green-700'>ViteHelper will use /build/manifest.json</p>
          </div>";
    
    $manifest = json_decode(file_get_contents($manifestPath), true);
} elseif ($altManifestExists) {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h4 class='font-bold text-yellow-800'>⚠️ Using Alt Manifest</h4>
            <p class='text-yellow-700'>ViteHelper will copy from /build/.vite/manifest.json to /build/manifest.json</p>
          </div>";
    
    $manifest = json_decode(file_get_contents($altManifestPath), true);
} else {
    echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
            <h4 class='font-bold text-red-800'>❌ No Manifest Found</h4>
            <p class='text-red-700'>ViteHelper will use fallback hardcoded assets</p>
          </div>";
    
    $manifest = null;
}

if ($manifest) {
    echo "<div class='mt-4'>
            <h4 class='font-semibold mb-2'>📄 Manifest Contents</h4>
            <pre class='bg-gray-100 p-4 rounded text-xs overflow-x-auto'>" . 
                htmlspecialchars(json_encode($manifest, JSON_PRETTY_PRINT)) . "</pre>
          </div>";
    
    // Check if the hardcoded fallback files exist
    $fallbackFiles = [
        '/app/public/build/assets/app-DWrjc4j3.css',
        '/app/public/build/assets/app-_lpql_RX.js'
    ];
    
    echo "<div class='mt-4'>
            <h4 class='font-semibold mb-2'>🔄 Fallback Files Status</h4>";
    
    foreach ($fallbackFiles as $file) {
        $exists = file_exists($file);
        $filename = basename($file);
        echo "<div class='flex justify-between items-center p-2 border-b'>
                <span>$filename</span>
                <span class='" . ($exists ? 'text-green-600' : 'text-red-600') . "'>" . 
                    ($exists ? '✅ Exists' : '❌ Missing') . "</span>
              </div>";
    }
    echo "</div>";
}

// Check actual built assets
if ($buildExists) {
    $assetsDir = $buildDir . '/assets';
    if (is_dir($assetsDir)) {
        $assetFiles = scandir($assetsDir);
        $assetFiles = array_filter($assetFiles, function($file) {
            return !in_array($file, ['.', '..']);
        });
        
        echo "<div class='mt-4'>
                <h4 class='font-semibold mb-2'>📂 Actual Built Assets</h4>
                <div class='bg-gray-100 p-4 rounded max-h-60 overflow-y-auto'>
                    <ul class='text-sm space-y-1'>";
        
        foreach ($assetFiles as $file) {
            $filePath = $assetsDir . '/' . $file;
            $size = filesize($filePath);
            $modified = date('M j H:i', filemtime($filePath));
            echo "<li class='flex justify-between'>
                    <span>$file</span>
                    <span class='text-gray-500'>$modified (" . number_format($size) . " bytes)</span>
                  </li>";
        }
        
        echo "      </ul>
                </div>
              </div>";
    }
}

echo "</div>";

// Check Laravel logs for Vite errors
echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
        <h3 class='text-lg font-semibold mb-4'>📋 Recent Laravel Logs</h3>";

$logPath = '/app/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $logContent = file_get_contents($logPath);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -20); // Last 20 lines
    
    echo "<div class='bg-gray-100 p-4 rounded text-xs overflow-x-auto max-h-60 overflow-y-auto'>
            <pre>" . htmlspecialchars(implode("\n", $recentLines)) . "</pre>
          </div>";
} else {
    echo "<div class='text-gray-500'>No log file found</div>";
}

echo "</div>";

// Actions
if ($_POST['action'] ?? '' === 'fix_manifest') {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>🔧 Fixing Manifest...</h3>
          </div>";
    
    if ($altManifestExists && !$manifestExists) {
        if (copy($altManifestPath, $manifestPath)) {
            echo "<div class='bg-green-100 border border-green-400 p-2 rounded mb-2'>
                    ✅ Copied manifest from .vite directory to main location
                  </div>";
        } else {
            echo "<div class='bg-red-100 border border-red-400 p-2 rounded mb-2'>
                    ❌ Failed to copy manifest
                  </div>";
        }
    } else {
        echo "<div class='bg-blue-100 border border-blue-400 p-2 rounded mb-2'>
                ℹ️ No action needed - manifest already in correct location
              </div>";
    }
}

if ($_POST['action'] ?? '' === 'rebuild') {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>🔨 Rebuilding Assets...</h3>
          </div>";
    
    $output = shell_exec('cd /app && npm run build 2>&1');
    echo "<div class='bg-blue-100 border border-blue-400 p-4 rounded mb-2'>
            <strong>Build Output:</strong>
            <pre class='text-xs mt-2 overflow-x-auto'>" . htmlspecialchars($output) . "</pre>
          </div>";
    
    // Check if build was successful
    if (file_exists($manifestPath) || file_exists($altManifestPath)) {
        echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-green-800'>✅ Build Complete!</h3>
              </div>";
    } else {
        echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-red-800'>❌ Build may have failed - no manifest found</h3>
              </div>";
    }
}

?>

        <div class='bg-white p-6 rounded-lg shadow mb-6'>
            <h3 class='text-lg font-semibold mb-4'>🔨 Actions</h3>
            <div class='space-x-4'>
                <form method='POST' class='inline'>
                    <button type='submit' name='action' value='fix_manifest'
                            class='bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors'>
                        🔧 Fix Manifest Location
                    </button>
                </form>
                
                <form method='POST' class='inline'>
                    <button type='submit' name='action' value='rebuild'
                            class='bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 transition-colors'>
                        🔨 Rebuild Assets
                    </button>
                </form>
            </div>
        </div>
        
        <div class='text-center'>
            <a href='/admin-dashboard.php?token=<?php echo $validToken; ?>' 
               class='px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors'>
                ← Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
