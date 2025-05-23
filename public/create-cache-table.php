<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Create Cache Table</h1>";

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

echo "<h2>Creating Cache Table</h2>";
echo "<pre>";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the cache table already exists
    $stmt = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public'
            AND table_name = 'cache'
        )
    ");
    
    $tableExists = $stmt->fetchColumn();
    
    if ($tableExists) {
        echo "The 'cache' table already exists.\n";
    } else {
        // Create the cache table
        $pdo->exec("
            CREATE TABLE cache (
                key VARCHAR(255) NOT NULL,
                value TEXT NOT NULL,
                expiration INTEGER NOT NULL,
                PRIMARY KEY (key)
            )
        ");
        echo "Successfully created the 'cache' table.\n";
    }
    
    // Check if the cache_locks table already exists
    $stmt = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public'
            AND table_name = 'cache_locks'
        )
    ");
    
    $tableExists = $stmt->fetchColumn();
    
    if ($tableExists) {
        echo "The 'cache_locks' table already exists.\n";
    } else {
        // Create the cache_locks table
        $pdo->exec("
            CREATE TABLE cache_locks (
                key VARCHAR(255) NOT NULL,
                owner VARCHAR(255) NOT NULL,
                expiration INTEGER NOT NULL,
                PRIMARY KEY (key)
            )
        ");
        echo "Successfully created the 'cache_locks' table.\n";
    }
    
    // Check if the sessions table already exists
    $stmt = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public'
            AND table_name = 'sessions'
        )
    ");
    
    $tableExists = $stmt->fetchColumn();
    
    if ($tableExists) {
        echo "The 'sessions' table already exists.\n";
    } else {
        // Create the sessions table
        $pdo->exec("
            CREATE TABLE sessions (
                id VARCHAR(255) NOT NULL,
                user_id BIGINT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                payload TEXT NOT NULL,
                last_activity INTEGER NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        echo "Successfully created the 'sessions' table.\n";
    }
    
    // Check if the jobs table already exists
    $stmt = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public'
            AND table_name = 'jobs'
        )
    ");
    
    $tableExists = $stmt->fetchColumn();
    
    if ($tableExists) {
        echo "The 'jobs' table already exists.\n";
    } else {
        // Create the jobs table
        $pdo->exec("
            CREATE TABLE jobs (
                id BIGSERIAL PRIMARY KEY,
                queue VARCHAR(255) NOT NULL,
                payload TEXT NOT NULL,
                attempts SMALLINT NOT NULL,
                reserved_at INTEGER NULL,
                available_at INTEGER NOT NULL,
                created_at INTEGER NOT NULL
            )
        ");
        $pdo->exec("CREATE INDEX jobs_queue_index ON jobs (queue)");
        echo "Successfully created the 'jobs' table.\n";
    }
    
    // Check if the job_batches table already exists
    $stmt = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public'
            AND table_name = 'job_batches'
        )
    ");
    
    $tableExists = $stmt->fetchColumn();
    
    if ($tableExists) {
        echo "The 'job_batches' table already exists.\n";
    } else {
        // Create the job_batches table
        $pdo->exec("
            CREATE TABLE job_batches (
                id VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                total_jobs INTEGER NOT NULL,
                pending_jobs INTEGER NOT NULL,
                failed_jobs INTEGER NOT NULL,
                failed_job_ids TEXT NOT NULL,
                options TEXT NULL,
                cancelled_at INTEGER NULL,
                created_at INTEGER NOT NULL,
                finished_at INTEGER NULL,
                PRIMARY KEY (id)
            )
        ");
        echo "Successfully created the 'job_batches' table.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Update Cache Configuration</h2>";
echo "<pre>";
try {
    // Update the cache configuration to use file driver instead of database
    $cachePath = __DIR__ . '/../config/cache.php';
    $cacheBackupPath = $cachePath . '.backup-' . date('Y-m-d-H-i-s');
    
    // Backup the original file
    if (file_exists($cachePath)) {
        copy($cachePath, $cacheBackupPath);
        echo "Original cache.php backed up to: $cacheBackupPath\n";
    }
    
    // Read the current content
    $cacheContent = file_get_contents($cachePath);
    
    // Update the default cache store to file
    $cacheContent = preg_replace(
        "/'default' => env\('CACHE_DRIVER', '.*?'\),/",
        "'default' => env('CACHE_DRIVER', 'file'),",
        $cacheContent
    );
    
    // Write the updated file
    if (file_put_contents($cachePath, $cacheContent)) {
        echo "cache.php updated to use file driver by default.\n";
    } else {
        echo "Failed to update cache.php. Check permissions.\n";
    }
    
    // Update the session configuration to use file driver instead of database
    $sessionPath = __DIR__ . '/../config/session.php';
    $sessionBackupPath = $sessionPath . '.backup-' . date('Y-m-d-H-i-s');
    
    // Backup the original file
    if (file_exists($sessionPath)) {
        copy($sessionPath, $sessionBackupPath);
        echo "Original session.php backed up to: $sessionBackupPath\n";
    }
    
    // Read the current content
    $sessionContent = file_get_contents($sessionPath);
    
    // Update the default session driver to file
    $sessionContent = preg_replace(
        "/'driver' => env\('SESSION_DRIVER', '.*?'\),/",
        "'driver' => env('SESSION_DRIVER', 'file'),",
        $sessionContent
    );
    
    // Write the updated file
    if (file_put_contents($sessionPath, $sessionContent)) {
        echo "session.php updated to use file driver by default.\n";
    } else {
        echo "Failed to update session.php. Check permissions.\n";
    }
    
    // Update the .env file to use file drivers
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        
        // Update CACHE_DRIVER
        $envContent = preg_replace('/^CACHE_DRIVER=.*$/m', 'CACHE_DRIVER=file', $envContent);
        
        // Update SESSION_DRIVER
        $envContent = preg_replace('/^SESSION_DRIVER=.*$/m', 'SESSION_DRIVER=file', $envContent);
        
        // Write the updated .env file
        if (file_put_contents($envPath, $envContent)) {
            echo ".env file updated to use file drivers.\n";
        } else {
            echo "Failed to update .env file. Check permissions.\n";
        }
    }
    
    // Update environment variables in memory
    putenv('CACHE_DRIVER=file');
    putenv('SESSION_DRIVER=file');
    
    echo "Environment variables updated in memory.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Clear Cache</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan cache:clear', $output, $return_var);
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
