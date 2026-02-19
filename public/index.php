<?php
session_start();

// Autoload Controllers (Manual for now)
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php'; // Load Admin Controller
require_once __DIR__ . '/../src/Models/Database.php';

// Simple Router
$page = $_GET['page'] ?? 'jobs';
$action = $_GET['action'] ?? null;

// Audit Helper
function audit_log($action, $details = null)
{
    if (isset($_SESSION['user_id'])) {
        $admin = new AdminController(); // Reusing the helper method
        $admin->logAction($_SESSION['user_id'], $action, $details);
    }
}

// Auth Actions
if ($action === 'logout') {
    audit_log('logout', 'User logged out');
    $auth = new AuthController();
    $auth->logout();
}

if ($action === 'update_status') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['hr', 'admin'])) {
        redirect('/');
        exit;
    }
    $application_id = $_GET['id'];
    $new_status = $_GET['status']; // e.g. 'hired', 'rejected'

    if (in_array($new_status, ['pending', 'reviewed', 'rejected', 'shortlisted'])) {
        $pdo = Database::connect();

        // Fetch applicant email and job details before updating
        $stmt = $pdo->prepare("SELECT u.email, u.name, j.title 
                               FROM applications a 
                               JOIN users u ON a.user_id = u.id 
                               JOIN jobs j ON a.job_id = j.id 
                               WHERE a.id = ?");
        $stmt->execute([$application_id]);
        $appData = $stmt->fetch();

        // Update status
        $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $application_id]);

        // Audit Log
        audit_log('update_application_status', "App ID: $application_id set to $new_status");

        // Send Status Update Email - ONLY if status is 'shortlisted' AND setting is enabled
        if ($appData && $new_status === 'shortlisted') {
            $emailCheck = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $emailCheck->execute(['email_enabled']);
            $emailOn = $emailCheck->fetchColumn() === '1';
            $emailCheck->execute(['notify_shortlisted']);
            $shortlistOn = $emailCheck->fetchColumn() === '1';
            if ($emailOn && $shortlistOn) {
                require_once __DIR__ . '/../src/Services/EmailService.php';
                $emailService = new EmailService();
                $emailService->sendStatusUpdateEmail($appData['email'], $appData['name'], $appData['title'], $new_status);
            }
        }
    }
    redirect('/?page=dashboard_hr');
    exit;
} elseif ($action === 'review_cv') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }
    $application_id = $_GET['id'];

    $pdo = Database::connect();

    // Get application info
    $stmt = $pdo->prepare("SELECT status, resume_path FROM applications WHERE id = ?");
    $stmt->execute([$application_id]);
    $app = $stmt->fetch();

    if ($app) {
        // Auto-update status to 'reviewed' if it is currently pending
        if ($app['status'] === 'pending') {
            $stmt = $pdo->prepare("UPDATE applications SET status = 'reviewed' WHERE id = ?");
            $stmt->execute([$application_id]);
            audit_log('review_cv', "Auto-updated App ID: $application_id to reviewed");
        }

        // Redirect to CV
        header('Location: ' . url($app['resume_path']));
        exit;
    }
    die("Application not found.");
} elseif ($action === 'download_all_cvs') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }

    $jobTitle = $_GET['job_title'] ?? '';
    if (empty($jobTitle))
        die("Job Title Required");

    $pdo = Database::connect();
    // Fetch all applications with CV and qualification files
    $stmt = $pdo->prepare("SELECT a.resume_path, a.qualification_files, u.name 
                           FROM applications a 
                           JOIN jobs j ON a.job_id = j.id 
                           JOIN users u ON a.user_id = u.id 
                           WHERE j.title = ?");
    $stmt->execute([$jobTitle]);
    $applications = $stmt->fetchAll();

    if (empty($applications)) {
        die("No applications found for this job.");
    }

    $zip = new ZipArchive();
    $safeJobTitle = preg_replace('/[^a-zA-Z0-9]/', '_', $jobTitle);
    $zipName = "Applications_" . $safeJobTitle . "_" . date('Ymd') . ".zip";
    $tempFile = sys_get_temp_dir() . '/' . $zipName;

    if ($zip->open($tempFile, ZipArchive::CREATE) !== TRUE) {
        die("Could not create zip file.");
    }

    foreach ($applications as $app) {
        // Create a folder per candidate
        $candidateFolder = preg_replace('/[^a-zA-Z0-9 ]/', '', $app['name']);
        $candidateFolder = str_replace(' ', '_', trim($candidateFolder));

        // Add CV
        if (!empty($app['resume_path'])) {
            $realPath = __DIR__ . '/../public' . $app['resume_path'];
            if (file_exists($realPath)) {
                $extension = pathinfo($realPath, PATHINFO_EXTENSION);
                $zip->addFile($realPath, $candidateFolder . '/CV.' . $extension);
            }
        }

        // Add Qualification Files
        $qualFiles = json_decode($app['qualification_files'] ?? '[]', true);
        if (!empty($qualFiles)) {
            foreach ($qualFiles as $idx => $filePath) {
                $realPath = __DIR__ . '/../public' . $filePath;
                if (file_exists($realPath)) {
                    $extension = pathinfo($realPath, PATHINFO_EXTENSION);
                    $zip->addFile($realPath, $candidateFolder . '/Qualification_' . ($idx + 1) . '.' . $extension);
                }
            }
        }
    }
    $zip->close();

    // Stream the file
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=' . $zipName);
    header('Content-Length: ' . filesize($tempFile));
    readfile($tempFile);
    unlink($tempFile);
    exit;

} elseif ($action === 'export_csv') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }

    $jobTitle = $_GET['job_title'] ?? '';
    if (empty($jobTitle))
        die("Job Title Required");

    $pdo = Database::connect();
    // Fetch all applications for this job title
    $stmt = $pdo->prepare("SELECT u.name, u.email, u.title as applicant_title, u.nationality, u.place_of_work, a.phone, a.status, a.created_at, a.resume_path 
                           FROM applications a 
                           JOIN jobs j ON a.job_id = j.id 
                           JOIN users u ON a.user_id = u.id 
                           WHERE j.title = ? 
                           ORDER BY a.created_at DESC");
    $stmt->execute([$jobTitle]);
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($applicants)) {
        die("No applicants found for this job.");
    }

    $filename = "Candidates_" . preg_replace('/[^a-zA-Z0-9]/', '_', $jobTitle) . "_" . date('Ymd') . ".csv";

    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Add Byte Order Mark (BOM) for Excel compatibility with UTF-8
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Header Row
    fputcsv($output, ['Applicant Name', 'Job Title (Designation)', 'Nationality', 'Place of Work', 'Email', 'Phone', 'Status', 'Applied Date', 'CV Link']);

    // Data Rows
    foreach ($applicants as $app) {
        // Generate full URL for CV if needed, or just path
        $cvLink = $app['resume_path'] ? "http://" . $_SERVER['HTTP_HOST'] . $app['resume_path'] : 'N/A';

        fputcsv($output, [
            $app['name'],
            $app['applicant_title'] ?? '',
            $app['nationality'] ?? '',
            $app['place_of_work'] ?? '',
            $app['email'],
            $app['phone'],
            ucfirst($app['status']),
            $app['created_at'],
            $cvLink
        ]);
    }

    fclose($output);
    exit;

} elseif ($action === 'send_reset_link') {
    $auth = new AuthController();
    $msg = $auth->sendResetLink();
    $page = 'forgot_password';
} elseif ($action === 'update_password') {
    $auth = new AuthController();
    $error = $auth->resetPassword();
    // If we're here, it failed
    $page = 'reset_password';
} elseif ($action === 'store_user') {
    $admin = new AdminController();
    $admin->storeUser($_POST);
} elseif ($action === 'reset_applications') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        redirect('/');
        exit;
    }

    $pdo = Database::connect();
    
    // 1. Truncate Applications Table
    $pdo->exec("DELETE FROM applications");
    $pdo->exec("DELETE FROM sqlite_sequence WHERE name='applications'"); // Reset ID counter
    
    // 2. Delete All Files in Upload Directories
    $dirs = [
        __DIR__ . '/../public/uploads/resumes/',
        __DIR__ . '/../public/uploads/qualifications/'
    ];

    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '*'); // Get all files
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file); // Delete file
                }
            }
        }
    }

    audit_log('reset_applications', 'Deleted ALL applications and files');
    
    // Redirect with success parameter handled by settings page logic (need to update settings page to show generic msg if needed, 
    // but for now we rely on the existing success handling or add a specific one)
    // The settings page looks for $success variable. We can't pass it easily via redirect unless we add a param.
    // Let's rely on adding a msg param.
    redirect('/?page=admin_settings&msg=reset_success');
    exit;
} elseif ($action === 'update_application') {
    if (!isset($_SESSION['user_id'])) {
        redirect('/?page=login');
        exit;
    }

    $appId = $_GET['id'];
    $pdo = Database::connect();
    
    // Verify ownership
    // Verify ownership & Fetch Job Dates
    $stmt = $pdo->prepare("SELECT a.*, j.opening_date, j.closing_date 
                           FROM applications a 
                           JOIN jobs j ON a.job_id = j.id
                           WHERE a.id = ? AND a.user_id = ?");
    $stmt->execute([$appId, $_SESSION['user_id']]);
    $application = $stmt->fetch();

    if (!$application) {
        die("Application not found.");
    }

    // Date Restriction Check
    $now = new DateTime();
    $opening = !empty($application['opening_date']) ? new DateTime($application['opening_date'] . ' 08:00:00') : null;
    $closing = !empty($application['closing_date']) ? new DateTime($application['closing_date'] . ' 23:59:59') : null;

    if (($opening && $now < $opening) || ($closing && $now > $closing)) {
        redirect('/?page=dashboard_applicant&msg=expired');
        exit;
    }



    // Handle Resume Update (Replace)
    $resumePath = $application['resume_path'];


    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../public/uploads/resumes/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = uniqid() . '_' . basename($_FILES['resume']['name']);
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $uploadDir . $filename)) {
             $resumePath = '/uploads/resumes/' . $filename;
        }
    }

    // Handle Qualifications Update (Append)
    $qualFiles = json_decode($application['qualification_files'] ?? '[]', true);
    if (isset($_FILES['qualifications'])) {
        $uploadDir = __DIR__ . '/../public/uploads/qualifications/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $count = count($_FILES['qualifications']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['qualifications']['error'][$i] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . basename($_FILES['qualifications']['name'][$i]);
                if (move_uploaded_file($_FILES['qualifications']['tmp_name'][$i], $uploadDir . $filename)) {
                    $qualFiles[] = '/uploads/qualifications/' . $filename;
                }
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE applications SET resume_path = ?, qualification_files = ? WHERE id = ?");
    $stmt->execute([$resumePath, json_encode($qualFiles), $appId]);

    redirect('/?page=dashboard_applicant&msg=updated');
    exit;
} elseif ($action === 'delete_qualification') {
    if (!isset($_SESSION['user_id'])) {
        redirect('/?page=login');
        exit;
    }

    $appId = $_GET['id'];
    $fileToDelete = urldecode($_GET['file']);
    $pdo = Database::connect();

    // Verify ownership
    // Verify ownership & Fetch Job Dates
    $stmt = $pdo->prepare("SELECT a.*, j.opening_date, j.closing_date 
                           FROM applications a 
                           JOIN jobs j ON a.job_id = j.id
                           WHERE a.id = ? AND a.user_id = ?");
    $stmt->execute([$appId, $_SESSION['user_id']]);
    $application = $stmt->fetch();

    // Date Restriction Check
    if ($application) {
        $now = new DateTime();
        $opening = !empty($application['opening_date']) ? new DateTime($application['opening_date'] . ' 08:00:00') : null;
        $closing = !empty($application['closing_date']) ? new DateTime($application['closing_date'] . ' 23:59:59') : null;

        if (($opening && $now < $opening) || ($closing && $now > $closing)) {
            redirect('/?page=dashboard_applicant&msg=expired');
            exit;
        }

        $qualFiles = json_decode($application['qualification_files'] ?? '[]', true);
        
        // Find and remove the file
        $key = array_search($fileToDelete, $qualFiles);
        if ($key !== false) {
            unset($qualFiles[$key]);
            $qualFiles = array_values($qualFiles); // Re-index
            
            // Start Update
            $stmt = $pdo->prepare("UPDATE applications SET qualification_files = ? WHERE id = ?");
            $stmt->execute([json_encode($qualFiles), $appId]);

            // Optional: Delete physical file if it exists
            // Since we use unique IDs, we can delete it safely if we are sure no one else uses it (which is true here)
            $filePath = __DIR__ . '/../public' . $fileToDelete;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    redirect('/?page=dashboard_applicant&msg=file_deleted');
    exit;
} elseif ($action === 'update_user') {
    $admin = new AdminController();
    $admin->updateUser($_GET['id'], $_POST);
} elseif ($action === 'delete_user') {
    $admin = new AdminController();
    $admin->deleteUser($_GET['id']);
} elseif ($action === 'update_profile') {
    if (!isset($_SESSION['user_id'])) {
        redirect('/?page=login');
        exit;
    }

    $pdo = Database::connect();
    $userId = $_SESSION['user_id'];

    // Handle Avatar Upload
    $avatarPath = null; // Default: keep existing
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = uniqid() . '_' . basename($_FILES['avatar']['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
            $avatarPath = '/uploads/avatars/' . $filename;
        }
    }

    // Combine phone code and number if provided separately
    // Combine phone code and number if provided separately
    $phone = $_POST['phone'];
    if (isset($_POST['phone_code']) && !empty($_POST['phone_code'])) {
        $phone = $_POST['phone_code'] . ' ' . $_POST['phone'];
    }

    // Validation: Check for required fields
    if (empty($_POST['name']) || empty($_POST['title']) || empty($phone) || 
        empty($_POST['nationality']) || empty($_POST['place_of_work']) || empty($_POST['gender'])) {
        redirect('/?page=profile&msg=incomplete_profile');
        exit;
    }

    // Prepare Update Query
    $sql = "UPDATE users SET name = ?, title = ?, phone = ?, bio = ?, nationality = ?, place_of_work = ?, gender = ?";
    $params = [
        $_POST['name'],
        $_POST['title'],
        $phone,
        $_POST['bio'],
        $_POST['nationality'],
        $_POST['place_of_work'],
        $_POST['gender']
    ];

    if ($avatarPath) {
        $sql .= ", avatar = ?";
        $params[] = $avatarPath;
    }

    $sql .= " WHERE id = ?";
    $params[] = $userId;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Update session name if changed
    $_SESSION['user_name'] = $_POST['name'];

    audit_log('update_profile', "User ID: $userId updated profile");

    if (isset($_GET['redirect_to']) && $_GET['redirect_to'] === 'job_detail') {
        $jobId = $_GET['job_id'] ?? '';
        redirect('/?page=job_detail&id=' . $jobId . '&msg=profile_updated');
    }

    redirect('/?page=profile&success=1');
    exit;

} elseif ($action === 'export_analytics_report') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }

    $pdo = Database::connect();

    // 1. Gather Global Stats
    $globalStats = [];
    $globalStats['Total Applications'] = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
    $globalStats['Active Jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")->fetchColumn();
    $globalStats['Shortlisted Candidates'] = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'shortlisted'")->fetchColumn();
    $globalStats['Total Jobs Created'] = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();

    // 2. Gather Per-Job Stats
    // Title, Status, Total Apps, Pending, Reviewed, Shortlisted, Rejected
    $stmt = $pdo->query("
        SELECT 
            j.title, 
            j.status,
            COUNT(a.id) as total_applications,
            SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN a.status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
            SUM(CASE WHEN a.status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
            SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM jobs j
        LEFT JOIN applications a ON j.id = a.job_id
        GROUP BY j.id, j.title, j.status
        ORDER BY total_applications DESC
    ");
    $jobStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Generate CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="Recruitment_Analytics_Report_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    // Section 1: Header & Global Stats
    fputcsv($output, ['--- Global Recruitment Overview ---']);
    foreach ($globalStats as $key => $val) {
        fputcsv($output, [$key, $val]);
    }
    fputcsv($output, []); // Empty line

    // Section 2: Job Performance
    fputcsv($output, ['--- Job Performance Breakdown ---']);
    fputcsv($output, ['Job Title', 'Status', 'Total Apps', 'Pending', 'Reviewed', 'Shortlisted', 'Rejected']);

    foreach ($jobStats as $row) {
        fputcsv($output, [
            $row['title'],
            $row['status'],
            $row['total_applications'],
            $row['pending'],
            $row['reviewed'],
            $row['shortlisted'],
            $row['rejected']
        ]);
    }

    fclose($output);
    audit_log('export_analytics', "Exported Analytics Report");
    exit;

} elseif ($action === 'delete_job') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }
    $id = $_GET['id'];
    $pdo = Database::connect();
    $stmt = $pdo->prepare("UPDATE jobs SET status = 'closed' WHERE id = ?"); // Soft delete

    // Cleanup applications
    $pdo->prepare("DELETE FROM applications WHERE job_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM jobs WHERE id = ?")->execute([$id]);

    audit_log('delete_job', "Deleted Job ID: $id");
    redirect('/?page=jobs');
    exit;

} elseif ($action === 'archive_job') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }
    $id = $_GET['id'];
    $pdo = Database::connect();
    $stmt = $pdo->prepare("UPDATE jobs SET status = 'archived' WHERE id = ?");
    $stmt->execute([$id]);
    audit_log('archive_job', "Archived Job ID: $id");
    redirect('/?page=jobs');
    exit;

} elseif ($action === 'toggle_job_status') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }
    $id = $_GET['id'];
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT status FROM jobs WHERE id = ?");
    $stmt->execute([$id]);
    $currentStatus = $stmt->fetchColumn();

    // Toggle Logic: open -> draft, anything else -> open
    $newStatus = ($currentStatus === 'open') ? 'draft' : 'open';

    $stmt = $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    audit_log('toggle_status', "Job ID: $id status changed to $newStatus");
    redirect('/?page=jobs');
    exit;

} elseif ($action === 'duplicate_job') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }
    $id = $_GET['id'];
    $pdo = Database::connect();

    // Fetch original job
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($job) {
        unset($job['id']); // Remove ID to auto-increment
        unset($job['created_at']); // Let DB handle timestamp
        $job['title'] = $job['title'] . ' (Copy)';
        $job['status'] = 'draft'; // Default to draft for copies

        // Insert Copy
        $cols = implode(',', array_keys($job));
        $vals = implode(',', array_fill(0, count($job), '?'));
        $stmt = $pdo->prepare("INSERT INTO jobs ($cols) VALUES ($vals)");
        $stmt->execute(array_values($job));

        $newId = $pdo->lastInsertId();
        audit_log('duplicate_job', "Duplicated Job ID: $id to New ID: $newId");
    }
    redirect('/?page=jobs');
    exit;
}

