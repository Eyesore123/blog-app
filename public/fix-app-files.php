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
    <title>Fix App Files</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>🔧 Fix App Files</h1>";

// Files that should NOT have security checks (used by Laravel app)
$appFiles = [
    'file-info.php' => 'File info endpoint for Laravel',
    'index.php' => 'Laravel entry point'
];

// Files that SHOULD have security checks (admin tools)
$adminFiles = [
    'admin-dashboard.php' => 'Admin dashboard',
    'backup-database.php' => 'Database backup tool',
    'create-admin.php' => 'Admin user creator',
    'db-test.php' => 'Database test tool',
    'manage-backups.php' => 'Backup management',
    'secure-all-scripts.php' => 'Security script'
];

echo "<div class='bg-blue-100 border border-blue-400 p-4 rounded mb-6'>
        <h3 class='font-bold text-blue-800'>📋 File Security Status</h3>
        <p class='text-blue-700'>Checking which files should and shouldn't have security checks...</p>
      </div>";

// Check current status
foreach ($appFiles as $filename => $description) {
    $filePath = __DIR__ . '/' . $filename;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $hasSecurityCheck = strpos($content, 'ADMIN_SETUP_TOKEN') !== false;
        
        $status = $hasSecurityCheck ? 
            '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">❌ Has Security (WRONG)</span>' : 
            '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">✅ No Security (CORRECT)</span>';
        
        echo "<div class='bg-white p-4 rounded mb-2 flex justify-between items-center'>
                <div>
                    <strong>$filename</strong> - $description
                </div>
                <div>$status</div>
              </div>";
    }
}

foreach ($adminFiles as $filename => $description) {
    $filePath = __DIR__ . '/' . $filename;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $hasSecurityCheck = strpos($content, 'ADMIN_SETUP_TOKEN') !== false;
        
        $status = $hasSecurityCheck ? 
            '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">✅ Has Security (CORRECT)</span>' : 
            '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">❌ No Security (WRONG)</span>';
        
        echo "<div class='bg-white p-4 rounded mb-2 flex justify-between items-center'>
                <div>
                    <strong>$filename</strong> - $description
                </div>
                <div>$status</div>
              </div>";
    }
}

if ($_POST['action'] ?? '' === 'fix') {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>🔧 Fixing Files...</h3>
          </div>";
    
    // Remove security from app files
    foreach ($appFiles as $filename => $description) {
        $filePath = __DIR__ . '/' . $filename;
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            
            if (strpos($content, 'ADMIN_SETUP_TOKEN') !== false) {
                // Remove security check block
                $pattern = '/\/\/ Security check[^}]+}\s*/s';
                $content = preg_replace($pattern, '', $content);
                
                // Also remove any standalone security checks
                $lines = explode("\n", $content);
                $cleanLines = [];
                $skipNext = false;
                
                foreach ($lines as $line) {
                    if (strpos($line, 'ADMIN_SETUP_TOKEN') !== false || 
                        strpos($line, 'providedToken') !== false ||
                        strpos($line, 'validToken') !== false) {
                        $skipNext = true;
                        continue;
                    }
                    if ($skipNext && (trim($line) === '}' || strpos($line, 'exit') !== false)) {
                        $skipNext = false;
                        continue;
                    }
                    if (!$skipNext) {
                        $cleanLines[] = $line;
                    }
                }
                
                $cleanContent = implode("\n", $cleanLines);
                file_put_contents($filePath, $cleanContent);
                
                echo "<div class='bg-green-100 border border-green-400 p-2 rounded mb-2'>
                        ✅ Removed security from $filename
                      </div>";
            }
        }
    }
    
    echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-green-800'>✅ Files Fixed!</h3>
            <p class='text-green-700'>App files no longer have security checks. Admin files still protected.</p>
          </div>";
}

?>

        <div class='mt-6'>
            <form method='POST' class='bg-white p-6 rounded-lg shadow'>
                <h3 class='text-lg font-semibold mb-4'>🔧 Fix File Security</h3>
                <p class='text-gray-600 mb-4'>This will remove security checks from app files (like file-info.php) while keeping them on admin tools.</p>
                
                <button type='submit' name='action' value='fix'
                        class='bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition-colors'>
                    🔧 Fix Files Now
                </button>
            </form>
        </div>
        
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
