<?php
require_once __DIR__ . '/src/Models/Database.php';

$pdo = Database::connect();

// Users Table
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT CHECK(role IN ('applicant', 'hr', 'admin')) NOT NULL DEFAULT 'applicant',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Audit Logs Table
$pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
)");

// Jobs Table
$pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    location TEXT,
    gender TEXT,
    experience TEXT,
    languages TEXT,
    nationalities TEXT,
    opening_date DATE,
    closing_date DATE,
    type TEXT DEFAULT 'Full-time',
    status TEXT CHECK(status IN ('open', 'closed')) DEFAULT 'open',
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(created_by) REFERENCES users(id)
)");

// Applications Table
$pdo->exec("CREATE TABLE IF NOT EXISTS applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER,
    user_id INTEGER,
    phone TEXT,
    resume_path TEXT,
    status TEXT CHECK(status IN ('pending', 'reviewed', 'rejected', 'hired')) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(job_id) REFERENCES jobs(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
)");

// Seed default HR User
$hrEmail = 'hr@company.com';
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute([$hrEmail]);
if ($stmt->fetchColumn() == 0) {
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['HR Admin', $hrEmail, $password, 'hr']);
    echo "Default HR user created (hr@company.com / password123)\n";
}

echo "Database initialized successfully.\n";
