<?php
require_once __DIR__ . '/src/Models/Database.php';

try {
    $pdo = Database::connect();
    
    // Add columns if they don't exist
    // SQLite doesn't support IF NOT EXISTS in ALTER TABLE perfectly in all versions, 
    // but duplicate column error is easy to catch/ignore.
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN nationality TEXT");
        echo "Added 'nationality' column.\n";
    } catch (PDOException $e) {
        echo "Column 'nationality' might already exist or error: " . $e->getMessage() . "\n";
    }

    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN place_of_work TEXT");
        echo "Added 'place_of_work' column.\n";
    } catch (PDOException $e) {
        echo "Column 'place_of_work' might already exist or error: " . $e->getMessage() . "\n";
    }

    echo "Migration completed.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
