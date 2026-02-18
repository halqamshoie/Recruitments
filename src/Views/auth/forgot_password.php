<div class="auth-container glass-panel" style="max-width: 400px; margin: 4rem auto; padding: 2rem;">
    <h2 class="text-center mb-8">Forgot Password</h2>

    <?php if (isset($msg)): ?>
        <div
            style="background: #eff6ff; color: #1e3a8a; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center;">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/?action=send_reset_link" method="POST">
        <div class="mb-4">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required placeholder="Enter your registered email">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Reset Link</button>
    </form>

    <div class="text-center mt-6">
        <a href="<?= BASE_URL ?>/?page=login" class="text-sm">Back to Login</a>
    </div>
</div>