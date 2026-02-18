<div class="mb-8 flex justify-between items-center">
    <h1>Manage Users</h1>
    <div>
        <a href="<?= BASE_URL ?>/?page=admin_dashboard" class="btn btn-outline" style="margin-right: 0.5rem;">&larr; Dashboard</a>
        <a href="<?= BASE_URL ?>/?page=admin_user_create" class="btn btn-primary">Add New User</a>
    </div>
</div>

<div class="glass-panel">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                <th style="padding: 1rem;">ID</th>
                <th style="padding: 1rem;">Name</th>
                <th style="padding: 1rem;">Email</th>
                <th style="padding: 1rem;">Role</th>
                <th style="padding: 1rem;">Joined</th>
                <th style="padding: 1rem;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 1rem;">#
                        <?php echo $user['id']; ?>
                    </td>
                    <td style="padding: 1rem; font-weight: 600;">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </td>
                    <td style="padding: 1rem;">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </td>
                    <td style="padding: 1rem;">
                        <span class="tag"
                            style="background: <?php echo $user['role'] === 'admin' ? '#f3e8ff' : ($user['role'] === 'hr' ? '#dbeafe' : '#f1f5f9'); ?>; color: <?php echo $user['role'] === 'admin' ? '#7e22ce' : ($user['role'] === 'hr' ? '#1e40af' : '#475569'); ?>;">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem; color: #64748b; font-size: 0.9rem;">
                        <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                    </td>
                    <td style="padding: 1rem;">
                        <a href="<?= BASE_URL ?>/?page=admin_user_edit&id=<?php echo $user['id']; ?>" class="btn btn-outline"
                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">Edit</a>
                        <?php if ($user['role'] !== 'admin' || $user['id'] !== $_SESSION['user_id']): ?>
                            <a href="<?= BASE_URL ?>/?action=delete_user&id=<?php echo $user['id']; ?>" class="btn btn-outline"
                                style="color: #ef4444; border-color: #ef4444; padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>