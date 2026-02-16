<?php
$title = 'My Profile';
?>

<div class="container" style="max-width: 800px; margin: 2rem auto;">
    <div class="glass-panel" style="padding: 2.5rem;">
        <h1
            style="color: #1e293b; margin-bottom: 2.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 1.5rem; font-size: 2rem;">
            My Profile
        </h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"
                style="margin-bottom: 2rem; padding: 1rem; border-radius: 0.5rem; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                Profile updated successfully!
            </div>
        <?php endif; ?>

        <form action="/?action=update_profile" method="POST" enctype="multipart/form-data">

            <!-- Avatar Section -->
            <div
                style="display: flex; align-items: center; gap: 2.5rem; margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 1px solid #f1f5f9;">
                <div style="position: relative;">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Profile Avatar"
                            style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                    <?php else: ?>
                        <div
                            style="width: 120px; height: 120px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #94a3b8;">
                            👤
                        </div>
                    <?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <label class="form-label" style="font-size: 1.1rem; margin-bottom: 0.5rem;">Profile Picture</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*" style="padding: 0.75rem;">
                    <p class="text-muted" style="font-size: 0.9rem; margin-top: 0.75rem;">Accepted formats: JPG, PNG.
                        Max size: 2MB.</p>
                </div>
            </div>

            <div class="grid margin-bottom-2rem"
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo htmlspecialchars($user['name']); ?>" required style="padding: 0.85rem;">
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>"
                        disabled style="background: #f8fafc; cursor: not-allowed; padding: 0.85rem;">
                    <p class="text-muted" style="font-size: 0.8rem; margin-top: 0.5rem;">Email cannot be changed.</p>
                </div>
            </div>

            <div class="grid margin-bottom-2rem"
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <label class="form-label">Job Title / Designation</label>
                    <input type="text" name="title" class="form-control"
                        value="<?php echo htmlspecialchars($user['title'] ?? ''); ?>"
                        placeholder="e.g. Senior Developer" style="padding: 0.85rem;">
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+968 1234 5678"
                        style="padding: 0.85rem;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2.5rem;">
                <label class="form-label">Bio / About Me</label>
                <textarea name="bio" class="form-control" rows="5" placeholder="Tell us a bit about yourself..."
                    style="padding: 0.85rem; line-height: 1.6;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <div style="margin-top: 3rem; text-align: right; padding-top: 1.5rem; border-top: 1px solid #f1f5f9;">
                <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2.5rem; font-size: 1.05rem;">Save
                    Changes</button>
            </div>
        </form>
    </div>
</div>