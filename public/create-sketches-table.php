<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
if (($_GET['token'] ?? '') !== $validToken) {
    http_response_code(404);
    exit;
}

echo "<h1>Creating sketches table</h1>";

try {
    $dsn = getenv('DB_CONNECTION') === 'pgsql'
        ? 'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE')
        : '';
    $pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));

    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS sketches (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    topic VARCHAR(255),
    published BOOLEAN DEFAULT TRUE,
    image VARCHAR(255),
    tags JSONB,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
SQL;

    $pdo->exec($sql);
    echo "<p>✅ Table 'sketches' created or already exists.</p>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p>Done. <a href='/'>Back to app</a></p>";