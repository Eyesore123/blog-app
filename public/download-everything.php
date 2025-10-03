<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';
if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Full Backup (DB + Storage)</h1>";

// Directories
$backupDir = __DIR__ . '/../storage/app/backups';
$storageDir = __DIR__ . '/../storage';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

// --------------------
// Handle Delete Request
// --------------------
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']); // sanitize
    $fullPath = "$backupDir/$fileToDelete";
    if (file_exists($fullPath)) {
        unlink($fullPath);
        echo "<p style='color:red;'>Deleted backup: $fileToDelete</p>";
    } else {
        echo "<p style='color:red;'>File not found: $fileToDelete</p>";
    }
}

// --------------------
// Handle Download Request
// --------------------
if (isset($_GET['download'])) {
    $fileToDownload = basename($_GET['download']);
    $fullPath = "$backupDir/$fileToDownload";
    if (!file_exists($fullPath)) {
        die("Invalid backup file name.");
    }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $fileToDownload . '"');
    header('Content-Length: ' . filesize($fullPath));
    readfile($fullPath);
    exit;
}

// --------------------
// Handle Backup Now
// --------------------
if (isset($_POST['backup_now'])) {
    $timestamp = date('Y-m-d_H-i-s');
    $zipFile = "$backupDir/backup_full_$timestamp.zip";

    // --- 1️⃣ Database Backup (PHP-based) ---
    $databaseUrl = getenv('DATABASE_URL');
    if (!$databaseUrl) {
        echo "<p>DATABASE_URL is not set!</p>";
        exit;
    }

    $dbParts = parse_url($databaseUrl);
    $host = $dbParts['host'] ?? '';
    $port = $dbParts['port'] ?? 5432;
    $database = ltrim($dbParts['path'] ?? '', '/');
    $username = $dbParts['user'] ?? '';
    $password = $dbParts['pass'] ?? '';

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sqlContent = "-- Database backup generated on " . date('Y-m-d H:i:s') . "\n\n";

        $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name")
                      ->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $columns = $pdo->query("
                SELECT column_name, data_type, is_nullable, column_default, character_maximum_length
                FROM information_schema.columns
                WHERE table_schema='public' AND table_name='$table'
            ")->fetchAll(PDO::FETCH_ASSOC);

            $sqlContent .= "-- Table structure for $table\nDROP TABLE IF EXISTS $table CASCADE;\nCREATE TABLE $table (\n";
            $colsSql = [];
            foreach ($columns as $col) {
                $colSql = $col['column_name'] . ' ' . $col['data_type'];
                if ($col['character_maximum_length']) $colSql .= "({$col['character_maximum_length']})";
                if ($col['is_nullable'] === 'NO') $colSql .= " NOT NULL";
                if ($col['column_default']) $colSql .= " DEFAULT {$col['column_default']}";
                $colsSql[] = $colSql;
            }
            $sqlContent .= implode(",\n", $colsSql) . "\n);\n\n";

            // Table data
            $rows = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) {
                $sqlContent .= "-- Data for $table\n";
                foreach ($rows as $row) {
                    $cols = array_keys($row);
                    $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), array_values($row));
                    $sqlContent .= "INSERT INTO $table (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
                }
                $sqlContent .= "\n";
            }
        }

        $sqlFile = "$backupDir/db_backup_$timestamp.sql";
        file_put_contents($sqlFile, $sqlContent);
        echo "<p>Database backup completed: $sqlFile</p>";
    } catch (PDOException $e) {
        echo "<p>Database backup failed: {$e->getMessage()}</p>";
        exit;
    }

    // --- 2️⃣ Create ZIP with DB + storage ---
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
        echo "<p>Cannot create ZIP file!</p>";
        exit;
    }

    $zip->addFile($sqlFile, basename($sqlFile));

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storageDir));
    foreach ($files as $file) {
        if ($file->isDir()) continue;
        $filePath = $file->getRealPath();
        $relativePath = 'storage/' . substr($filePath, strlen($storageDir) + 1);
        $zip->addFile($filePath, $relativePath);
    }

    $zip->close();
    echo "<p>Full backup ZIP created: $zipFile</p>";
}

// --------------------
// HTML Form
// --------------------
?>
<form method="post" style="margin-bottom:20px;">
    <input type="hidden" name="token" value="<?= htmlspecialchars($providedToken) ?>">
    <button type="submit" name="backup_now" style="padding:10px 20px;font-size:16px;">Create Full Backup</button>
</form>

<h2>Existing Backups</h2>
<ul>
<?php
$backups = glob($backupDir . "/backup_full_*.zip");
if (!$backups) {
    echo "<li>No backups found.</li>";
} else {
    rsort($backups);
    foreach ($backups as $backup) {
        $fileName = basename($backup);
        $fileSize = filesize($backup);
        $fileSizeFormatted = $fileSize > 1024 ? round($fileSize / 1024, 2) . " KB" : $fileSize . " bytes";
        $fileDate = date("F j, Y, g:i a", filemtime($backup));

        $downloadUrl = "/download-backup.php?token=$validToken&file=$fileName";
        $deleteUrl   = "?token=$validToken&delete=$fileName";


        echo "<li>
                $fileName - $fileSizeFormatted - $fileDate
                <a href='$downloadUrl' style='margin-left:10px;'>Download</a>
                <a href='$deleteUrl' style='margin-left:10px;color:red;' onclick='return confirm(\"Delete this backup?\")'>Delete</a>
              </li>";
    }
}
?>
</ul>
