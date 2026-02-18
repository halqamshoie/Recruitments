<div class="glass-panel" style="max-width: 400px; margin: 2rem auto;">
    <h2 class="text-center">Welcome Back</h2>

    <?php if (isset($error)): ?>
        <div class="bg-red" style="padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/?page=login">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="you@example.com">
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required placeholder="••••••••">
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
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

        <div class="flex justify-between items-center mb-4">
            <label class="flex items-center gap-2" style="cursor: pointer;">
                <input type="checkbox" name="remember" style="width: auto; margin: 0;">
                <span class="text-sm text-muted">Remember me</span>
            </label>
            <a href="<?= BASE_URL ?>/?page=forgot_password" class="text-sm" style="color: var(--primary-color);">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>

    <div class="text-center mt-6">
        <p class="text-sm text-muted">Don't have an account? <a href="<?= BASE_URL ?>/?page=register" style="font-weight: 600;">Sign
                Up</a></p>
    </div>
</div>