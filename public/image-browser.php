<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { 
    http_response_code(404); 
    exit; 
}

// Fix the undefined array key warning
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Change to uploads directory to match Laravel app expectation
$uploadDir = '/app/storage/app/public/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

echo "<!DOCTYPE html><html><head><title>Image Browser</title></head>";
echo "<body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>📸 Image Browser</h1>";

// Handle file upload
if ($action === 'upload' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            $extension = $mimeToExt[$fileType] ?? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'upload-' . time() . '-' . uniqid() . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                echo "<div style='background: #d1fae5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "✅ Image uploaded successfully: $filename";
                echo "</div>";
            } else {
                echo "<div style='background: #fecaca; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "❌ Failed to move uploaded file";
                echo "</div>";
            }
        } else {
            echo "<div style='background: #fecaca; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #fecaca; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Upload error: " . $file['error'];
        echo "</div>";
    }
}

// Handle file deletion
if ($action === 'delete' && isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = $uploadDir . $filename;
    
    if (file_exists($filepath) && unlink($filepath)) {
        echo "<div style='background: #d1fae5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ File deleted: $filename";
        echo "</div>";
    } else {
        echo "<div style='background: #fecaca; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Failed to delete file";
        echo "</div>";
    }
}

// Get all image files w/ pagination
$perPage = 20;
$page = max(1, intval($_GET['page'] ?? 1));

$images= [];
$totalSize = 0;

if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $filepath = $uploadDir . $file;
        if (is_file($filepath)) {
            $size = filesize($filepath);
            $totalSize += $size;
            $imageInfo = @getimagesize($filepath);
            if ($imageInfo !== false) {
                $images[] = [
                    'name' => $file,
                    'size' => $size,
                    'modified' => filemtime($filepath),
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'type' => $imageInfo['mime']
                ];
            }
        }
    }
}

// Sort newest first
usort($images, fn($a, $b) => $b['modified'] - $a['modified']);

// Pagination calculations
$totalImages = count($images);
$totalPages = max(1, ceil($totalImages / $perPage));
$start = ($page - 1) * $perPage;
$imagesPage = array_slice($images, $start, $perPage);

// 

echo "<h3>📊 Storage Stats:</h3>";
echo "<p>" . count($images) . " images | " . formatBytes($totalSize) . " total size</p>";

// Upload form
echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📤 Upload New Image</h3>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='hidden' name='action' value='upload'>";
echo "<input type='file' name='image' accept='image/*' required style='margin: 10px 0;'>";
echo "<br><button type='submit' style='background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>Upload Image</button>";
echo "</form>";
echo "</div>";

// Display images
echo "<h3>🖼️ Images (" . count($images) . "):</h3>";

if (empty($imagesPage)) {
    echo "<p>No images found</p>";
} else {
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;'>";
    
    foreach ($imagesPage as $image) {
        // Changed from /storage/images/ to /storage/uploads/
        $imageUrl = "/storage/uploads/" . $image['name'];
        $fullUrl = "https://blog-app-production-16c2.up.railway.app" . $imageUrl;
        
        echo "<div style='border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; background: white;'>";
        
        // Image preview
        echo "<div style='text-align: center; margin-bottom: 10px;'>";
        echo "<img src='$imageUrl' alt='{$image['name']}' style='max-width: 100%; max-height: 200px; border-radius: 4px;' ";
        echo "onerror=\"this.style.display='none'; this.nextElementSibling.style.display='block';\">";
        echo "<div style='display: none; background: #f3f4f6; padding: 40px; border-radius: 4px; color: #6b7280;'>❌ Image failed to load</div>";
        echo "</div>";
        
        // Image info
        echo "<div style='font-size: 14px; color: #6b7280;'>";
        echo "<strong>{$image['name']}</strong><br>";
        echo "Size: " . formatBytes($image['size']) . "<br>";
        echo "Dimensions: {$image['width']} × {$image['height']}<br>";
        echo "Type: {$image['type']}<br>";
        echo "Modified: " . date('Y-m-d H:i:s', $image['modified']) . "<br>";
        echo "</div>";
        
        // Action buttons
        echo "<div style='margin-top: 10px;'>";
        echo "<button onclick=\"copyToClipboard('$fullUrl')\" style='background: #3b82f6; color: white; padding: 5px 10px; border: none; border-radius: 3px; margin-right: 5px; font-size: 12px;'>📋 Copy URL</button>";
        echo "<button onclick=\"copyToClipboard('$imageUrl')\" style='background: #8b5cf6; color: white; padding: 5px 10px; border: none; border-radius: 3px; margin-right: 5px; font-size: 12px;'>📋 Copy Path</button>";
        echo "<a href='?token={$_GET['token']}&action=delete&file={$image['name']}' onclick='return confirm(\"Delete this image?\")' style='background: #ef4444; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 12px;'>🗑️ Delete</a>";
        echo "</div>";
        
        echo "</div>";
    }
    
    echo "</div>";
}

// Pagination controls
echo "<div style='margin-top: 20px; text-align: center;'>";
if ($page > 1) {
    $prev = $page - 1;
    echo "<a href='?token={$_GET['token']}&page=$prev' style='margin-right: 10px;'>⬅️ Previous</a>";
}
echo " Page $page of $totalPages ";
if ($page < $totalPages) {
    $next = $page + 1;
    echo "<a href='?token={$_GET['token']}&page=$next' style='margin-left: 10px;'>Next ➡️</a>";
}
echo "</div>";

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

?>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard: ' + text);
    });
}
</script>

<p><a href="/admin-dashboard.php?token=<?php echo $_GET['token']; ?>">← Back to Dashboard</a></p>

</body></html>
