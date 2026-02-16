<?php
require_once __DIR__ . '/src/Models/Database.php';

$pdo = Database::connect();

echo "Migrating database (Date Fields)...\n";

$columns = [
    'opening_date' => 'DATE',
    'closing_date' => 'DATE'
];

foreach ($columns as $col => $type) {
    try {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN $col $type");
        echo "Added column '$col' to 'jobs' table.\n";
    } catch (PDOException $e) {
        echo "Column '$col' might already exist or error: " . $e->getMessage() . "\n";
    }
}

echo "Migration complete.\n";
