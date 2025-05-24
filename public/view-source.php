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
    echo "Invalid filename";
    exit;
}

$filepath = __DIR__ . '/' . $filename;

if (!file_exists($filepath)) {
    echo "File not found";
    exit;
}

// Set content type for plain text
header('Content-Type: text/plain; charset=utf-8');

// Output the file contents
echo file_get_contents($filepath);
