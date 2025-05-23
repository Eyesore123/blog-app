<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Fix Database Schema</h1>";

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

echo "<h2>Adding Topic Column to Posts Table</h2>";
echo "<pre>";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if topic column exists
    $stmt = $pdo->query("
        SELECT column_name
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'posts'
        AND column_name = 'topic'
    ");
    
    $topicExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($topicExists) {
        echo "The 'topic' column already exists in the posts table.\n";
    } else {
        // Add the topic column to the posts table
        $pdo->exec("ALTER TABLE posts ADD COLUMN topic VARCHAR(255)");
        echo "Successfully added 'topic' column to the posts table.\n";
    }
    
    // Update existing posts to have a default topic
    $pdo->exec("UPDATE posts SET topic = 'General' WHERE topic IS NULL");
    echo "Updated existing posts to have 'General' as the default topic.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Checking for Other Missing Columns</h2>";
echo "<pre>";
try {
    // Define expected columns for each table
    $expectedColumns = [
        'posts' => [
            'id' => 'bigint',
            'user_id' => 'bigint',
            'title' => 'varchar',
            'content' => 'text',
            'topic' => 'varchar',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp'
        ],
        'comments' => [
            'id' => 'bigint',
            'user_id' => 'bigint',
            'post_id' => 'bigint',
            'content' => 'text',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp'
        ],
        'users' => [
            'id' => 'bigint',
            'name' => 'varchar',
            'email' => 'varchar',
            'email_verified_at' => 'timestamp',
            'password' => 'varchar',
            'remember_token' => 'varchar',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp'
        ]
    ];
    
    // Check each table
    foreach ($expectedColumns as $table => $columns) {
        echo "Checking $table table:\n";
        
        // Get existing columns
        $stmt = $pdo->query("
            SELECT column_name, data_type
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = '$table'
        ");
        
        $existingColumns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[$row['column_name']] = $row['data_type'];
        }
        
        // Check for missing columns
        $missingColumns = [];
        foreach ($columns as $column => $type) {
            if (!isset($existingColumns[$column])) {
                $missingColumns[$column] = $type;
            }
        }
        
        if (empty($missingColumns)) {
            echo "- All expected columns exist.\n";
        } else {
            echo "- Missing columns:\n";
            foreach ($missingColumns as $column => $type) {
                echo "  - $column ($type)\n";
                
                // Add the missing column
                try {
                    $sqlType = $type;
                    if ($type === 'varchar') {
                        $sqlType = 'varchar(255)';
                    } elseif ($type === 'timestamp') {
                        $sqlType = 'timestamp(0) without time zone';
                    }
                    
                    $pdo->exec("ALTER TABLE $table ADD COLUMN $column $sqlType");
                    echo "    - Added $column column to $table table.\n";
                } catch (PDOException $e) {
                    echo "    - Error adding column: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
