<?php
// Simple security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

$filename = $_GET['file'] ?? '';

// Security: only allow PHP files in the public directory
if (empty($filename) || !preg_match('/^[a-zA-Z0-9_-]+\.php$/', $filename)) {
    echo json_encode(['error' => 'Invalid filename']);
    exit;
}

$filepath = __DIR__ . '/' . $filename;

if (!file_exists($filepath)) {
    echo json_encode(['error' => 'File not found']);
    exit;
}

$fileInfo = [
    'filename' => $filename,
    'path' => $filepath,
    'size' => number_format(filesize($filepath)),
    'modified' => date('Y-m-d H:i:s', filemtime($filepath)),
    'permissions' => substr(sprintf('%o', fileperms($filepath)), -4),
    'type' => 'PHP Script',
    'lines' => count(file($filepath))
];

header('Content-Type: application/json');
echo json_encode($fileInfo);
