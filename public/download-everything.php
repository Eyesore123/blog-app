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

// Backup filename
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

    // Start SQL content
    $sqlContent = "-- Database backup generated on " . date('Y-m-d H:i:s') . "\n\n";

    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name")
                  ->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Table structure
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

    // Save SQL file
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

// Add SQL file
$zip->addFile($sqlFile, basename($sqlFile));

// Recursively add storage folder
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storageDir));
foreach ($files as $file) {
    if ($file->isDir()) continue;
    $filePath = $file->getRealPath();
    $relativePath = 'storage/' . substr($filePath, strlen($storageDir) + 1);
    $zip->addFile($filePath, $relativePath);
}

$zip->close();
echo "<p>Full backup ZIP created: $zipFile</p>";

// --- 3️⃣ Provide download link ---
$downloadUrl = "/download-backup.php?file=" . basename($zipFile) . "&token=$validToken";
echo "<p><a href='$downloadUrl'>Download Full Backup</a></p>";

// --- 4️⃣ List previous backups ---
echo "<h2>Existing Backups</h2>";
$backups = glob($backupDir . "/backup_full_*.zip");
if (!$backups) {
    echo "<p>No previous backups found.</p>";
} else {
    echo "<ul>";
    rsort($backups);
    foreach ($backups as $backup) {
        $fileName = basename($backup);
        $fileSize = filesize($backup);
        $fileSizeFormatted = $fileSize > 1024 ? round($fileSize / 1024, 2) . " KB" : $fileSize . " bytes";
        $fileDate = date("F j, Y, g:i a", filemtime($backup));
        $downloadUrl = "/download-backup.php?file=$fileName&token=$validToken";
        echo "<li><a href='$downloadUrl'>$fileName</a> - $fileSizeFormatted - $fileDate</li>";
    }
    echo "</ul>";
}

echo "<p><a href='/'>Back to homepage</a></p>";
