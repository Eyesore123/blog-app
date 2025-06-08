<?php
$db = new PDO('sqlite:database/database.sqlite');

$sql = "-- SQLite dump\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

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
    
    $sql .= "-- Table: $table\n";
    $sql .= $createSql . ";\n\n";
    
    // Get data (optional)
    $rows = $db->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $columns = array_keys($rows[0]);
        $columnList = implode(', ', $columns);
        
        $sql .= "-- Data for $table\n";
        foreach ($rows as $row) {
            $values = array_map(function($val) {
                return $val === null ? 'NULL' : "'" . str_replace("'", "''", $val) . "'";
            }, array_values($row));
            
            $sql .= "INSERT INTO $table ($columnList) VALUES (" . implode(', ', $values) . ");\n";
        }
        $sql .= "\n";
    }
}

// Write to file
file_put_contents('sqlite_dump.sql', $sql);
echo "Dump written to sqlite_dump.sql\n";
?>