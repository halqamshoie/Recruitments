<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - SQCCCRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <a href="<?= BASE_URL ?>/" class="logo">
                        <img src="<?= BASE_URL ?>/images/logo.png" alt="SQCCCRC Recruitment" style="height: 50px;">
                    </a>
                    <a href="<?= BASE_URL ?>/" class="logo">
                        <img src="<?= BASE_URL ?>/images/logo2.png" alt="Oman Vision 2040" style="height: 80px;">
                    </a>
                </div>
                <div class="nav-links">
                    <a href="<?= BASE_URL ?>/?page=jobs">Browse Jobs</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/?page=admin_dashboard">Admin Dashboard</a>
                            <a href="<?= BASE_URL ?>/?page=dashboard_hr">HR Dashboard</a>
                            <a href="<?= BASE_URL ?>/?page=analytics">Analytics</a>
                            <a href="<?= BASE_URL ?>/?page=admin_settings">Settings</a>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] === 'hr'): ?>
                            <a href="<?= BASE_URL ?>/?page=dashboard_hr">HR Dashboard</a>
                            <a href="<?= BASE_URL ?>/?page=analytics">Analytics</a>
                        <?php elseif ($_SESSION['role'] === 'applicant'): ?>
                            <a href="<?= BASE_URL ?>/?page=dashboard_applicant">My Dashboard</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/?page=profile" class="nav-link">My Profile</a>
                        <div class="user-menu" style="display: flex; align-items: center; gap: 1rem;">
                            <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                            <a href="<?= BASE_URL ?>/?action=logout" class="btn btn-outline" style="padding: 0.5rem 1rem;">Logout</a>
                        </div>
                    <?php else: ?> <a href="<?= BASE_URL ?>/?page=login">Login</a>
                        <a href="<?= BASE_URL ?>/?page=register" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main class="container">