// Render Logic
ob_start();

if ($page === 'login') {
    $auth = new AuthController();
    $error = $auth->login();
    require __DIR__ . '/../src/Views/auth/login.php';
} elseif ($page === 'register') {
    $auth = new AuthController();
    $error = $auth->register();
    require __DIR__ . '/../src/Views/auth/register.php';
} elseif ($page === 'verify_email') {
    $auth = new AuthController();
    $result = $auth->verifyEmail();
    $resend_msg = '';
    require __DIR__ . '/../src/Views/auth/verify_email.php';
} elseif ($page === 'resend_code') {
    $auth = new AuthController();
    $resend_msg = $auth->resendCode();
    $result = ['error' => '', 'success' => ''];
    require __DIR__ . '/../src/Views/auth/verify_email.php';
} elseif ($page === 'forgot_password') {
    require __DIR__ . '/../src/Views/auth/forgot_password.php';
} elseif ($page === 'reset_password') {
    require __DIR__ . '/../src/Views/auth/reset_password.php';
} elseif ($page === 'apply') {
    if (!isset($_SESSION['user_id'])) {
        redirect('/?page=login');
        exit;
    }

    // Force Profile Update
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (empty($user['nationality']) || empty($user['place_of_work']) || empty($user['phone']) || empty($user['title'])) {
         redirect('/?page=profile&msg=incomplete_profile');
         exit;
    }
    $job_id = $_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate Certification
        if (!isset($_POST['certification'])) {
            echo "<script>alert('You must certify that the information is true and correct.'); history.back();</script>";
            exit;
        }

        // Handle Resume Upload
        $resumePath = null;
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/resumes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['resume']['name']);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetPath)) {
                $resumePath = '/uploads/resumes/' . $filename;
            }
        }

        // Handle Qualification Files Upload
        $qualificationPaths = [];
        if (isset($_FILES['qualifications'])) {
            $uploadDir = __DIR__ . '/../public/uploads/qualifications/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $count = count($_FILES['qualifications']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['qualifications']['error'][$i] === UPLOAD_ERR_OK) {
                    $filename = uniqid() . '_' . basename($_FILES['qualifications']['name'][$i]);
                    $targetPath = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['qualifications']['tmp_name'][$i], $targetPath)) {
                        $qualificationPaths[] = '/uploads/qualifications/' . $filename;
                    }
                }
            }
        }
        $qualificationsJson = json_encode($qualificationPaths);

        $pdo = Database::connect();
        
        // Check for duplicate application
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ? AND user_id = ?");
        $stmt->execute([$job_id, $_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
           echo "<script>alert('You have already applied for this job.'); window.location.href='/?page=dashboard_applicant';</script>";
           exit;
        }
        
        // Fetch User Phone
        $stmt = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userPhone = $stmt->fetchColumn();

        // New columns: phone, qualification_files. Removed cover_letter.
        $stmt = $pdo->prepare("INSERT INTO applications (job_id, user_id, phone, resume_path, qualification_files) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$job_id, $_SESSION['user_id'], $userPhone, $resumePath, $qualificationsJson]);

        // Fetch Job Title and User Name for Email
        $stmt = $pdo->prepare("SELECT title FROM jobs WHERE id = ?");
        $stmt->execute([$job_id]);
        $jobTitle = $stmt->fetchColumn();

        // Send Confirmation Email
        require_once __DIR__ . '/../src/Services/EmailService.php';
        $emailService = new EmailService();
        $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            $emailService->sendApplicationReceivedEmail($user['email'], $user['name'], $jobTitle);
            // Redirect
            redirect('/?page=dashboard_applicant');
            exit;
        }
    }

} elseif ($page === 'jobs') {
    // Job Listing (Public/Home) with Filtering
    $pdo = Database::connect();

    // Determine visibility
    $isHR = isset($_SESSION['role']) && ($_SESSION['role'] === 'hr' || $_SESSION['role'] === 'admin');

    $sql = "SELECT * FROM jobs";
    // If NOT HR, only show open jobs and exclude archived/drafts
    if (!$isHR) {
        $sql .= " WHERE status = 'open' AND (opening_date IS NULL OR datetime(opening_date || ' 08:00:00') <= datetime('now', 'localtime')) AND (closing_date IS NULL OR datetime(closing_date || ' 23:59:59') >= datetime('now', 'localtime'))";
    } else {
        $sql .= " WHERE 1=1"; // Placeholder for simpler appending
    }

    $params = [];

    // Filter Logic
    if (!empty($_GET['q'])) {
        $sql .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = '%' . $_GET['q'] . '%';
        $params[] = '%' . $_GET['q'] . '%';
    }
    if (!empty($_GET['location'])) {
        $sql .= " AND location LIKE ?";
        $params[] = '%' . $_GET['location'] . '%';
    }

    // Status Filter (HR Only)
    if ($isHR && !empty($_GET['status'])) {
        $statusFilter = $_GET['status'];
        // Map UI terms if necessary, or just use DB values
        // DB Values: open, closed, draft, archived
        if (in_array($statusFilter, ['open', 'closed', 'draft', 'archived'])) {
            $sql .= " AND status = ?";
            $params[] = $statusFilter;
        }
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();

    require __DIR__ . '/../src/Views/jobs/index.php';
} elseif ($page === 'job_detail') {
    $id = $_GET['id'] ?? 0;
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$id]);
    $job = $stmt->fetch();
    require __DIR__ . '/../src/Views/jobs/show.php';
} elseif ($page === 'dashboard_hr') {
    // HR Check
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['hr', 'admin'])) {
        redirect('/?page=login');
        exit;
    }
    // Fetch All Jobs for Filter
    $pdo = Database::connect();
    $jobsStmt = $pdo->query("SELECT id, title FROM jobs ORDER BY title");
    $allJobs = $jobsStmt->fetchAll();

    $filterJobId = $_GET['job_id'] ?? '';

    // Fetch Applications
    $sql = "SELECT a.*, j.title as job_title, j.closing_date as job_closing_date, j.status as job_status,
                         u.name as applicant_name, u.email as applicant_email, 
                         u.title as applicant_title, u.nationality, u.place_of_work, u.gender
                         FROM applications a 
                         JOIN jobs j ON a.job_id = j.id 
                         JOIN users u ON a.user_id = u.id";

    $params = [];
    if (!empty($filterJobId)) {
        $sql .= " WHERE a.job_id = ?";
        $params[] = $filterJobId;
    }

    $sql .= " ORDER BY a.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll();
    require __DIR__ . '/../src/Views/dashboard/hr.php';
} elseif ($page === 'analytics') {
    // HR Check
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['hr', 'admin'])) {
        redirect('/?page=login');
        exit;
    }

    $pdo = Database::connect();

    // 1. Key Metrics
    $stats = [];
    $stats['total_applications'] = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
    $stats['active_jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'open' AND (closing_date IS NULL OR closing_date >= DATE('now'))")->fetchColumn();
    $stats['shortlisted_count'] = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'shortlisted'")->fetchColumn();
    $stats['pending_count'] = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'pending'")->fetchColumn();

    // 2. Status Distribution
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM applications GROUP BY status");
    $status_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    // Ensure all keys exist for colors
    $status_data = [
        'Pending' => $status_raw['pending'] ?? 0,
        'Reviewed' => $status_raw['reviewed'] ?? 0,
        'Rejected' => $status_raw['rejected'] ?? 0,
        'Shortlisted' => $status_raw['shortlisted'] ?? 0
    ];

    // 3. Top Jobs
    $stmt = $pdo->query("SELECT j.title, COUNT(a.id) as count 
                         FROM jobs j 
                         LEFT JOIN applications a ON j.id = a.job_id 
                         GROUP BY j.id 
                         ORDER BY count DESC 
                         LIMIT 5");
    $top_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Trend (Last 30 Days)
    $stmt = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count 
                         FROM applications 
                         WHERE created_at >= DATE('now', '-30 days') 
                         GROUP BY DATE(created_at) 
                         ORDER BY date ASC");
    $trend_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require __DIR__ . '/../src/Views/dashboard/analytics.php';
} elseif ($page === 'dashboard_applicant') {
    // Applicant Check
    if (!isset($_SESSION['user_id'])) {
        redirect('/?page=login');
        exit;
    }
    // Fetch My Applications
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT a.*, j.title as job_title, j.opening_date, j.closing_date 
                           FROM applications a 
                           JOIN jobs j ON a.job_id = j.id 
                           WHERE a.user_id = ? 
                           ORDER BY a.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $my_applications = $stmt->fetchAll();
    require __DIR__ . '/../src/Views/dashboard/applicant.php';
} elseif ($page === 'job_create') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['hr', 'admin'])) {
        redirect('/');
        exit;
    }
    // Handle Post
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo = Database::connect();

        // Determine status (default to open if not set)
        $status = $_POST['status'] ?? 'open';
        if (!in_array($status, ['open', 'draft'])) {
            $status = 'open';
        }

        // Server-side validation for Open jobs
        if ($status === 'open') {
            $errors = [];
            if (empty($_POST['location']))
                $errors[] = "Location is required.";
            if (empty($_POST['opening_date']))
                $errors[] = "Opening date is required.";
            if (empty($_POST['closing_date']))
                $errors[] = "Closing date is required.";
            if (empty($_POST['description']))
                $errors[] = "Job description is required.";

            if (!empty($errors)) {
                // Determine how to handle errors. For simple implementation, stop and show error.
                // ideally, we re-render the view with errors.
                echo "<div style='color: red; padding: 2rem; text-align: center; font-family: sans-serif;'>
                        <h2>Error: Cannot Publish Job</h2>
                        <ul style='list-style: none; padding: 0;'>";
                foreach ($errors as $error) {
                    echo "<li style='margin-bottom: 0.5rem;'>$error</li>";
                }
                echo "</ul>
                      <p>Please <a href='javascript:history.back()'>go back</a> and fill in all required fields to post the job, or save it as a draft.</p>
                      </div>";
                exit;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO jobs (title, description, requirements, location, gender, experience, vacancies, department, opening_date, closing_date, created_by, status) VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['location'],
            $_POST['gender'],
            $_POST['experience'],
            $_POST['vacancies'] ?? 1,
            $_POST['department'] ?? '',
            $_POST['opening_date'],
            $_POST['closing_date'],
            $_SESSION['user_id'],
            $status
        ]);
        redirect('/?page=jobs');
        exit;
    }
    require __DIR__ . '/../src/Views/jobs/create.php';
} elseif ($page === 'job_edit') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hr' && $_SESSION['role'] !== 'admin')) {
        redirect('/');
        exit;
    }
    $id = $_GET['id'];
    $pdo = Database::connect();

    // Handle Post Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Determine status
        $status = $_POST['status'] ?? 'open';
        if (!in_array($status, ['open', 'draft'])) {
            $status = 'open'; // Default fallback
        }

        // Server-side validation if Publishing
        if ($status === 'open') {
            $errors = [];
            if (empty($_POST['location']))
                $errors[] = "Location is required.";
            if (empty($_POST['opening_date']))
                $errors[] = "Opening date is required.";
            if (empty($_POST['closing_date']))
                $errors[] = "Closing date is required.";
            if (empty($_POST['description']))
                $errors[] = "Job description is required.";

            if (!empty($errors)) {
                echo "<div style='color: red; padding: 2rem; text-align: center; font-family: sans-serif;'>
                        <h2>Error: Cannot Publish Job</h2>
                        <ul style='list-style: none; padding: 0;'>";
                foreach ($errors as $error) {
                    echo "<li style='margin-bottom: 0.5rem;'>$error</li>";
                }
                echo "</ul>
                      <p>Please <a href='javascript:history.back()'>go back</a> and fill in all required fields to publish the job, or save it as a draft.</p>
                      </div>";
                exit;
            }
        }

        $stmt = $pdo->prepare("UPDATE jobs SET title = ?, location = ?, gender = ?, opening_date = ?, closing_date = ?, experience = ?, vacancies = ?, department = ?, description = ?, status = ? WHERE id = ?");
        $stmt->execute([
            $_POST['title'],
            $_POST['location'],
            $_POST['gender'],
            $_POST['opening_date'],
            $_POST['closing_date'],
            $_POST['experience'],
            $_POST['vacancies'] ?? 1,
            $_POST['department'] ?? '',
            $_POST['description'],
            $status,
            $id
        ]);

        audit_log('edit_job', "Edited Job ID: $id (Status: $status)");
        redirect('/?page=job_detail&id=' . $id);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$id]);
    $job = $stmt->fetch();
    require __DIR__ . '/../src/Views/jobs/edit.php';
} elseif ($page === 'apply') {
    if (!isset($_SESSION['user_id'])) {
        redirect('/?page=login');
        exit;
    }
    $job_id = $_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle File Upload
        $resumePath = null;
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/resumes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['resume']['name']);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetPath)) {
                $resumePath = '/uploads/resumes/' . $filename;
            }
        }

        $pdo = Database::connect();
        // New columns: phone. Removed cover_letter.
        $stmt = $pdo->prepare("INSERT INTO applications (job_id, user_id, phone, resume_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$job_id, $_SESSION['user_id'], $_POST['phone'] ?? '', $resumePath]);
        redirect('/?page=dashboard_applicant');
        exit;
    }

} elseif ($page === 'profile') {
    if (!isset($_SESSION['user_id'])) {
        redirect('/?page=login');
        exit;
    }
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    require __DIR__ . '/../src/Views/profile/edit.php';
} elseif ($page === 'admin_dashboard') {
    $admin = new AdminController();
    $data = $admin->dashboard();
    $stats = $data['stats'];
    $recent_logs = $data['recent_logs'];
    require __DIR__ . '/../src/Views/admin/dashboard.php';
} elseif ($page === 'admin_users') {
    $admin = new AdminController();
    $roleFilter = $_GET['role'] ?? null;
    $users = $admin->listUsers($roleFilter);
    require __DIR__ . '/../src/Views/admin/users.php';
} elseif ($page === 'admin_user_create') {
    $admin = new AdminController();
    $admin->createUser(); // Just access check
    require __DIR__ . '/../src/Views/admin/user_form.php';
} elseif ($page === 'admin_user_edit') {
    $admin = new AdminController();
    $user = $admin->editUser($_GET['id']);
    require __DIR__ . '/../src/Views/admin/user_form.php';
} elseif ($page === 'admin_logs') {
    $admin = new AdminController();
    $logs = $admin->listLogs();
    require __DIR__ . '/../src/Views/admin/logs.php';
} elseif ($page === 'admin_settings') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        redirect('/?page=login');
        exit;
    }

    $pdo = Database::connect();
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, datetime('now'))");
        $section = $_POST['section'] ?? '';

        if ($section === 'api_keys') {
            $stmt->execute(['tinymce_api_key', $_POST['tinymce_api_key'] ?? '']);
            audit_log('update_settings', 'Updated API Keys');
            $success = 'API Keys saved successfully.';
        } elseif ($section === 'email_notifications') {
            $stmt->execute(['email_enabled', isset($_POST['email_enabled']) ? '1' : '0']);
            $stmt->execute(['notify_new_application', isset($_POST['notify_new_application']) ? '1' : '0']);
            $stmt->execute(['notify_status_change', isset($_POST['notify_status_change']) ? '1' : '0']);
            $stmt->execute(['notify_shortlisted', isset($_POST['notify_shortlisted']) ? '1' : '0']);
            $stmt->execute(['notify_new_job', isset($_POST['notify_new_job']) ? '1' : '0']);
            $stmt->execute(['notify_job_closing', isset($_POST['notify_job_closing']) ? '1' : '0']);
            $stmt->execute(['notify_interview_scheduled', isset($_POST['notify_interview_scheduled']) ? '1' : '0']);
            $stmt->execute(['notification_email', $_POST['notification_email'] ?? '']);
            audit_log('update_settings', 'Updated Email Notification Settings');
            $success = 'Email notification settings saved successfully.';
        }
    }

    // Load all settings
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $settings = $rows;

    require __DIR__ . '/../src/Views/admin/settings.php';
} else {
    echo "404 Not Found";
}

$content = ob_get_clean();
require __DIR__ . '/../src/Views/layout/header.php';
echo $content;
require __DIR__ . '/../src/Views/layout/footer.php';
