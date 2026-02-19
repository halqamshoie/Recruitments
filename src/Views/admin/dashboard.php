<div class="mb-8 flex justify-between items-center">
    <h1>Admin Dashboard</h1>
    <span class="text-muted">Welcome,
        <?php echo htmlspecialchars($_SESSION['name']); ?>
    </span>
</div>

<!-- Stats Grid -->
<div class="grid"
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="glass-panel text-center">
        <h3 style="color: #00AAE6; font-size: 2.5rem; margin-bottom: 0.5rem;">
            <?php echo $stats['users']; ?>
        </h3>
        <p class="text-muted">Total Users</p>
    </div>
    <div class="glass-panel text-center">
        <h3 style="color: #7A4398; font-size: 2.5rem; margin-bottom: 0.5rem;">
            <?php echo $stats['jobs']; ?>
        </h3>
        <p class="text-muted">Active Jobs</p>
    </div>
    <div class="glass-panel text-center">
        <h3 style="color: #ec4899; font-size: 2.5rem; margin-bottom: 0.5rem;">
            <?php echo $stats['applications']; ?>
        </h3>
        <p class="text-muted">Total Applications</p>
    </div>
</div>

<div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Quick Actions -->
    <div class="glass-panel">
        <h3>Quick Actions</h3>
        <div class="flex flex-col gap-4" style="display: flex; flex-direction: column; gap: 1rem;">
            <a href="<?= BASE_URL ?>/?page=admin_users" class="btn btn-outline" style="text-align: left;">Manage Users &rarr;</a>
            <a href="<?= BASE_URL ?>/?page=admin_logs" class="btn btn-outline" style="text-align: left;">View Audit Logs &rarr;</a>
            <a href="<?= BASE_URL ?>/?page=admin_settings" class="btn btn-outline" style="text-align: left;">System Settings &rarr;</a>
            <a href="<?= BASE_URL ?>/?page=jobs" class="btn btn-outline" style="text-align: left;">Manage Jobs (Public View) &rarr;</a>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="glass-panel">
        <h3>Recent Activity</h3>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <?php foreach ($recent_logs as $log): ?>
                <li style="border-bottom: 1px solid #e2e8f0; padding: 0.75rem 0;">
                    <div style="font-weight: 600; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($log['action']); ?>
                        <span class="text-muted" style="font-weight: 400; font-size: 0.8rem;">by
                            <?php echo htmlspecialchars($log['user_name'] ?? 'Unknown'); ?>
                        </span>
                    </div>
                    <div class="text-muted text-sm">
                        <?php echo htmlspecialchars($log['details']); ?>
                    </div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        <?php echo $log['created_at']; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="mt-4" style="margin-top: 1rem; text-align: center;">
            <a href="<?= BASE_URL ?>/?page=admin_logs" class="text-sm">View All Logs</a>
        </div>
    </div>
</div>