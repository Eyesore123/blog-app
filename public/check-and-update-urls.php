<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) { http_response_code(404); exit; }


/**
 * Web-based admin script for scanning/replacing/reverting URLs
 * Includes database content and media files in /storage/uploads/
 */

$oldUrl = 'https://blog-app-production-16c2.up.railway.app';
$newUrl = 'https://blog.joniputkinen.com';
$backupFile = __DIR__ . '/url_replacement_backup.json';
$uploadsDir = __DIR__ . '/storage/uploads';

$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) die("DATABASE_URL not set");

$dbParts = parse_url($databaseUrl);

$dsn = "pgsql:host={$dbParts['host']};port=" . ($dbParts['port'] ?? 5432) . ";dbname=" . ltrim($dbParts['path'], '/');
$user = $dbParts['user'] ?? '';
$pass = $dbParts['pass'] ?? '';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}


// ===== Helper functions =====
function scanTables(PDO $pdo, string $oldUrl) {
    $backupData = [];
    $tablesStmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $columnsStmt = $pdo->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = :table AND data_type IN ('character varying', 'text')
        ");
        $columnsStmt->execute([':table' => $table]);
        $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($columns as $column) {
        // Check if the table has a primary key column (or just include the first column as identifier)
        $idColumn = null;
        $pkStmt = $pdo->prepare("
            SELECT a.attname as column_name
            FROM pg_index i
            JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
            WHERE i.indrelid = :table::regclass AND i.indisprimary
        ");
        $pkStmt->execute([':table' => $table]);
        $pkCols = $pkStmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($pkCols)) $idColumn = $pkCols[0]; // use first primary key

        // Build SELECT dynamically
        $selectCols = $idColumn ? "$idColumn, $column" : $column;

        $stmt = $pdo->prepare("SELECT $selectCols FROM $table WHERE $column LIKE :like LIMIT 20");
        $stmt->execute([':like' => "%$oldUrl%"]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $backupData[] = [
                'type' => 'db',
                'table' => $table,
                'column' => $column,
                'id' => $idColumn ? $row[$idColumn] : null,
                'original' => $row[$column],
            ];
        }
    }

    }

    return $backupData;
}

function scanUploads(string $uploadsDir, string $oldUrl) {
    $filesToUpdate = [];
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadsDir));

    foreach ($rii as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['html', 'htm', 'txt'])) {
            $content = file_get_contents($file->getPathname());
            if (strpos($content, $oldUrl) !== false) {
                $filesToUpdate[] = [
                    'type' => 'file',
                    'path' => $file->getPathname(),
                    'original' => $content,
                ];
            }
        }
    }

    return $filesToUpdate;
}

function replaceUrls(PDO $pdo, array $data, string $oldUrl, string $newUrl, string $backupFile) {
    $backupData = [];

    foreach ($data as $row) {
        if ($row['type'] === 'db') {
            if ($row['id'] !== null) {
                $stmt = $pdo->prepare("
                    UPDATE {$row['table']} 
                    SET {$row['column']} = REPLACE({$row['column']}, :old, :new) 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':old' => $oldUrl,
                    ':new' => $newUrl,
                    ':id' => $row['id'],
                ]);
            } else {
                // no primary key, update all matching rows
                $stmt = $pdo->prepare("
                    UPDATE {$row['table']}
                    SET {$row['column']} = REPLACE({$row['column']}, :old, :new)
                    WHERE {$row['column']} LIKE :like
                ");
                $stmt->execute([
                    ':old' => $oldUrl,
                    ':like' => "%$oldUrl%"
                ]);
            }
            $backupData[] = $row;
        } elseif ($row['type'] === 'file') {
            file_put_contents($row['path'], str_replace($oldUrl, $newUrl, $row['original']));
            $backupData[] = $row;
        }
    }

    // Save backup for revert
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
}

// ===== Helper functions =====
function revertUrls(PDO $pdo, string $backupFile, string $oldUrl) {
    if (!file_exists($backupFile)) return false;

    $backupData = json_decode(file_get_contents($backupFile), true);
    foreach ($backupData as $row) {
        if ($row['type'] === 'db') {
            if ($row['id'] !== null) {
                // Update by primary key
                $stmt = $pdo->prepare("
                    UPDATE {$row['table']} 
                    SET {$row['column']} = :original 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':original' => $row['original'],
                    ':id' => $row['id'],
                ]);
            } else {
                // No primary key, update all rows containing old content
                $stmt = $pdo->prepare("
                    UPDATE {$row['table']} 
                    SET {$row['column']} = :original
                    WHERE {$row['column']} LIKE :like
                ");
                $stmt->execute([
                    ':original' => $row['original'],
                    ':like' => "%$oldUrl%"
                ]);
            }
        } elseif ($row['type'] === 'file') {
            file_put_contents($row['path'], $row['original']);
        }
    }
    return true;
}

