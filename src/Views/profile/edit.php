<?php
$title = 'My Profile';
require_once __DIR__ . '/../../../src/Helpers/CountryHelper.php';
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

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'incomplete_profile'): ?>
            <div class="alert alert-warning"
                style="margin-bottom: 2rem; padding: 1rem; border-radius: 0.5rem; background: #fffbeb; color: #92400e; border: 1px solid #fcd34d;">
                <strong>Action Required:</strong> Please complete your profile (Nationality, Place of Work, Phone, Title) before applying for jobs.
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/?action=update_profile" method="POST" enctype="multipart/form-data">

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
                    <label class="form-label">Nationality</label>
                    <select name="nationality" class="form-control" style="padding: 0.85rem;" required>
                        <option value="">Select Nationality</option>
                        <?php foreach (CountryHelper::getCountries() as $country): ?>
                            <option value="<?= $country ?>" <?= ($user['nationality'] ?? '') === $country ? 'selected' : '' ?>>
                                <?= $country ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <select name="phone_code" class="form-control" style="width: 140px; flex-shrink: 0; padding: 0.85rem;" required>
                            <option value="">Code</option>
                            <?php 
                            // CountryHelper already required at top
                            foreach (CountryHelper::getLinkCodes() as $code => $label): ?>
                                <option value="<?= $code ?>" <?= (strpos($user['phone'] ?? '', $code) === 0) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="phone" class="form-control"
                            value="<?php echo htmlspecialchars(preg_replace('/^\+\d+\s?/', '', $user['phone'] ?? '')); ?>" 
                            placeholder="12345678" style="padding: 0.85rem;" required>
                    </div>
                </div>
            </div>

            <div class="grid margin-bottom-2rem"
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <label class="form-label">Job Title / Designation</label>
                    <input type="text" name="title" class="form-control"
                        value="<?php echo htmlspecialchars($user['title'] ?? ''); ?>"
                        placeholder="e.g. Senior Developer" style="padding: 0.85rem;" required>
                </div>
                <div>
                    <label class="form-label">Current Place of Work</label>
                    <input type="text" name="place_of_work" class="form-control"
                        value="<?php echo htmlspecialchars($user['place_of_work'] ?? ''); ?>"
                        placeholder="e.g. Ministry of Health" style="padding: 0.85rem;" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label class="form-label">Gender</label>
                <div style="display: flex; gap: 2rem; padding: 0.5rem 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                        <input type="radio" name="gender" value="Male" <?= ($user['gender'] ?? '') === 'Male' ? 'checked' : '' ?> style="width: 20px; height: 20px;" required> 
                        <span style="font-size: 1.1rem;">Male</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                        <input type="radio" name="gender" value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'checked' : '' ?> style="width: 20px; height: 20px;" required> 
                        <span style="font-size: 1.1rem;">Female</span>
                    </label>
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