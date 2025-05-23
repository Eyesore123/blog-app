<?php
echo "<h1>Database Schema Check</h1>";

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

echo "<h2>Posts Table Schema</h2>";
echo "<pre>";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get column information for the posts table
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'posts'
        ORDER BY ordinal_position
    ");
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "No columns found in the posts table.\n";
    } else {
        echo "Columns in the posts table:\n";
        foreach ($columns as $column) {
            echo "- {$column['column_name']} ({$column['data_type']})";
            echo $column['is_nullable'] === 'YES' ? ' NULL' : ' NOT NULL';
            if ($column['column_default'] !== null) {
                echo " DEFAULT {$column['column_default']}";
            }
            echo "\n";
        }
    }
    
    // Check if topic column exists
    $topicExists = false;
    foreach ($columns as $column) {
        if ($column['column_name'] === 'topic') {
            $topicExists = true;
            break;
        }
    }
    
    if (!$topicExists) {
        echo "\nThe 'topic' column does not exist in the posts table.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Migration History</h2>";
echo "<pre>";
try {
    // Check the migrations table
    $stmt = $pdo->query("
        SELECT id, migration, batch
        FROM migrations
        ORDER BY batch ASC, id ASC
    ");
    
    $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($migrations)) {
        echo "No migrations found.\n";
    } else {
        echo "Applied migrations:\n";
        foreach ($migrations as $migration) {
            echo "- {$migration['migration']} (Batch: {$migration['batch']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Add Topic Column</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='add_topic'>";
echo "<button type='submit'>Add Topic Column to Posts Table</button>";
echo "</form>";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_topic') {
    echo "<h3>Adding Topic Column</h3>";
    echo "<pre>";
    try {
        // Add the topic column to the posts table
        $pdo->exec("ALTER TABLE posts ADD COLUMN topic VARCHAR(255)");
        echo "Successfully added 'topic' column to the posts table.\n";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "</pre>";
}

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
