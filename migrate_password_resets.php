<?php
require_once __DIR__ . '/src/Models/Database.php';

echo "Migrating Password Resets Table...\n";
$pdo = Database::connect();

$pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

echo "Migration complete.\n";
