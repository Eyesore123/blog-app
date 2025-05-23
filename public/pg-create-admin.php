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
    
    echo "<h1>Creating Admin User</h1>";
    
    // Check if admin user already exists
    $email = 'admin@example.com'; // Change this to your email
    $result = pg_query_params($conn, "SELECT COUNT(*) FROM users WHERE email = $1", [$email]);
    
    if (!$result) {
        echo "<p>Error checking for admin user: " . pg_last_error($conn) . "</p>";
        exit;
    }
    
    $adminExists = (int)pg_fetch_result($result, 0, 0) > 0;
    
    if ($adminExists) {
        echo "<p>Admin user with email $email already exists.</p>";
    } else {
        // Generate a secure random password
        $password = bin2hex(random_bytes(8)); // 16 character random password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $now = date('Y-m-d H:i:s');
        
        // Create admin user
        $result = pg_query_params($conn, "
            INSERT INTO users (name, email, password, is_admin, email_verified_at, created_at, updated_at) 
            VALUES ($1, $2, $3, $4, $5, $6, $7)
        ", [
            'Admin User',
            $email,
            $hashedPassword,
            't', // PostgreSQL boolean true
            $now,
            $now,
            $now
        ]);
        
        if (!$result) {
            echo "<p>Error creating admin user: " . pg_last_error($conn) . "</p>";
            exit;
        }
        
        echo "<p>Admin user created successfully!</p>";
        echo "<p>Email: $email</p>";
        echo "<p>Password: $password</p>";
        echo "<p><strong>Save this password! It won't be shown again.</strong></p>";
    }
    
    // Close connection
    pg_close($conn);
    
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
