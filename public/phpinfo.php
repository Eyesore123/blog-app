<?php
// Security check - added automatically
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

// Show PHP info with custom styling
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Info - Railway</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .back-btn { display: inline-block; background: #666; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-bottom: 20px; }
        .back-btn:hover { background: #555; }
        .phpinfo-container { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="header">
        <h1>🐘 PHP Information</h1>
        <p>Server configuration and environment details</p>
    </div>
    
    <a href="/admin-dashboard.php?token=<?php echo htmlspecialchars($validToken); ?>" class="back-btn">← Back to Dashboard</a>
    
    <div class="phpinfo-container">
        <?php
        // Capture phpinfo output and style it
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        
        // Remove the HTML wrapper that phpinfo() adds
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        
        echo $phpinfo;
        ?>
    </div>
</body>
</html>
