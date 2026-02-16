<?php
// Migration: Create settings table and seed default TinyMCE API key
require_once __DIR__ . '/../src/Models/Database.php';

try {
    $pdo = Database::connect();

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key TEXT PRIMARY KEY,
        setting_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed default TinyMCE API key
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute(['tinymce_api_key', 'mmpmpy98l71siq1c468aslbh5femwi3xy2ppk9d569li8d8f']);

    echo "Settings table created and seeded successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
