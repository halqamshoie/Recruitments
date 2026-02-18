</main>
<footer>
    <div class="container text-center">
        <p class="text-muted">&copy;
            <?php echo date('Y'); ?> SQCCCRC. All rights reserved.
        </p>
    </div>
</footer>
<!-- Loader -->
<div id="global-loader" class="loader-overlay" style="display: none;">
    <div class="loader-spinner"></div>
    <div class="loader-text">Processing...</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show loader on all form submissions
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                // simple check to avoid showing if validation fails client-side (if using standardized constraints)
                if (form.checkValidity()) {
                    document.getElementById('global-loader').classList.add('active');
                }
            });
        });
    });

    // Hide loader when navigating back (bfcache)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            document.getElementById('global-loader').classList.remove('active');
        }
    });
</script>
</body>

</html>