<div class="mb-8 flex justify-between items-center">
    <h1>Edit Job Posting</h1>
    <a href="<?= BASE_URL ?>/?page=job_detail&id=<?php echo $job['id']; ?>" class="btn btn-outline">Cancel</a>
</div>

<div class="glass-panel" style="max-width: 800px; margin: 0 auto;">
    <form action="<?= BASE_URL ?>/?page=job_edit&id=<?php echo $job['id']; ?>" method="POST">
        <div class="mb-4">
            <label class="form-label">Job Title</label>
            <input type="text" name="title" class="form-control"
                value="<?php echo htmlspecialchars($job['title'] ?? ''); ?>" required>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="mb-4">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control"
                    value="<?php echo htmlspecialchars($job['location'] ?? ''); ?>" placeholder="e.g. Muscat, Oman">
            </div>
            <div class="mb-4">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-control">
                    <option value="Male" <?php echo ($job['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($job['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female
                    </option>
                    <option value="Male/Female" <?php echo ($job['gender'] ?? '') === 'Male/Female' ? 'selected' : ''; ?>>
                        Male/Female</option>
                </select>
            </div>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="mb-4">
                <label class="form-label">Opening Date</label>
                <input type="date" name="opening_date" class="form-control"
                    value="<?php echo htmlspecialchars($job['opening_date'] ?? ''); ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Closing Date</label>
                <input type="date" name="closing_date" class="form-control"
                    value="<?php echo htmlspecialchars($job['closing_date'] ?? ''); ?>">
            </div>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <!-- <div class="mb-4">
                <label class="form-label">Experience Required</label>
                <input type="text" name="experience" class="form-control"
                    value="<?php echo htmlspecialchars($job['experience'] ?? ''); ?>" placeholder="e.g. 2-5 years">
            </div> -->
            <div class="mb-4">
                <label class="form-label">No. of vacancies</label>
                <input type="number" name="vacancies" class="form-control" min="1"
                    value="<?php echo htmlspecialchars($job['vacancies'] ?? '1'); ?>">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Department / Section</label>
            <input type="text" name="department" class="form-control"
                value="<?php echo htmlspecialchars($job['department'] ?? ''); ?>"
                placeholder="e.g. Pharmacy, IT, Administration">
        </div>

        <div class="mb-4">
            <label class="form-label">Job Description</label>
            <textarea name="description" id="description" class="form-control"
                rows="10"><?php echo htmlspecialchars($job['description']); ?></textarea>
        </div>

        <?php
        $pdo_settings = Database::connect();
        $stmt_key = $pdo_settings->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt_key->execute(['tinymce_api_key']);
        $tinymceKey = $stmt_key->fetchColumn() ?: 'no-api-key';
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

        <div class="flex justify-end gap-4" style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="submit" name="status" value="draft" class="btn btn-outline">Save as Draft</button>
            <button type="submit" name="status" value="open" class="btn btn-primary">Publish / Save</button>
        </div>
    </form>
</div>