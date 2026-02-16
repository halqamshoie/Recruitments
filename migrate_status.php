<?php
require_once __DIR__ . '/src/Models/Database.php';

echo "Migrating Applications Table Status Constraint...\n";
$pdo = Database::connect();

try {
    $pdo->beginTransaction();

    // 1. Create new table with updated CHECK constraint
    $pdo->exec("CREATE TABLE applications_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        job_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        resume_path TEXT,
        phone TEXT,
        status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'reviewed', 'rejected', 'shortlisted')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // 2. Copy data, mapping 'hired' to 'shortlisted'
    // Note: older schemas might not have 'phone', checking if it exists or we just select relevant cols.
    // Based on previous code, 'phone' exists.
    $pdo->exec("INSERT INTO applications_new (id, job_id, user_id, resume_path, phone, status, created_at)
                SELECT id, job_id, user_id, resume_path, phone, 
                CASE WHEN status = 'hired' THEN 'shortlisted' ELSE status END, 
                created_at FROM applications");

    // 3. Drop old table
    $pdo->exec("DROP TABLE applications");

    // 4. Rename new table
    $pdo->exec("ALTER TABLE applications_new RENAME TO applications");

    $pdo->commit();
    echo "Migration complete. Constraint updated.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Migration failed: " . $e->getMessage() . "\n";
}
