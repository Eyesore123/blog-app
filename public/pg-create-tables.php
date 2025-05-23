<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

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
    
    echo "<h1>Creating Tables</h1>";
    
    // Create users table
    $result = pg_query($conn, "
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            email_verified_at TIMESTAMP NULL,
            password VARCHAR(255) NOT NULL,
            is_admin BOOLEAN NOT NULL DEFAULT FALSE,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )
    ");
    
    if (!$result) {
        echo "<p>Error creating users table: " . pg_last_error($conn) . "</p>";
    } else {
        echo "<p>Users table created successfully.</p>";
    }
    
    // Create password_reset_tokens table
    $result = pg_query($conn, "
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            email VARCHAR(255) PRIMARY KEY,
            token VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NULL
        )
    ");
    
    if (!$result) {
        echo "<p>Error creating password_reset_tokens table: " . pg_last_error($conn) . "</p>";
    } else {
        echo "<p>Password reset tokens table created successfully.</p>";
    }
    
    // Create migrations table
    $result = pg_query($conn, "
        CREATE TABLE IF NOT EXISTS migrations (
            id SERIAL PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INTEGER NOT NULL
        )
    ");
    
    if (!$result) {
        echo "<p>Error creating migrations table: " . pg_last_error($conn) . "</p>";
    } else {
        echo "<p>Migrations table created successfully.</p>";
    }
    
    // Create personal_access_tokens table
    $result = pg_query($conn, "
        CREATE TABLE IF NOT EXISTS personal_access_tokens (
            id SERIAL PRIMARY KEY,
            tokenable_type VARCHAR(255) NOT NULL,
            tokenable_id BIGINT NOT NULL,
            name VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            abilities TEXT NULL,
            last_used_at TIMESTAMP NULL,
            expires_at TIMESTAMP NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )
    ");
    
    if (!$result) {
        echo "<p>Error creating personal_access_tokens table: " . pg_last_error($conn) . "</p>";
    } else {
        echo "<p>Personal access tokens table created successfully.</p>";
    }
    
    // Create failed_jobs table
    $result = pg_query($conn, "
        CREATE TABLE IF NOT EXISTS failed_jobs (
            id SERIAL PRIMARY KEY,
            uuid VARCHAR(255) NOT NULL UNIQUE,
            connection TEXT NOT NULL,
            queue TEXT NOT NULL,
            payload TEXT NOT NULL,
            exception TEXT NOT NULL,
            failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    if (!$result) {
        echo "<p>Error creating failed_jobs table: " . pg_last_error($conn) . "</p>";
    } else {
        echo "<p>Failed jobs table created successfully.</p>";
    }
    
    // Create posts table
    $result = pg_query($conn, "
        CREATE TABLE IF NOT EXISTS posts (
            id SERIAL PRIMARY KEY,
            user_id BIGINT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    if (!$result) {
        echo "<p>Error creating posts table: " . pg_last_error($conn) . "</p>";
    } else {
        echo "<p>Posts table created successfully.</p>";
    }
    
    // Create comments table
    $result = pg_query($conn, "
        CREATE TABLE IF NOT EXISTS comments (
            id SERIAL PRIMARY KEY,
            post_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    if (!$result) {
        echo "<p>Error creating comments table: " . pg_last_error($conn) . "</p>";
    } else {
        echo "<p>Comments table created successfully.</p>";
    }
    
    // Close connection
    pg_close($conn);
    
    echo "<h2>All tables created successfully!</h2>";
    echo "<p><a href='pg-test.php'>Check Tables</a></p>";
    
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
