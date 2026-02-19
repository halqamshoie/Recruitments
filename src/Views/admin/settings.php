<div class="mb-8 flex justify-between items-center">
    <h1>System Settings</h1>
    <a href="<?= BASE_URL ?>/?page=admin_dashboard" class="btn btn-outline">← Back to Dashboard</a>
</div>

<?php if (!empty($success) || (isset($_GET['msg']) && $_GET['msg'] === 'reset_success')): ?>
    <div class="glass-panel mb-4" style="background: #dcfce7; border: 1px solid #86efac; padding: 1rem 1.5rem;">
        <p style="color: #166534; margin: 0;">✅
            <?php 
                echo !empty($success) ? htmlspecialchars($success) : 'All applications and files have been successfully reset.'; 
            ?>
        </p>
    </div>
<?php endif; ?>

<div class="glass-panel" style="max-width: 700px; margin-bottom: 2rem;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
        🔑 API Keys
    </h3>

    <form action="<?= BASE_URL ?>/?page=admin_settings" method="POST">
        <input type="hidden" name="section" value="api_keys">
        <div class="form-group">
            <label>TinyMCE API Key</label>
            <input type="text" name="tinymce_api_key"
                value="<?php echo htmlspecialchars($settings['tinymce_api_key'] ?? ''); ?>"
                placeholder="Enter your TinyMCE API key" style="font-family: monospace; font-size: 0.9rem;">
            <small class="text-muted" style="display: block; margin-top: 0.25rem;">
                Get a free API key at <a href="https://www.tiny.cloud/" target="_blank">tiny.cloud</a>.
                This key is used for the rich text editor in job descriptions.
            </small>
        </div>

        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Save API Keys</button>
        </div>
    </form>
</div>

