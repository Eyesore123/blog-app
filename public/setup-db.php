<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Simple script to set up the database without using Laravel's Artisan

try {
    // Get database connection details from environment variables
    $host = getenv('PGHOST');
    $port = intval(getenv('PGPORT'));
    $database = getenv('PGDATABASE');
    $username = getenv('PGUSER');
    $password = getenv('PGPASSWORD');
    
    echo "Connecting to PostgreSQL database...\n";
    echo "Host: $host\n";
    echo "Port: $port\n";
    echo "Database: $database\n";
    echo "Username: $username\n";
    
    // Connect to the database
    $dsn = "pgsql:host=$host;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully!\n";
    
    // Check if migrations table exists
    $stmt = $pdo->query("SELECT EXISTS (
        SELECT 1 FROM pg_class c, pg_namespace n 
        WHERE n.nspname = current_schema() 
        AND c.relname = 'migrations' 
        AND c.relkind in ('r', 'p') 
        AND n.oid = c.relnamespace
    )");
    
    $migrationsTableExists = $stmt->fetchColumn();
    
    if (!$migrationsTableExists) {
        echo "Creating migrations table...\n";
        
        // Create migrations table
        $pdo->exec("CREATE TABLE migrations (
            id SERIAL PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INTEGER NOT NULL
        )");
        
        echo "Migrations table created successfully!\n";
    } else {
        echo "Migrations table already exists.\n";
    }
    
    echo "Database setup completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
