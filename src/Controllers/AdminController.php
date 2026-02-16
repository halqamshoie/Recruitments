<?php
require_once __DIR__ . '/../Models/Database.php';

class AdminController
{

    private function checkAdmin()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }
    }

    public function dashboard()
    {
        $this->checkAdmin();
        $pdo = Database::connect();

        // Stats
        $stats = [];
        $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
        $stats['applications'] = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

        // Recent Logs
        $logs = $pdo->query("SELECT l.*, u.name as user_name FROM audit_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 5")->fetchAll();

        return ['stats' => $stats, 'recent_logs' => $logs];
    }

    public function listUsers()
    {
        $this->checkAdmin();
        $pdo = Database::connect();
        return $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
    }

    public function listLogs()
    {
        $this->checkAdmin();
        $pdo = Database::connect();
        return $pdo->query("SELECT l.*, u.name as user_name FROM audit_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 50")->fetchAll();
    }

    public function deleteUser($id)
    {
        $this->checkAdmin();
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        // Log implementation would go here
        $this->logAction($_SESSION['user_id'], 'delete_user', "Deleted user ID: $id");

        header('Location: /?page=admin_users');
        exit;
    }

    public function logAction($userId, $action, $details = null)
    {
        $pdo = Database::connect();
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $details, $ip]);
    }

    public function createUser()
    {
        $this->checkAdmin();
        return; // View will be handled by router
    }

    public function storeUser($data)
    {
        $this->checkAdmin();
        $pdo = Database::connect();

        // Basic Validation
        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
            die("All fields are required.");
        }

        // Check Email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            die("Email already exists.");
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'], $hashedPassword, $data['role']]);

        $this->logAction($_SESSION['user_id'], 'create_user', "Created user: {$data['email']}");
        header('Location: /?page=admin_users');
        exit;
    }

    public function editUser($id)
    {
        $this->checkAdmin();
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateUser($id, $data)
    {
        $this->checkAdmin();
        $pdo = Database::connect();

        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $params = [$data['name'], $data['email'], $data['role'], $id];

        if (!empty($data['password'])) {
            $sql = "UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?";
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $params = [$data['name'], $data['email'], $data['role'], $hashedPassword, $id];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $this->logAction($_SESSION['user_id'], 'update_user', "Updated user ID: $id");
        header('Location: /?page=admin_users');
        exit;
    }
}
