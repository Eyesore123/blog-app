<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Get the requested backup file
$fileName = $_GET['file'] ?? '';

// Allow any file that starts with backup_ and ends with .sql
if (empty($fileName) || !preg_match('/^backup_.*\.sql$/', $fileName)) {
    echo "Invalid backup file name.";
    exit(1);
}

$backupDir = realpath(__DIR__ . '/../storage/app/backups');
$filePath = realpath($backupDir . DIRECTORY_SEPARATOR . $fileName);

// Security: Ensure the file is inside the backup directory
if (!$filePath || strpos($filePath, $backupDir) !== 0 || !is_file($filePath)) {
    echo "Backup file not found.";
    exit(1);
}

// Set headers for file download
while (ob_get_level()) ob_end_clean();
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Read the file and output it to the browser
readfile($filePath);
exit;