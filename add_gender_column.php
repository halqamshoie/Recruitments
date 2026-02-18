<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Models/Database.php';

try {
    $pdo = Database::connect();
    
    // Check if column exists
    $stmt = $pdo->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (!in_array('gender', $columns)) {
        echo "Adding gender column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN gender TEXT DEFAULT NULL");
        echo "Column 'gender' added successfully.\n";
    } else {
        echo "Column 'gender' already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
