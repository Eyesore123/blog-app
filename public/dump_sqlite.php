<?php

// Simple admin token check (set ADMIN_SETUP_TOKEN in your environment)
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

$db = new PDO('sqlite:database/database.sqlite');

$dumpFile = __DIR__ . '/sqlite_dump.sql';
file_put_contents($dumpFile, "-- SQLite dump\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n");

// Get all table creation statements
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    // Get CREATE statement
    $stmt = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'");
    $createSql = $stmt->fetchColumn();

    // Convert SQLite to PostgreSQL syntax
    $createSql = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'SERIAL PRIMARY KEY', $createSql);
    $createSql = str_replace('AUTOINCREMENT', '', $createSql);
    $createSql = str_replace('INTEGER PRIMARY KEY', 'SERIAL PRIMARY KEY', $createSql);

    file_put_contents($dumpFile, "-- Table: $table\n$createSql;\n\n", FILE_APPEND);

    // Get data (optional)
    $rows = $db->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $columns = array_keys($rows[0]);
        $columnList = implode(', ', $columns);

        file_put_contents($dumpFile, "-- Data for $table\n", FILE_APPEND);
        foreach ($rows as $row) {
            $values = array_map(function($val) {
                return $val === null ? 'NULL' : "'" . str_replace("'", "''", $val) . "'";
            }, array_values($row));
            $insert = "INSERT INTO $table ($columnList) VALUES (" . implode(', ', $values) . ");\n";
            file_put_contents($dumpFile, $insert, FILE_APPEND);
        }
        file_put_contents($dumpFile, "\n", FILE_APPEND);
    }
}

echo "Dump written to sqlite_dump.sql\n";