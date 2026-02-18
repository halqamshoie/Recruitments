<div style="max-width: 800px; margin: 0 auto;">
    <h1 class="mb-8">Post a New Job</h1>

    <div class="glass-panel">
        <form action="<?= BASE_URL ?>/?page=job_create" method="POST">
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" name="title" required placeholder="e.g. Senior Medical Researcher">
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="e.g. Muscat, Oman">
                </div>
                <!-- <div class="form-group">
                <label>Experience</label>
                <input type="text" name="experience" class="form-control" placeholder="e.g. 2-5 years">
            </div> -->
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Opening Date</label>
                    <input type="date" name="opening_date">
                </div>
                <div class="form-group">
                    <label>Closing Date</label>
                    <input type="date" name="closing_date">
                </div>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Gender Preference</label>
                    <select name="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Male/Female">Male/Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Number of Vacancies</label>
                    <input type="number" name="vacancies" min="1" value="1" placeholder="e.g. 3">
                </div>
            </div>

            <div class="form-group">
                <label>Department / Section</label>
                <input type="text" name="department" placeholder="e.g. Pharmacy, IT, Administration">
            </div>

            <div class="form-group">
                <label>Job Description</label>
                <textarea name="description" id="description" rows="10"
                    placeholder="Describe the role responsibilities, requirements, qualifications..."></textarea>
            </div>

            <?php
            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute(['tinymce_api_key']);
            $tinymceKey = $stmt->fetchColumn() ?: 'no-api-key';
            ?>
            <script src="https://cdn.tiny.cloud/1/<?php echo htmlspecialchars($tinymceKey); ?>/tinymce/6/tinymce.min.js"
                referrerpolicy="origin"></script>
            <script>
                tinymce.init({
                    selector: '#description',
                    height: 350,
                    menubar: false,
                    plugins: 'lists link paste',
                    toolbar: 'bold italic underline | bullist numlist | link | removeformat',
                    paste_as_text: false,
                    paste_retain_style_properties: 'all',
                    content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; }',
                    branding: false
                });
            </script>

            <div class="flex justify-between items-center" style="margin-top: 2rem;">
                <a href="<?= BASE_URL ?>/" class="text-muted">Cancel</a>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="status" value="draft" class="btn btn-outline">Save as Draft</button>
                    <button type="submit" name="status" value="open" class="btn btn-primary">Post Job</button>
                </div>
            </div>
        </form>
    </div>
</div>