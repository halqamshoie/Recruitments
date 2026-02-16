<?php
require_once __DIR__ . '/../Models/Database.php';

class AuthController
{

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Check if email is verified
                if (isset($user['email_verified']) && $user['email_verified'] == 0) {
                    // Resend verification code
                    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $stmt = $pdo->prepare("UPDATE users SET verification_code = ?, verification_expires = ? WHERE id = ?");
                    $stmt->execute([$code, $expires, $user['id']]);

                    require_once __DIR__ . '/../Services/EmailService.php';
                    $emailService = new EmailService();
                    $emailService->sendVerificationEmail($email, $user['name'], $code);

                    $_SESSION['verify_email'] = $email;
                    header('Location: /?page=verify_email');
                    exit;
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                header('Location: /');
                exit;
            } else {
                return "Invalid credentials";
            }
        }
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                return "Passwords do not match.";
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'applicant'; // Default role
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $pdo = Database::connect();
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified, verification_code, verification_expires) VALUES (?, ?, ?, ?, 0, ?, ?)");
                if ($stmt->execute([$name, $email, $hashed_password, $role, $code, $expires])) {
                    // Send Verification Email
                    require_once __DIR__ . '/../Services/EmailService.php';
                    $emailService = new EmailService();
                    $emailService->sendVerificationEmail($email, $name, $code);

                    $_SESSION['verify_email'] = $email;
                    header('Location: /?page=verify_email');
                    exit;
                } else {
                    return "Registration failed.";
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'UNIQUE constraint failed: users.email') !== false) {
                    return "This email address is already registered. Please use a different email or log in to your existing account.";
                }
                return "Registration failed. Please try again.";
            }
        }
    }

    public function verifyEmail()
    {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_SESSION['verify_email'] ?? '';
            $code = $_POST['code'] ?? '';

            if (empty($email)) {
                $error = "Session expired. Please register again.";
                return ['error' => $error, 'success' => $success];
            }

            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ? AND verification_expires > datetime('now')");
            $stmt->execute([$email, $code]);
            $user = $stmt->fetch();

            if ($user) {
                // Mark as verified
                $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_code = NULL, verification_expires = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);

                // Send Welcome Email
                require_once __DIR__ . '/../Services/EmailService.php';
                $emailService = new EmailService();
                $emailService->sendWelcomeEmail($email, $user['name']);

                unset($_SESSION['verify_email']);

                // Auto-login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                header('Location: /');
                exit;
            } else {
                $error = "Invalid or expired verification code. Please check and try again.";
            }
        }

        return ['error' => $error, 'success' => $success];
    }

    public function resendCode()
    {
        $email = $_SESSION['verify_email'] ?? '';
        if (empty($email)) {
            return "Session expired. Please register again.";
        }

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND email_verified = 0");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $stmt = $pdo->prepare("UPDATE users SET verification_code = ?, verification_expires = ? WHERE id = ?");
            $stmt->execute([$code, $expires, $user['id']]);

            require_once __DIR__ . '/../Services/EmailService.php';
            $emailService = new EmailService();
            $emailService->sendVerificationEmail($email, $user['name'], $code);

            return "A new verification code has been sent to your email.";
        }
        return "Could not resend code.";
    }

    public function sendResetLink()
    {
        $email = $_POST['email'];
        $pdo = Database::connect();

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            return "If this email is registered, you will receive a reset link.";
        }

        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $stmt->execute([$email, $token]);

        require_once __DIR__ . '/../Services/EmailService.php';
        $emailService = new EmailService();
        $emailService->sendPasswordResetEmail($email, $token);

        return "Reset link sent! Check your email.";
    }

    public function resetPassword()
    {
        $token = $_POST['token'];
        $password = $_POST['password'];

        $pdo = Database::connect();

        // Validate Token
        $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at > datetime('now', '-1 hour')");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            return "Invalid or expired token.";
        }

        // Update Password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $reset['email']]);

        // Delete Token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$reset['email']]);

        header('Location: /?page=login&msg=password_reset_success');
        exit;
    }

    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}
