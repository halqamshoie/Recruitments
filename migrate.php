<?php
require_once __DIR__ . '/src/Models/Database.php';

$pdo = Database::connect();

echo "Migrating database...\n";

// Add new columns to jobs table
$columns = [
    'gender' => 'TEXT',
    'experience' => 'TEXT',
    'languages' => 'TEXT',
    'nationalities' => 'TEXT'
];

foreach ($columns as $col => $type) {
    try {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN $col $type");
        echo "Added column '$col' to 'jobs' table.\n";
    } catch (PDOException $e) {
        // Ignore if column already exists (simplistic check)
        echo "Column '$col' might already exist or error: " . $e->getMessage() . "\n";
    }
}

// Add phone to applications table
try {
    $pdo->exec("ALTER TABLE applications ADD COLUMN phone TEXT");
    echo "Added column 'phone' to 'applications' table.\n";
} catch (PDOException $e) {
    echo "Column 'phone' might already exist or error: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
