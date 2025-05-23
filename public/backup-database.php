<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>PostgreSQL Database Backup</h1>";

// Get the DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo "<p>DATABASE_URL is not set!</p>";
    exit;
}

// Parse the DATABASE_URL
$dbParts = parse_url($databaseUrl);
$host = $dbParts['host'] ?? '';
$port = $dbParts['port'] ?? 5432;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

// Create backup directory if it doesn't exist
$backupDir = __DIR__ . '/../storage/app/backups';
if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0755, true)) {
        echo "<p>Failed to create backup directory: $backupDir</p>";
        exit;
    }
}

// Generate backup filename with timestamp
$timestamp = date('Y-m-d_H-i-s');
$backupFile = $backupDir . "/backup_$timestamp.sql";

// Set PGPASSWORD environment variable
putenv("PGPASSWORD=$password");

// Build the pg_dump command
$command = "pg_dump -h $host -p $port -U $username -d $database -f $backupFile";

// Execute the command
echo "<h2>Running Backup</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec($command . " 2>&1", $output, $return_var);

if ($return_var !== 0) {
    echo "Error executing pg_dump command:\n";
    print_r($output);
    echo "</pre>";
    
    echo "<h2>Alternative Backup Method</h2>";
    echo "<p>Trying PHP-based backup method...</p>";
    
    try {
        // Connect to the database
        $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get all tables
        $stmt = $pdo->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public'
            ORDER BY table_name
        ");
        
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Start the SQL file
        $sql = "-- Database backup generated on " . date('Y-m-d H:i:s') . "\n\n";
        
        // Add table creation and data for each table
        foreach ($tables as $table) {
            echo "Processing table: $table\n";
            
            // Get table creation SQL
            $stmt = $pdo->query("
                SELECT 
                    'CREATE TABLE ' || table_name || ' (' ||
                    string_agg(
                        column_name || ' ' || data_type ||
                        CASE 
                            WHEN character_maximum_length IS NOT NULL THEN '(' || character_maximum_length || ')'
                            ELSE ''
                        END ||
                        CASE 
                            WHEN is_nullable = 'NO' THEN ' NOT NULL'
                            ELSE ''
                        END ||
                        CASE 
                            WHEN column_default IS NOT NULL THEN ' DEFAULT ' || column_default
                            ELSE ''
                        END,
                        ', '
                    ) || ');' as create_table_sql
                FROM 
                    information_schema.columns
                WHERE 
                    table_schema = 'public' AND table_name = '$table'
                GROUP BY 
                    table_name
            ");
            
            $createTableSql = $stmt->fetchColumn();
            $sql .= "-- Table structure for table $table\n";
            $sql .= "DROP TABLE IF EXISTS $table CASCADE;\n";
            $sql .= "$createTableSql\n\n";
            
            // Get table data
            $stmt = $pdo->query("SELECT * FROM $table");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $sql .= "-- Data for table $table\n";
                
                foreach ($rows as $row) {
                    $columns = array_keys($row);
                    $values = array_map(function($value) use ($pdo) {
                        if ($value === null) {
                            return 'NULL';
                        } elseif (is_numeric($value)) {
                            return $value;
                        } else {
                            return $pdo->quote($value);
                        }
                    }, array_values($row));
                    
                    $sql .= "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                
                $sql .= "\n";
            }
        }
        
        // Write the SQL to the backup file
        if (file_put_contents($backupFile, $sql)) {
            echo "PHP-based backup completed successfully.\n";
        } else {
            echo "Failed to write PHP-based backup to file.\n";
        }
        
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }
    
    echo "</pre>";
} else {
    echo "Backup completed successfully.\n";
    echo "Command: $command\n";
    echo "Output:\n";
    print_r($output);
    echo "</pre>";
}

// Clear the PGPASSWORD environment variable
putenv("PGPASSWORD");

// Check if the backup file was created
if (file_exists($backupFile)) {
    $fileSize = filesize($backupFile);
    $fileSizeFormatted = $fileSize > 1024 ? round($fileSize / 1024, 2) . " KB" : $fileSize . " bytes";
    
    echo "<p>Backup file created: $backupFile ($fileSizeFormatted)</p>";
    
    // Provide a download link
    $downloadUrl = "/download-backup.php?file=" . basename($backupFile) . "&token=$validToken";
    echo "<p><a href='$downloadUrl' class='px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600'>Download Backup</a></p>";
    
    // List existing backups
    echo "<h2>Existing Backups</h2>";
    $backups = glob($backupDir . "/backup_*.sql");
    
    if (empty($backups)) {
        echo "<p>No previous backups found.</p>";
    } else {
        echo "<ul>";
        rsort($backups); // Sort by newest first
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
} else {
    echo "<p>Failed to create backup file.</p>";
}

echo "<p><a href='/'>Back to homepage</a></p>";
