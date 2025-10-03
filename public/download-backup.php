<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    die("Unauthorized");
}

// Get requested file
$fileName = $_GET['file'] ?? '';
$fileName = basename($fileName); // sanitize
$backupDir = realpath(__DIR__ . '/../storage/app/backups');
$filePath = realpath($backupDir . DIRECTORY_SEPARATOR . $fileName);

// Security: ensure file exists and is inside backup dir
if (!$filePath || strpos($filePath, $backupDir) !== 0 || !is_file($filePath)) {
    die("Backup file not found.");
}

// Determine MIME type
$mime = mime_content_type($filePath) ?: 'application/octet-stream';

// Clean output buffer
while (ob_get_level()) ob_end_clean();

// Send headers
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Output file
readfile($filePath);
exit;
