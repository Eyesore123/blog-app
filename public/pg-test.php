<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>PostgreSQL Test</h1>";

// Check if pg_connect exists
if (!function_exists('pg_connect')) {
    echo "<p>pg_connect function does not exist. PostgreSQL extension is not installed.</p>";
    exit;
}

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

// Create connection string
$connectionString = "host=$host port=$port dbname=$database user=$username password=$password";

try {
    // Connect to PostgreSQL
    $conn = pg_connect($connectionString);
    
    if (!$conn) {
        echo "<p>Failed to connect to PostgreSQL: " . pg_last_error() . "</p>";
        exit;
    }
    
    echo "<p>Connected to PostgreSQL successfully!</p>";
    
    // Get PostgreSQL version
    $result = pg_query($conn, "SELECT version()");
    $version = pg_fetch_result($result, 0, 0);
    echo "<p>PostgreSQL version: " . htmlspecialchars($version) . "</p>";
    
    // List tables
    $result = pg_query($conn, "
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public'
        ORDER BY table_name
    ");
    
    echo "<h2>Tables</h2>";
    
    if (pg_num_rows($result) == 0) {
        echo "<p>No tables found in the database.</p>";
    } else {
        echo "<ul>";
        while ($row = pg_fetch_row($result)) {
            echo "<li>" . htmlspecialchars($row[0]) . "</li>";
        }
        echo "</ul>";
    }
    
    // Close connection
    pg_close($conn);
    
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
