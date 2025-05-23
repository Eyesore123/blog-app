<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Fix Users Table</h1>";

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

echo "<h2>Checking Users Table Structure</h2>";
echo "<pre>";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connection: Success\n";
    
    // Check if the users table exists
    $stmt = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public'
            AND table_name = 'users'
        )
    ");
    
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        echo "The 'users' table does not exist. Please run migrations first.\n";
    } else {
        echo "The 'users' table exists.\n";
        
        // Check if the is_active column exists
        $stmt = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.columns 
                WHERE table_schema = 'public'
                AND table_name = 'users'
                AND column_name = 'is_active'
            )
        ");
        
        $columnExists = $stmt->fetchColumn();
        
        if ($columnExists) {
            echo "The 'is_active' column already exists in the 'users' table.\n";
        } else {
            echo "The 'is_active' column does not exist in the 'users' table.\n";
            
            // Add the is_active column
            $pdo->exec("
                ALTER TABLE users
                ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE
            ");
            
            echo "Successfully added the 'is_active' column to the 'users' table.\n";
        }
        
        // Show the current structure of the users table
        $stmt = $pdo->query("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_schema = 'public'
            AND table_name = 'users'
            ORDER BY ordinal_position
        ");
        
        echo "\nCurrent structure of the 'users' table:\n";
        echo "Column Name | Data Type | Nullable | Default\n";
        echo "-----------------------------------------------\n";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['column_name']} | {$row['data_type']} | {$row['is_nullable']} | {$row['column_default']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Checking for Other Missing Columns</h2>";
echo "<pre>";
try {
    // Check for other potentially missing columns based on common Laravel user models
    $potentialColumns = [
        'email_verified_at' => 'TIMESTAMP NULL DEFAULT NULL',
        'remember_token' => 'VARCHAR(100) NULL DEFAULT NULL',
        'two_factor_secret' => 'TEXT NULL DEFAULT NULL',
        'two_factor_recovery_codes' => 'TEXT NULL DEFAULT NULL',
        'two_factor_confirmed_at' => 'TIMESTAMP NULL DEFAULT NULL',
        'profile_photo_path' => 'VARCHAR(2048) NULL DEFAULT NULL',
        'current_team_id' => 'BIGINT NULL DEFAULT NULL',
        'role' => 'VARCHAR(255) NOT NULL DEFAULT \'user\'',
        'is_admin' => 'BOOLEAN NOT NULL DEFAULT FALSE'
    ];
    
    foreach ($potentialColumns as $column => $definition) {
        $stmt = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.columns 
                WHERE table_schema = 'public'
                AND table_name = 'users'
                AND column_name = '$column'
            )
        ");
        
        $columnExists = $stmt->fetchColumn();
        
        if ($columnExists) {
            echo "The '$column' column already exists in the 'users' table.\n";
        } else {
            echo "The '$column' column does not exist in the 'users' table.\n";
            
            // Add the column
            $pdo->exec("
                ALTER TABLE users
                ADD COLUMN $column $definition
            ");
            
            echo "Successfully added the '$column' column to the 'users' table.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Checking for Missing Indexes</h2>";
echo "<pre>";
try {
    // Check for email index
    $stmt = $pdo->query("
        SELECT EXISTS (
            SELECT FROM pg_indexes
            WHERE schemaname = 'public'
            AND tablename = 'users'
            AND indexname = 'users_email_unique'
        )
    ");
    
    $indexExists = $stmt->fetchColumn();
    
    if ($indexExists) {
        echo "The 'users_email_unique' index already exists.\n";
    } else {
        echo "The 'users_email_unique' index does not exist.\n";
        
        // Add the email unique index
        $pdo->exec("
            CREATE UNIQUE INDEX users_email_unique ON users (email)
        ");
        
        echo "Successfully added the 'users_email_unique' index.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Checking User Data</h2>";
echo "<pre>";
try {
    // Check if there are any users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    
    echo "Number of users in the database: $userCount\n";
    
    if ($userCount > 0) {
        // Show the first few users
        $stmt = $pdo->query("
            SELECT id, name, email, is_active
            FROM users
            ORDER BY id
            LIMIT 5
        ");
        
        echo "\nSample users:\n";
        echo "ID | Name | Email | Is Active\n";
        echo "-----------------------------------------------\n";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $isActive = $row['is_active'] ? 'Yes' : 'No';
            echo "{$row['id']} | {$row['name']} | {$row['email']} | {$isActive}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Clear Cache</h2>";
echo "<pre>";
$output = [];
$return_var = 0;
exec('cd ' . __DIR__ . '/../ && php artisan config:clear', $output, $return_var);
print_r($output);
echo "</pre>";
echo "<p>Return code: $return_var</p>";

echo "<p>Done. <a href='/admin'>Go to admin dashboard</a></p>";
