<?php
require_once __DIR__ . '/src/Models/Database.php';

$pdo = Database::connect();

echo "Checking/Creating audit_logs table...\n";

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action TEXT NOT NULL,
        details TEXT,
        ip_address TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");
    echo "Success: 'audit_logs' table holds correct schema.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
