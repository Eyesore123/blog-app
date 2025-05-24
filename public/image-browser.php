<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }

$uploadsDir = __DIR__ . '/../storage/app/public/uploads';
$publicUploadsDir = __DIR__ . '/storage/uploads';

// Handle file deletion
if ($_POST['action'] === 'delete' && !empty($_POST['file'])) {
    $filename = basename($_POST['file']); // Security: only filename, no paths
    $filePath = $uploadsDir . '/' . $filename;
    
    if (file_exists($filePath) && unlink($filePath)) {
        echo "<div style='background: #d1fae5; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ Deleted: $filename";
        echo "</div>";
    } else {
        echo "<div style='background: #fee2e2; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Failed to delete: $filename";
        echo "</div>";
    }
}

// Get all uploaded files
$files = [];
if (file_exists($uploadsDir)) {
    $allFiles = array_diff(scandir($uploadsDir), ['.', '..', '.gitkeep']);
    foreach ($allFiles as $file) {
        $filePath = $uploadsDir . '/' . $file;
        if (is_file($filePath)) {
            $files[] = [
                'name' => $file,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
                'url' => "/storage/uploads/$file"
            ];
        }
    }
    
    // Sort by modification time (newest first)
    usort($files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function isImage($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Browser</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f9fafb; }
        .container { max-width: 1200px; margin: 0 auto; }
        .file-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .file-card { background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .file-preview { width: 100%; height: 200px; background: #f3f4f6; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; overflow: hidden; }
        .file-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .file-info { font-size: 14px; color: #6b7280; }
        .file-name { font-weight: bold; color: #111827; margin-bottom: 5px; word-break: break-all; }
        .file-actions { margin-top: 10px; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 12px; }
        .btn-view { background: #3b82f6; color: white; }
        .btn-copy { background: #10b981; color: white; }
        .btn-delete { background: #ef4444; color: white; }
        .stats { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .no-files { text-align: center; padding: 40px; color: #6b7280; }
    </style>
</head>
<body>

<div class="container">
    <h1>📸 Image Browser</h1>
    
    <div class="stats">
        <strong>📊 Storage Stats:</strong>
        <?php if (empty($files)): ?>
            No files uploaded yet
        <?php else: ?>
            <?php 
            $totalSize = array_sum(array_column($files, 'size'));
            $imageCount = count(array_filter($files, function($f) { return isImage($f['name']); }));
            ?>
            <?php echo count($files); ?> files total | 
            <?php echo $imageCount; ?> images | 
            <?php echo formatFileSize($totalSize); ?> total size
        <?php endif; ?>
    </div>

    <?php if (empty($files)): ?>
        <div class="no-files">
            <h3>No files found</h3>
            <p>Upload some images through your blog to see them here!</p>
        </div>
    <?php else: ?>
        <div class="file-grid">
            <?php foreach ($files as $file): ?>
                <div class="file-card">
                    <div class="file-preview">
                        <?php if (isImage($file['name'])): ?>
                            <img src="<?php echo $file['url']; ?>" alt="<?php echo htmlspecialchars($file['name']); ?>" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none; color: #ef4444;">❌ Image failed to load</div>
                        <?php else: ?>
                            <div style="font-size: 48px; color: #9ca3af;">📄</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="file-name"><?php echo htmlspecialchars($file['name']); ?></div>
                    
                    <div class="file-info">
                        Size: <?php echo formatFileSize($file['size']); ?><br>
                        Modified: <?php echo date('Y-m-d H:i:s', $file['modified']); ?>
                    </div>
                    
                    <div class="file-actions">
                        <a href="<?php echo $file['url']; ?>" target="_blank" class="btn btn-view">👁️ View</a>
                        <button onclick="copyUrl('<?php echo $file['url']; ?>')" class="btn btn-copy">📋 Copy URL</button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this file?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="file" value="<?php echo htmlspecialchars($file['name']); ?>">
                            <button type="submit" class="btn btn-delete">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="/admin-dashboard.php?token=<?php echo $validToken; ?>" style="color: #6b7280;">← Back to Dashboard</a>
    </div>
</div>

<script>
function copyUrl(url) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard.writeText(fullUrl).then(function() {
        alert('URL copied to clipboard: ' + fullUrl);
    }).catch(function() {
        prompt('Copy this URL:', fullUrl);
    });
}
</script>

</body>
</html>
