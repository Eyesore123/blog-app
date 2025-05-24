<?php
// File info endpoint for Laravel app - NO SECURITY CHECK NEEDED
// This file is called internally by the Laravel application

header('Content-Type: application/json');

try {
    $filename = $_GET['filename'] ?? '';
    
    if (empty($filename)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid filename']);
        exit;
    }
    
    // Sanitize filename to prevent directory traversal
    $filename = basename($filename);
    $filePath = __DIR__ . '/storage/' . $filename;
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
        exit;
    }
    
    $fileInfo = [
        'name' => $filename,
        'size' => filesize($filePath),
        'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
        'type' => mime_content_type($filePath) ?: 'application/octet-stream'
    ];
    
    echo json_encode($fileInfo);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
