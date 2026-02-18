<div class="mb-8 flex justify-between items-center">
    <h1>
        <?php echo isset($user) ? 'Edit User' : 'Create New User'; ?>
    </h1>
    <a href="<?= BASE_URL ?>/?page=admin_users" class="btn btn-outline">Cancel</a>
</div>

<div class="glass-panel" style="max-width: 600px; margin: 0 auto;">
    <form action="<?= BASE_URL ?>/?action=<?php echo isset($user) ? 'update_user&id=' . $user['id'] : 'store_user'; ?>" method="POST">
        <div class="mb-4">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control"
                value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control"
                value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Password
                <?php echo isset($user) ? '(Leave blank to keep current)' : ''; ?>
            </label>
            <input type="password" name="password" class="form-control" <?php echo isset($user) ? '' : 'required'; ?>>
        </div>

        <div class="mb-4">
            <label class="form-label">Role</label>
            <select name="role" class="form-control" required>
                <option value="applicant" <?php echo (isset($user) && $user['role'] === 'applicant') ? 'selected' : ''; ?>>Applicant</option>
                <option value="hr" <?php echo (isset($user) && $user['role'] === 'hr') ? 'selected' : ''; ?>>HR Staff
                </option>
                <option value="admin" <?php echo (isset($user) && $user['role'] === 'admin') ? 'selected' : ''; ?>>Admin
                </option>
            </select>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="btn btn-primary">
                <?php echo isset($user) ? 'Update User' : 'Create User'; ?>
            </button>
        </div>
    </form>
</div>