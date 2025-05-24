<?php
// Simple security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

$securityCode = "<?php
// Security check - added automatically
\$validToken = getenv('ADMIN_SETUP_TOKEN');
\$providedToken = \$_GET['token'] ?? '';

if (!\$validToken || \$providedToken !== \$validToken) {
    http_response_code(404);
    echo \"Not Found\";
    exit;
}

";

$publicDir = __DIR__;
$phpFiles = glob($publicDir . '/*.php');

// Files to skip (already secure or shouldn't be modified)
$skipFiles = [
    'index.php',
    'admin-dashboard.php',
    'view-source.php', 
    'file-info.php',
    'secure-all-scripts.php'
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Secure All Scripts</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <h1 class='text-2xl font-bold mb-4'>🔒 Securing All Admin Scripts</h1>";

$secured = 0;
$skipped = 0;
$errors = 0;

foreach ($phpFiles as $file) {
    $filename = basename($file);
    
    if (in_array($filename, $skipFiles)) {
        echo "<div class='bg-yellow-100 border border-yellow-400 p-3 mb-2 rounded'>
                ⏭️ Skipped: $filename (in skip list)
              </div>";
        $skipped++;
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Check if already secured
    if (strpos($content, 'ADMIN_SETUP_TOKEN') !== false) {
        echo "<div class='bg-blue-100 border border-blue-400 p-3 mb-2 rounded'>
                ✅ Already secured: $filename
              </div>";
        $skipped++;
        continue;
    }
    
    // Add security to the file
    if (strpos($content, '<?php') === 0) {
        $newContent = str_replace('<?php', '<?php' . "\n" . $securityCode, $content);
        
        if (file_put_contents($file, $newContent)) {
            echo "<div class='bg-green-100 border border-green-400 p-3 mb-2 rounded'>
                    🔒 Secured: $filename
                  </div>";
            $secured++;
        } else {
            echo "<div class='bg-red-100 border border-red-400 p-3 mb-2 rounded'>
                    ❌ Error securing: $filename
                  </div>";
            $errors++;
        }
    } else {
        echo "<div class='bg-red-100 border border-red-400 p-3 mb-2 rounded'>
                ❌ Invalid PHP file: $filename (doesn't start with <?php)
              </div>";
        $errors++;
    }
}

echo "<div class='bg-white p-6 rounded shadow mt-6'>
        <h2 class='text-xl font-bold mb-4'>Summary</h2>
        <div class='grid grid-cols-3 gap-4'>
            <div class='text-center'>
                <div class='text-2xl font-bold text-green-600'>$secured</div>
                <div class='text-sm'>Secured</div>
            </div>
            <div class='text-center'>
                <div class='text-2xl font-bold text-yellow-600'>$skipped</div>
                <div class='text-sm'>Skipped</div>
            </div>
            <div class='text-center'>
                <div class='text-2xl font-bold text-red-600'>$errors</div>
                <div class='text-sm'>Errors</div>
            </div>
        </div>
        <div class='mt-4 text-center'>
            <a href='/admin-dashboard.php?token=$validToken' class='px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600'>
                ← Back to Dashboard
            </a>
        </div>
      </div>";

echo "</body></html>";
?>
