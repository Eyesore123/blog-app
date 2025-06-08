<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Get the DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo "DATABASE_URL is not set!";
    exit(1);
}

try {
    // Connect to the database
    $pdo = new PDO($databaseUrl);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Creating sketches table</h1>";

    $sql = "
        CREATE TABLE IF NOT EXISTS sketches (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            topic VARCHAR(255),
            published BOOLEAN DEFAULT TRUE,
            image VARCHAR(255),
            tags JSONB,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        )
    ";

    $pdo->exec($sql);
    echo "<p>✅ Table 'sketches' created or already exists.</p>";
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p>Done. <a href='/'>Back to app</a></p>";