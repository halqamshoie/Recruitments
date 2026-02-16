<?php
require_once __DIR__ . '/public/index.php';

try {
    $pdo = Database::connect();

    // Check if column exists
    $cols = $pdo->query("PRAGMA table_info(applications)")->fetchAll(PDO::FETCH_ASSOC);
    $hasCol = false;
    foreach ($cols as $col) {
        if ($col['name'] === 'qualification_files') {
            $hasCol = true;
            break;
        }
    }

    if (!$hasCol) {
        $pdo->exec("ALTER TABLE applications ADD COLUMN qualification_files TEXT");
        echo "Added 'qualification_files' column to 'applications' table.\n";
    } else {
        echo "Column 'qualification_files' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
