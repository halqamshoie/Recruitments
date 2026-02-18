<div class="glass-panel" style="max-width: 400px; margin: 2rem auto;">
    <h2 class="text-center">Create Account</h2>

    <?php if (isset($error)): ?>
        <div class="bg-red" style="padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/?page=register">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required placeholder="John Doe">
        </div>

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

        <div class="form-group">
            <label>Confirm Password</label>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" required placeholder="••••••••">
                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
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

        <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>

        <div class="text-center" style="margin-top: 1rem;">
            <p class="text-sm text-muted">Already have an account? <a href="<?= BASE_URL ?>/?page=login">Login</a></p>
        </div>
    </form>
</div>