<?php
require_once __DIR__ . '/public/index.php'; // Load DB connection

try {
    $pdo = Database::connect();

    // Add columns if they don't exist. SQLite doesn't support IF NOT EXISTS in ADD COLUMN, so we wrap in try-catch or check first.
    // Simplifying by attempting to add each.

    $alterCommands = [
        "ALTER TABLE users ADD COLUMN phone TEXT",
        "ALTER TABLE users ADD COLUMN avatar TEXT",
        "ALTER TABLE users ADD COLUMN title TEXT",
        "ALTER TABLE users ADD COLUMN bio TEXT"
    ];

    foreach ($alterCommands as $cmd) {
        try {
            $pdo->exec($cmd);
            echo "Executed: $cmd\n";
        } catch (PDOException $e) {
            // Ignore error if column likely exists (duplicate column name)
            echo "Skipped (or error): $cmd - " . $e->getMessage() . "\n";
        }
    }

    echo "Migration completed.\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