<div class="glass-panel" style="max-width: 700px;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
        📧 Email Notifications
    </h3>

    <form action="<?= BASE_URL ?>/?page=admin_settings" method="POST">
        <input type="hidden" name="section" value="email_notifications">

        <!-- Master Toggle -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: #f8fafc; border-radius: 0.75rem; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
            <div>
                <strong style="font-size: 1.05rem;">Enable Email Notifications</strong>
                <p class="text-muted" style="margin: 0.25rem 0 0; font-size: 0.85rem;">Master switch — turn off to disable all email notifications.</p>
            </div>
            <label style="position: relative; display: inline-block; width: 52px; height: 28px; cursor: pointer;">
                <input type="checkbox" name="email_enabled" value="1"
                    <?php echo ($settings['email_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>
                    style="opacity: 0; width: 0; height: 0;" onchange="toggleNotificationOptions(this)">
                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo ($settings['email_enabled'] ?? '0') === '1' ? '#00AAE6' : '#cbd5e1'; ?>; transition: 0.3s; border-radius: 28px;" class="slider"></span>
                <span style="position: absolute; height: 22px; width: 22px; left: <?php echo ($settings['email_enabled'] ?? '0') === '1' ? '27px' : '3px'; ?>; bottom: 3px; background: white; transition: 0.3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" class="slider-knob"></span>
            </label>
        </div>

        <!-- Notification Events -->
        <div id="notification-options" style="<?php echo ($settings['email_enabled'] ?? '0') !== '1' ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">
            <p style="font-weight: 600; color: #334155; margin-bottom: 1rem;">Send email notifications when:</p>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 0.5rem; border: 1px solid #e2e8f0; cursor: pointer;">
                    <input type="checkbox" name="notify_new_application" value="1"
                        <?php echo ($settings['notify_new_application'] ?? '0') === '1' ? 'checked' : ''; ?>
                        style="width: 18px; height: 18px; accent-color: #00AAE6;">
                    <div>
                        <strong>New Application Received</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.8rem;">Notify HR when a candidate submits a new application.</p>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 0.5rem; border: 1px solid #e2e8f0; cursor: pointer;">
                    <input type="checkbox" name="notify_status_change" value="1"
                        <?php echo ($settings['notify_status_change'] ?? '0') === '1' ? 'checked' : ''; ?>
                        style="width: 18px; height: 18px; accent-color: #00AAE6;">
                    <div>
                        <strong>Application Status Changed</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.8rem;">Notify applicant when their application is accepted or rejected.</p>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 0.5rem; border: 1px solid #e2e8f0; cursor: pointer;">
                    <input type="checkbox" name="notify_shortlisted" value="1"
                        <?php echo ($settings['notify_shortlisted'] ?? '0') === '1' ? 'checked' : ''; ?>
                        style="width: 18px; height: 18px; accent-color: #00AAE6;">
                    <div>
                        <strong>Applicant Shortlisted</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.8rem;">Send email to applicant when they are shortlisted for a position.</p>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 0.5rem; border: 1px solid #e2e8f0; cursor: pointer;">
                    <input type="checkbox" name="notify_new_job" value="1"
                        <?php echo ($settings['notify_new_job'] ?? '0') === '1' ? 'checked' : ''; ?>
                        style="width: 18px; height: 18px; accent-color: #00AAE6;">
                    <div>
                        <strong>New Job Posted</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.8rem;">Notify all registered applicants when a new job is published.</p>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 0.5rem; border: 1px solid #e2e8f0; cursor: pointer;">
                    <input type="checkbox" name="notify_job_closing" value="1"
                        <?php echo ($settings['notify_job_closing'] ?? '0') === '1' ? 'checked' : ''; ?>
                        style="width: 18px; height: 18px; accent-color: #00AAE6;">
                    <div>
                        <strong>Job Closing Soon</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.8rem;">Remind applicants 3 days before a job posting closes.</p>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 0.5rem; border: 1px solid #e2e8f0; cursor: pointer;">
                    <input type="checkbox" name="notify_interview_scheduled" value="1"
                        <?php echo ($settings['notify_interview_scheduled'] ?? '0') === '1' ? 'checked' : ''; ?>
                        style="width: 18px; height: 18px; accent-color: #00AAE6;">
                    <div>
                        <strong>Interview Scheduled</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.8rem;">Notify applicant when an interview is scheduled.</p>
                    </div>
                </label>
            </div>

            <!-- Notification Email -->
            <div class="form-group" style="margin-top: 1.5rem;">
                <label>HR Notification Email</label>
                <input type="email" name="notification_email"
                    value="<?php echo htmlspecialchars($settings['notification_email'] ?? ''); ?>"
                    placeholder="e.g. hr@sqcccrc.com" style="font-size: 0.9rem;">
                <small class="text-muted" style="display: block; margin-top: 0.25rem;">
                    Email address where HR notifications will be sent.
                </small>
            </div>
        </div>

        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Save Notification Settings</button>
        </div>
    </form>
</div>

<div class="glass-panel" style="max-width: 700px; margin-top: 2rem; border: 1px solid #fee2e2;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #fee2e2; color: #991b1b;">
        ⚠️ Danger Zone
    </h3>

    <div style="padding: 1rem; background: #fef2f2; border-radius: 0.5rem; border: 1px solid #fecaca; margin-bottom: 1.5rem;">
        <h4 style="color: #991b1b; margin-bottom: 0.5rem;">Reset All Applications</h4>
        <p style="color: #7f1d1d; font-size: 0.9rem; margin-bottom: 1rem;">
            This action will <strong>permanently delete</strong> all applications, including:
            <ul style="margin: 0.5rem 0 0.5rem 1.5rem; list-style: disc;">
                <li>All application records in the database.</li>
                <li>All uploaded CVs and qualification documents.</li>
            </ul>
            <strong style="display: block; margin-top: 0.5rem;">This action cannot be undone.</strong>
        </p>
        
        <form action="<?= BASE_URL ?>/?action=reset_applications" method="POST" onsubmit="return confirm('Are you ABSOLUTELY SURE? This will delete ALL applications and files properly. There is no undo.');">
            <button type="submit" class="btn" style="background: #dc2626; color: white; border: none; padding: 0.75rem 1.5rem; font-weight: 600;">
                Delete All Applications
            </button>
        </form>
    </div>
</div>

<script>
function toggleNotificationOptions(checkbox) {
    const options = document.getElementById('notification-options');
    const slider = checkbox.parentElement.querySelector('.slider');
    const knob = checkbox.parentElement.querySelector('.slider-knob');
    if (checkbox.checked) {
        options.style.opacity = '1';
        options.style.pointerEvents = 'auto';
        slider.style.backgroundColor = '#00AAE6';
        knob.style.left = '27px';
    } else {
        options.style.opacity = '0.5';
        options.style.pointerEvents = 'none';
        slider.style.backgroundColor = '#cbd5e1';
        knob.style.left = '3px';
    }
}
</script>