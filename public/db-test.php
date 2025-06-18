<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Print all environment variables
echo "Environment variables:\n";
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'PG') === 0 || strpos($key, 'DATABASE') === 0) {
        echo "$key: $value\n";
    }
}

// Try different methods to get environment variables
echo "\nTrying different methods to get environment variables:\n";
echo "PGHOST via getenv(): " . getenv('PGHOST') . "\n";
echo "PGHOST via _ENV: " . ($_ENV['PGHOST'] ?? 'not set') . "\n";
echo "PGHOST via _SERVER: " . ($_SERVER['PGHOST'] ?? 'not set') . "\n";
echo "PGHOST via env(): " . (function_exists('env') ? env('PGHOST') : 'env() not available') . "\n";

// Try to connect using DATABASE_URL
echo "\nTrying to connect using DATABASE_URL:\n";
$databaseUrl = getenv('DATABASE_URL');
echo "DATABASE_URL: $databaseUrl\n";

if ($databaseUrl) {
    try {
        $pdo = new PDO($databaseUrl);
        echo "Connection successful using DATABASE_URL!\n";
    } catch (PDOException $e) {
        echo "Connection failed using DATABASE_URL: " . $e->getMessage() . "\n";
    }
}

// Try to connect using parsed DATABASE_URL
echo "\nTrying to connect using parsed DATABASE_URL:\n";
if ($databaseUrl) {
    $dbParts = parse_url($databaseUrl);
    $host = $dbParts['host'] ?? '';
    $port = $dbParts['port'] ?? 5432;
    $database = ltrim($dbParts['path'] ?? '', '/');
    $username = $dbParts['user'] ?? '';
    $password = $dbParts['pass'] ?? '';
    
    echo "Parsed host: $host\n";
    echo "Parsed port: $port\n";
    echo "Parsed database: $database\n";
    echo "Parsed username: $username\n";
    
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $username, $password);
        echo "Connection successful using parsed DATABASE_URL!\n";
    } catch (PDOException $e) {
        echo "Connection failed using parsed DATABASE_URL: " . $e->getMessage() . "\n";
    }
}

// Try to connect using direct PG variables
echo "\nTrying to connect using direct PG variables:\n";
$pgHost = getenv('PGHOST');
$pgPort = getenv('PGPORT');
$pgDatabase = getenv('PGDATABASE');
$pgUser = getenv('PGUSER');
$pgPassword = getenv('PGPASSWORD');

echo "PGHOST: $pgHost\n";
echo "PGPORT: $pgPort\n";
echo "PGDATABASE: $pgDatabase\n";
echo "PGUSER: $pgUser\n";

if ($pgHost && $pgPort && $pgDatabase && $pgUser && $pgPassword) {
    try {
        $dsn = "pgsql:host=$pgHost;port=$pgPort;dbname=$pgDatabase";
        $pdo = new PDO($dsn, $pgUser, $pgPassword);
        echo "Connection successful using direct PG variables!\n";
    } catch (PDOException $e) {
        echo "Connection failed using direct PG variables: " . $e->getMessage() . "\n";
    }
}
