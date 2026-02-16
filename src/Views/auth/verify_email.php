<div class="glass-panel" style="max-width: 440px; margin: 2rem auto; text-align: center;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">📧</div>
    <h2>Verify Your Email</h2>
    <p class="text-muted" style="margin-bottom: 1.5rem;">
        We've sent a 6-digit verification code to<br>
        <strong>
            <?php echo htmlspecialchars($_SESSION['verify_email'] ?? ''); ?>
        </strong>
    </p>

    <?php if (!empty($result['error'])): ?>
        <div
            style="padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
            <?php echo htmlspecialchars($result['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($resend_msg)): ?>
        <div
            style="padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; background: #dcfce7; border: 1px solid #86efac; color: #166534;">
            <?php echo htmlspecialchars($resend_msg); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/?page=verify_email">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: center; gap: 0.5rem;">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="verify-input"
                        id="digit-<?php echo $i; ?>" style="width: 48px; height: 56px; text-align: center; font-size: 1.5rem; font-weight: 700; 
                               border: 2px solid #e2e8f0; border-radius: 0.75rem; outline: none; transition: all 0.2s;
                               background: #f8fafc;"
                        onfocus="this.style.borderColor='#00AAE6'; this.style.background='#fff';"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc';"
                        oninput="handleInput(this, <?php echo $i; ?>)" onkeydown="handleKeydown(event, <?php echo $i; ?>)">
                <?php endfor; ?>
            </div>
            <input type="hidden" name="code" id="full-code">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;" onclick="collectCode()">
            Verify Email
        </button>
    </form>

    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
        <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.5rem;">Didn't receive the code?</p>
        <a href="/?page=resend_code" class="btn btn-outline" style="font-size: 0.9rem;">Resend Code</a>
    </div>

    <div style="margin-top: 1rem;">
        <a href="/?page=register" class="text-muted" style="font-size: 0.85rem;">← Back to Register</a>
    </div>
</div>

<script>
    function handleInput(el, index) {
        el.value = el.value.replace(/[^0-9]/g, '');
        if (el.value.length === 1 && index < 6) {
            document.getElementById('digit-' + (index + 1)).focus();
        }
    }

    function handleKeydown(e, index) {
        if (e.key === 'Backspace' && !e.target.value && index > 1) {
            document.getElementById('digit-' + (index - 1)).focus();
        }
    }

    function collectCode() {
        let code = '';
        for (let i = 1; i <= 6; i++) {
            code += document.getElementById('digit-' + i).value;
        }
        document.getElementById('full-code').value = code;
    }

    // Handle paste
    document.addEventListener('paste', function (e) {
        const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        if (text.length === 6) {
            for (let i = 0; i < 6; i++) {
                document.getElementById('digit-' + (i + 1)).value = text[i];
            }
            document.getElementById('digit-6').focus();
            e.preventDefault();
        }
    });
</script>