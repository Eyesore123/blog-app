<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Handle backup deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $fileName = $_GET['delete'];
    
    if (preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $fileName)) {
        $backupDir = __DIR__ . '/../storage/app/backups';
        $filePath = $backupDir . '/' . $fileName;
        
        if (file_exists($filePath) && unlink($filePath)) {
            $message = "Backup file '$fileName' deleted successfully.";
        } else {
            $error = "Failed to delete backup file '$fileName'.";
        }
    } else {
        $error = "Invalid backup file name.";
    }
}

// Handle backup restoration
if (isset($_GET['restore']) && !empty($_GET['restore'])) {
    $fileName = $_GET['restore'];
    
    if (preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $fileName)) {
        $backupDir = __DIR__ . '/../storage/app/backups';
        $filePath = $backupDir . '/' . $fileName;
        
        if (file_exists($filePath)) {
            // Get the DATABASE_URL
            $databaseUrl = getenv('DATABASE_URL');
            
            if (!$databaseUrl) {
                $error = "DATABASE_URL is not set!";
            } else {
                // Parse the DATABASE_URL
                $dbParts = parse_url($databaseUrl);
                $host = $dbParts['host'] ?? '';
                $port = $dbParts['port'] ?? 5432;
                $database = ltrim($dbParts['path'] ?? '', '/');
                $username = $dbParts['user'] ?? '';
                $password = $dbParts['pass'] ?? '';
                
                // Set PGPASSWORD environment variable
                putenv("PGPASSWORD=$password");
                
                // Build the psql command
                $command = "psql -h $host -p $port -U $username -d $database -f $filePath";
                
                // Execute the command
                $output = [];
                $return_var = 0;
                exec($command . " 2>&1", $output, $return_var);
                
                // Clear the PGPASSWORD environment variable
                putenv("PGPASSWORD");
                
                if ($return_var !== 0) {
                    $error = "Error restoring backup: " . implode("\n", $output);
                } else {
                    $message = "Backup file '$fileName' restored successfully.";
                }
            }
        } else {
            $error = "Backup file not found.";
        }
    } else {
        $error = "Invalid backup file name.";
    }
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Backup Management</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 min-h-screen'>
    <div class='container mx-auto px-4 py-8'>
        <h1 class='text-3xl font-bold mb-6'>Database Backup Management</h1>";

if (isset($error)) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4' role='alert'>
            <p>$error</p>
          </div>";
}

if (isset($message)) {
    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4' role='alert'>
            <p>$message</p>
          </div>";
}

echo "<div class='mb-6 flex space-x-4'>
            <a href='/backup-database.php?token=$validToken' class='px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600'>Create New Backup</a>
            <a href='/' class='px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600'>Back to Homepage</a>
        </div>
        
        <div class='bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4'>
            <h2 class='text-xl font-semibold mb-4'>Existing Backups</h2>";

$backupDir = __DIR__ . '/../storage/app/backups';
if (!is_dir($backupDir)) {
    echo "<p class='text-gray-600'>No backups directory found.</p>";
} else {
    $backups = glob($backupDir . "/backup_*.sql");
    
    if (empty($backups)) {
        echo "<p class='text-gray-600'>No backups found.</p>";
    } else {
        echo "<div class='overflow-x-auto'>
                <table class='min-w-full bg-white'>
                    <thead>
                        <tr>
                            <th class='py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider'>Filename</th>
                            <th class='py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider'>Size</th>
                            <th class='py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider'>Date</th>
                            <th class='py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider'>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        rsort($backups); // Sort by newest first
        foreach ($backups as $backup) {
            $fileName = basename($backup);
            $fileSize = filesize($backup);
            $fileSizeFormatted = $fileSize > 1024 ? round($fileSize / 1024, 2) . " KB" : $fileSize . " bytes";
            $fileDate = date("F j, Y, g:i a", filemtime($backup));
            
            $downloadUrl = "/download-backup.php?file=$fileName&token=$validToken";
            $deleteUrl = "/manage-backups.php?delete=$fileName&token=$validToken";
            $restoreUrl = "/manage-backups.php?restore=$fileName&token=$validToken";
            
            echo "<tr>
                    <td class='py-2 px-4 border-b border-gray-200'>$fileName</td>
                    <td class='py-2 px-4 border-b border-gray-200'>$fileSizeFormatted</td>
                    <td class='py-2 px-4 border-b border-gray-200'>$fileDate</td>
                    <td class='py-2 px-4 border-b border-gray-200'>
                        <a href='$downloadUrl' class='text-blue-500 hover:text-blue-700 mr-2'>Download</a>
                        <a href='$restoreUrl' class='text-green-500 hover:text-green-700 mr-2' onclick='return confirm(\"Are you sure you want to restore this backup? This will overwrite your current database.\")'>Restore</a>
                        <a href='$deleteUrl' class='text-red-500 hover:text-red-700' onclick='return confirm(\"Are you sure you want to delete this backup?\")'>Delete</a>
                    </td>
                  </tr>";
        }
        
        echo "</tbody>
                </table>
              </div>";
    }
}

echo "</div>
        
        <div class='bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4'>
            <h2 class='text-xl font-semibold mb-4'>Scheduled Backups</h2>
            <p class='mb-4'>To set up scheduled backups, you can use Railway's cron job feature or an external service like GitHub Actions.</p>
            
            <h3 class='text-lg font-semibold mb-2'>Railway Cron Job</h3>
            <p class='mb-4'>Add a new service to your Railway project with the following command:</p>
            <pre class='bg-gray-100 p-4 rounded mb-4'>curl -X POST https://your-app-url.up.railway.app/backup-database.php?token=your_token</pre>
            
            <h3 class='text-lg font-semibold mb-2'>GitHub Actions</h3>
            <p class='mb-4'>Create a GitHub Actions workflow file in your repository:</p>
            <pre class='bg-gray-100 p-4 rounded mb-4'>name: Database Backup

on:
  schedule:
    - cron: '0 0 * * *'  # Run daily at midnight UTC
  workflow_dispatch:     # Allow manual trigger

jobs:
  backup:
    runs-on: ubuntu-latest
    steps:
      - name: Trigger backup
        run: |
          curl -X POST https://your-app-url.up.railway.app/backup-database.php?token=\${{ secrets.ADMIN_SETUP_TOKEN }}</pre>
        </div>
    </div>
</body>
</html>";
