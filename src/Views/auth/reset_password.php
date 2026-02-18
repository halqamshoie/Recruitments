<div class="auth-container glass-panel" style="max-width: 400px; margin: 4rem auto; padding: 2rem;">
    <h2 class="text-center mb-8">Reset Password</h2>

    <?php if (isset($error)): ?>
        <div
            style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/?action=update_password" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

        <div class="mb-4">
            <label class="form-label">New Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="new_password" class="form-control" required
                    placeholder="Enter new password">
                <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </div>

        <script>
            function togglePassword(id) {
                const input = document.getElementById(id);
                if (input.type === "password") {
                    input.type = "text";
                } else {
                    input.type = "password";
                }
            }
        </script>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Update Password</button>
    </form>
</div>