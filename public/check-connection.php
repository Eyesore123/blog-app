<?php

use Illuminate\Support\Facades\DB;

echo "<h1>Database Connection Check</h1>";

// Get the current database connection
echo "<h2>Current Database Connection</h2>";
echo "<pre>";
try {
    $connection = config('database.default');
    echo "Default connection: " . $connection . "\n\n";
    
    $connections = config('database.connections');
    echo "Configured connections:\n";
    print_r($connections);
    
    // Try to connect to the database
    echo "\nTesting connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "Connected successfully to: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
    
    // Get the database driver
    echo "Database driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    
    // Get the server version
    echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Test a simple query
    echo "\nTesting query...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "Number of users: " . $count . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Get environment variables
    echo "\nEnvironment variables:\n";
    $env_vars = [];
    foreach ($_ENV as $key => $value) {
        if (strpos($key, 'DB_') === 0 || strpos($key, 'DATABASE') === 0) {
            // Mask passwords and sensitive information
            if (strpos($key, 'PASSWORD') !== false || strpos($key, 'URL') !== false) {
                $env_vars[$key] = '******';
            } else {
                $env_vars[$key] = $value;
            }
        }
    }
    print_r($env_vars);
}
echo "</pre>";
