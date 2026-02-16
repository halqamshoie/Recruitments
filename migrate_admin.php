<?php
require_once __DIR__ . '/src/Models/Database.php';

$pdo = Database::connect();

echo "Starting Admin & Audit Migration...\n";

// 1. Create audit_logs table
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
    echo "Created 'audit_logs' table.\n";
} catch (PDOException $e) {
    echo "Error creating audit_logs: " . $e->getMessage() . "\n";
}

// 2. Update users table to allow 'admin' role
// SQLite CHECK constraints cannot be altered easily. We must recreate the table.
echo "Migrating 'users' table to support 'admin' role...\n";

$pdo->beginTransaction();

try {
    // Rename existing table
    $pdo->exec("ALTER TABLE users RENAME TO users_old");

    // Create new table with updated CHECK constraint
    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT CHECK(role IN ('applicant', 'hr', 'admin')) NOT NULL DEFAULT 'applicant',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Copy data back
    $pdo->exec("INSERT INTO users (id, name, email, password, role, created_at)
                SELECT id, name, email, password, role, created_at FROM users_old");

    // Drop old table
    $pdo->exec("DROP TABLE users_old");

    // Create default admin user
    $adminName = 'System Admin';
    $adminEmail = 'admin@cccrc.gov.om';
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $adminRole = 'admin';

    // Check if admin already exists (by email) to avoid duplicate error if re-run
    // But since we just recreated table, we can just insert distinct or ignore.
    // However, unique constraint on email handles it.

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$adminName, $adminEmail, $adminPass, $adminRole]);
    echo "Created default admin user: $adminEmail / admin123\n";

    $pdo->commit();
    echo "Users table migration complete.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Migration failed: " . $e->getMessage() . "\n";
    // Attempt to restore if possible, but for dev env we might just need to fix script
    // If users_old exists and users doesn't, we might be in trouble, but the transaction should handle it.
}
