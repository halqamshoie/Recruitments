<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RecruitPro - Modern Recruitment</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <a href="/" class="logo">
                    <img src="/images/logo.png" alt="SQCCCRC Recruitment" style="height: 50px;">
                </a>
                <div class="nav-links">
                    <a href="/?page=jobs">Browse Jobs</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="/?page=admin_dashboard">Admin Dashboard</a>
                            <a href="/?page=dashboard_hr">HR Dashboard</a>
                            <a href="/?page=analytics">Analytics</a>
                            <a href="/?page=admin_settings">Settings</a>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] === 'hr'): ?>
                            <a href="/?page=dashboard_hr">HR Dashboard</a>
                            <a href="/?page=analytics">Analytics</a>
                        <?php elseif ($_SESSION['role'] === 'applicant'): ?>
                            <a href="/?page=dashboard_applicant">My Dashboard</a>
                        <?php endif; ?>
                        <a href="/?page=profile" class="nav-link">My Profile</a>
                        <div class="user-menu" style="display: flex; align-items: center; gap: 1rem;">
                            <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                            <a href="/?action=logout" class="btn btn-outline" style="padding: 0.5rem 1rem;">Logout</a>
                        </div>
                    <?php else: ?> <a href="/?page=login">Login</a>
                        <a href="/?page=register" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main class="container">