<?php
echo "<h1>Direct Database Connection Test</h1>";

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

echo "<h2>Connection Information</h2>";
echo "<pre>";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? 'not set' : '******') . "\n";
echo "</pre>";

echo "<h2>PDO Connection Test</h2>";
echo "<pre>";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connection: Success\n";
    
    // Get the PostgreSQL version
    $stmt = $pdo->query('SELECT version()');
    $version = $stmt->fetchColumn();
    echo "PostgreSQL Version: $version\n\n";
    
    // List tables
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public'
        ORDER BY table_name
    ");
    
    echo "Tables:\n";
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "No tables found.\n";
    } else {
        foreach ($tables as $table) {
            echo "- $table\n";
            
            // Get column information for each table
            $stmt = $pdo->query("
                SELECT column_name, data_type 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND table_name = '$table'
                ORDER BY ordinal_position
            ");
            
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($columns)) {
                echo "  Columns:\n";
                foreach ($columns as $column) {
                    echo "    {$column['column_name']} ({$column['data_type']})\n";
                }
            }
            
            // Count rows in the table
            $stmt = $pdo->query("SELECT COUNT(*) FROM \"$table\"");
            $count = $stmt->fetchColumn();
            echo "  Row count: $count\n\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>pg_connect Test</h2>";
echo "<pre>";
if (function_exists('pg_connect')) {
    try {
        $connectionString = "host=$host port=$port dbname=$database user=$username password=$password";
        $conn = pg_connect($connectionString);
        
        if ($conn) {
            echo "Connection: Success\n";
            
            // Get PostgreSQL version
            $result = pg_query($conn, "SELECT version()");
            $version = pg_fetch_result($result, 0, 0);
            echo "PostgreSQL Version: $version\n\n";
            
            // List tables
            $result = pg_query($conn, "
                SELECT table_name 
                FROM information_schema.tables 
                WHERE table_schema = 'public'
                ORDER BY table_name
            ");
            
            echo "Tables:\n";
            
            if (pg_num_rows($result) == 0) {
                echo "No tables found.\n";
            } else {
                while ($row = pg_fetch_row($result)) {
                    $table = $row[0];
                    echo "- $table\n";
                    
                    // Get column information for each table
                    $colResult = pg_query($conn, "
                        SELECT column_name, data_type 
                        FROM information_schema.columns 
                        WHERE table_schema = 'public' 
                        AND table_name = '$table'
                        ORDER BY ordinal_position
                    ");
                    
                    if (pg_num_rows($colResult) > 0) {
                        echo "  Columns:\n";
                        while ($colRow = pg_fetch_assoc($colResult)) {
                            echo "    {$colRow['column_name']} ({$colRow['data_type']})\n";
                        }
                    }
                    
                    // Count rows in the table
                    $countResult = pg_query($conn, "SELECT COUNT(*) FROM \"$table\"");
                    $count = pg_fetch_result($countResult, 0, 0);
                    echo "  Row count: $count\n\n";
                }
            }
            
            // Close connection
            pg_close($conn);
        } else {
            echo "Connection Error: " . pg_last_error() . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "pg_connect function does not exist. PostgreSQL extension is not installed.\n";
}
echo "</pre>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
