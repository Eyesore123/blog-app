<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Get the requested backup file
$fileName = $_GET['file'] ?? '';

if (empty($fileName) || !preg_match('/^backup_.*\.sql$/', $fileName)) {
    echo "Invalid backup file name.";
    exit(1);
}

$backupDir = __DIR__ . '/../storage/app/backups';
$filePath = $backupDir . '/' . $fileName;

if (!file_exists($filePath)) {
    echo "Backup file not found.";
    exit(1);
}

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Clear output buffer
ob_clean();
flush();

// Read the file and output it to the browser
readfile($filePath);
exit;
