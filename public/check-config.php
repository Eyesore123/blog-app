<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Configuration Check</h1>";

// Define the base storage path
$basePath = __DIR__ . '/../storage';

// Check storage directories
echo "<h2>Storage Directories</h2>";
echo "<pre>";
$storagePaths = [
    'app' => $basePath . '/app',
    'framework' => $basePath . '/framework',
    'framework/cache' => $basePath . '/framework/cache',
    'framework/cache/data' => $basePath . '/framework/cache/data',
    'framework/sessions' => $basePath . '/framework/sessions',
    'framework/views' => $basePath . '/framework/views',
    'logs' => $basePath . '/logs',
];

foreach ($storagePaths as $name => $path) {
    echo "$name:\n";
    echo "  Path: $path\n";
    echo "  Exists: " . (is_dir($path) ? 'Yes' : 'No') . "\n";
    echo "  Writable: " . (is_writable($path) ? 'Yes' : 'No') . "\n";
}
echo "</pre>";

// Check environment variables
echo "<h2>Environment Variables</h2>";
echo "<pre>";
$env_vars = [];
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'DB_') === 0 || strpos($key, 'DATABASE') === 0 || 
        strpos($key, 'CACHE') === 0 || strpos($key, 'APP_') === 0) {
        // Mask passwords and sensitive information
        if (strpos($key, 'PASSWORD') !== false || strpos($key, 'URL') !== false || 
            strpos($key, 'KEY') !== false || strpos($key, 'SECRET') !== false) {
            $env_vars[$key] = '******';
        } else {
            $env_vars[$key] = $value;
        }
    }
}
print_r($env_vars);
echo "</pre>";

// Check database connection
echo "<h2>Database Connection Test</h2>";
echo "<pre>";

// Get the DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo "DATABASE_URL is not set!\n";
} else {
    // Parse the DATABASE_URL
    $dbParts = parse_url($databaseUrl);
    $host = $dbParts['host'] ?? '';
    $port = $dbParts['port'] ?? 5432;
    $database = ltrim($dbParts['path'] ?? '', '/');
    $username = $dbParts['user'] ?? '';
    $password = $dbParts['pass'] ?? '';
    
    echo "Host: $host\n";
    echo "Port: $port\n";
    echo "Database: $database\n";
    echo "Username: $username\n";
    echo "Password: " . (empty($password) ? 'not set' : '******') . "\n\n";
    
    // Try to connect using PDO
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "PDO Connection: Success\n";
        
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
            }
        }
        
    } catch (PDOException $e) {
        echo "PDO Connection Error: " . $e->getMessage() . "\n";
    }
    
    // Try to connect using pg_connect
    if (function_exists('pg_connect')) {
        try {
            $connectionString = "host=$host port=$port dbname=$database user=$username password=$password";
            $conn = pg_connect($connectionString);
            
            if ($conn) {
                echo "\npg_connect Connection: Success\n";
                
                // Get PostgreSQL version
                $result = pg_query($conn, "SELECT version()");
                $version = pg_fetch_result($result, 0, 0);
                echo "PostgreSQL Version: $version\n";
                
                // Close connection
                pg_close($conn);
            } else {
                echo "\npg_connect Connection Error: " . pg_last_error() . "\n";
            }
        } catch (Exception $e) {
            echo "\npg_connect Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\npg_connect function does not exist. PostgreSQL extension is not installed.\n";
    }
}
echo "</pre>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