// ================= Handle actions =================
$action = $_POST['action'] ?? '';
$message = '';
$scanResults = [];
$previewResults = [];

if ($action === 'scan' || $action === 'preview') {
    $dbResults = scanTables($pdo, $oldUrl);
    $fileResults = scanUploads($uploadsDir, $oldUrl);
    $scanResults = array_merge($dbResults, $fileResults);

    if ($action === 'scan') {
        $message = count($scanResults) . " occurrences found (DB + files).";
    } else { // preview
        foreach ($scanResults as $row) {
            $previewResults[] = [
                'type' => $row['type'],
                'table' => $row['table'] ?? '',
                'column' => $row['column'] ?? '',
                'id' => $row['id'] ?? '',
                'path' => $row['path'] ?? '',
                'original_snippet' => substr($row['original'], 0, 100),
                'new_snippet' => substr(str_replace($oldUrl, $newUrl, $row['original']), 0, 100),
            ];
        }
        $message = count($previewResults) . " occurrences will be replaced (preview).";
    }
} elseif ($action === 'replace') {
    $dbResults = scanTables($pdo, $oldUrl);
    $fileResults = scanUploads($uploadsDir, $oldUrl);
    $scanResults = array_merge($dbResults, $fileResults);

    if ($scanResults) {
        replaceUrls($pdo, $scanResults, $oldUrl, $newUrl, $backupFile);
        $message = "Replacement complete. Backup saved.";
    } else {
        $message = "No occurrences found to replace.";
    }
} elseif ($action === 'revert') {
    if (revertUrls($pdo, $backupFile, $oldUrl)) {
        $message = "Revert completed from backup.";
    } else {
        $message = "No backup file found. Cannot revert.";
    }
}

// ================= HTML =================
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin URL Manager (DB + Media)</title>
<style>
body { font-family: sans-serif; padding: 2rem; background: #f9f9f9; }
.container { max-width: 1200px; margin: auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
button { padding: 0.5rem 1rem; margin-right: 1rem; cursor: pointer; border: none; border-radius: 4px; background: #0074D9; color: #fff; }
button:hover { background: #005bb5; }
table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
th, td { border: 1px solid #ddd; padding: 0.5rem; font-size: 0.9rem; }
th { background: #f0f0f0; }
pre { white-space: pre-wrap; word-wrap: break-word; }
.message { margin-top: 1rem; font-weight: bold; }
</style>
</head>
<body>
<div class="container">
<h1>Admin URL Manager (DB + Media)</h1>

<form method="post">
    <button name="action" value="scan">Scan for Old URLs</button>
    <button name="action" value="preview">Preview Replacement</button>
    <button name="action" value="replace">Replace Old URLs with New</button>
    <button name="action" value="revert">Revert Changes</button>
</form>

<?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!empty($scanResults) && $action === 'scan'): ?>
    <h2>Scan Results</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Table / File</th>
                <th>Column / Path</th>
                <th>ID</th>
                <th>Content Snippet</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($scanResults as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['type']) ?></td>
                <td><?= htmlspecialchars($row['table'] ?? $row['path']) ?></td>
                <td><?= htmlspecialchars($row['column'] ?? $row['path']) ?></td>
                <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                <td><pre><?= htmlspecialchars(substr($row['original'], 0, 100)) ?>...</pre></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif (!empty($previewResults) && $action === 'preview'): ?>
    <h2>Preview Replacement</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Table / File</th>
                <th>Column / Path</th>
                <th>ID</th>
                <th>Original Snippet</th>
                <th>After Replacement</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($previewResults as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['type']) ?></td>
                <td><?= htmlspecialchars($row['table'] ?? $row['path']) ?></td>
                <td><?= htmlspecialchars($row['column'] ?? $row['path']) ?></td>
                <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                <td><pre><?= htmlspecialchars($row['original_snippet']) ?>...</pre></td>
                <td><pre><?= htmlspecialchars($row['new_snippet']) ?>...</pre></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</div>
</body>
</html>
