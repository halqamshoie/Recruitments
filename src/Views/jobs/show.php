<?php
$currentDate = date('Y-m-d');
$canApply = true;
if (!empty($job['opening_date']) && $job['opening_date'] > $currentDate)
    $canApply = false;
if (!empty($job['closing_date']) && $job['closing_date'] < $currentDate)
    $canApply = false;
?>

<?php if (!$job): ?>
    <div class="glass-panel text-center">
        <h2>Job Not Found</h2>
        <a href="/" class="btn btn-primary">Back to Jobs</a>
    </div>
<?php else: ?>
    <div style="max-width: 900px; margin: 2rem auto;">
        <a href="/" class="btn btn-text" style="margin-bottom: 2rem; color: #64748b; font-weight: 500;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Back to Listings
        </a>

        <div class="glass-panel mb-8" style="padding: 2.5rem;">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 style="color: #1e293b; font-size: 2.5rem; margin-bottom: 0.5rem; line-height: 1.2;">
                        <?php echo htmlspecialchars($job['title'] ?? ''); ?>
                    </h1>
                    <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.5rem;">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'hr'): ?>
                            <span class="tag bg-green" style="font-size: 0.85rem; padding: 0.25rem 0.75rem;">
                                <?php echo htmlspecialchars($job['status'] ?? 'Open'); ?>
                            </span>
                        <?php endif; ?>
                        <span class="text-muted" style="font-size: 0.95rem;">
                            Posted on <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'hr' || $_SESSION['role'] === 'admin')): ?>
                        <a href="/?page=job_edit&id=<?php echo $job['id']; ?>" class="btn btn-outline"
                            style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            Edit Job
                        </a>
                        <a href="/?action=delete_job&id=<?php echo $job['id']; ?>" class="btn btn-outline"
                            style="color: #ef4444; border-color: #ef4444; padding: 0.5rem 1rem; font-size: 0.9rem;"
                            onclick="return confirm('Delete this job?');">
                            Delete
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="job-tags mb-8" style="flex-wrap: wrap; gap: 0.75rem;">
                <span class="tag" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; background: #f1f5f9; color: #334155;">
                    📍 <?php echo htmlspecialchars($job['location'] ?? 'Remote'); ?>
                </span>
                <span class="tag" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; background: #e0f2fe; color: #0369a1;">
                    💼 <?php echo htmlspecialchars($job['type'] ?? 'Full-time'); ?>
                </span>
                <?php if (!empty($job['opening_date']) && !empty($job['closing_date'])): ?>
                    <span class="tag" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; background: #fff1f2; color: #be123c;">
                        📅 Apply by: <?php echo htmlspecialchars($job['closing_date']); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="grid mb-10"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; background: #f8fafc; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0;">
                <div>
                    <strong
                        style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.25rem;">Experience</strong>
                    <span
                        style="font-weight: 500; font-size: 1.05rem;"><?php echo htmlspecialchars($job['experience'] ?? 'Not Specified'); ?></span>
                </div>
                <div>
                    <strong
                        style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.25rem;">Gender</strong>
                    <span
                        style="font-weight: 500; font-size: 1.05rem;"><?php echo htmlspecialchars($job['gender'] ?? 'Male/Female'); ?></span>
                </div>
                <div>
                    <strong
                        style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.25rem;">Vacancies</strong>
                    <span
                        style="font-weight: 500; font-size: 1.05rem;"><?php echo htmlspecialchars($job['vacancies'] ?? '1'); ?></span>
                </div>
                <div>
                    <strong
                        style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.25rem;">Department</strong>
                    <span
                        style="font-weight: 500; font-size: 1.05rem;"><?php echo htmlspecialchars($job['department'] ?? 'Not Specified'); ?></span>
                </div>
            </div>

            <div class="mb-10">
                <h3
                    style="font-size: 1.5rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                    Job Description</h3>
                <div style="font-size: 1.05rem; line-height: 1.7; color: #334155;">
                    <?php echo $job['description'] ?? ''; ?>
                </div>
            </div>
        </div>

        <?php if (!$canApply && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'hr')): ?>
            <div class="glass-panel text-center bg-red" style="padding: 2rem;">
                <h3>Applications Closed</h3>
                <p>This position is not currently accepting applications.</p>
                <p class="text-sm">Application window: <?php echo htmlspecialchars($job['opening_date']); ?> to
                    <?php echo htmlspecialchars($job['closing_date']); ?>
                </p>
            </div>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <div class="glass-panel text-center">
                <h3>Interested in this role?</h3>
                <p class="mb-4">Please log in to apply for this position.</p>
                <a href="/?page=login" class="btn btn-primary">Login to Apply</a>
            </div>
        <?php elseif ($_SESSION['role'] === 'applicant'): ?>
            <div class="glass-panel">
                <h3>Apply for this Position</h3>
                <form action="/?page=apply&id=<?php echo $job['id']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" required placeholder="+968 ...">
                    </div>

                    <div class="form-group">
                        <label>Upload CV (PDF Only)</label>
                        <input type="file" name="resume" required accept=".pdf"
                            style="padding: 0.5rem; border: 1px dashed #cbd5e1; background: #f8fafc; width: 100%;">
                    </div>

                    <div class="form-group">
                        <label>Qualification Document(s) (PDF or ZIP)</label>
                        <input type="file" name="qualifications[]" multiple accept=".pdf,.zip"
                            style="padding: 0.5rem; border: 1px dashed #cbd5e1; background: #f8fafc; width: 100%;">
                        <small class="text-muted" style="display: block; margin-top: 0.25rem;">You can select multiple
                            files.</small>
                    </div>

                    <div class="form-group"
                        style="display: flex; align-items: start; gap: 0.75rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                        <input type="checkbox" name="certification" id="certification" required
                            style="width: 20px; height: 20px; margin-top: 0.15rem; cursor: pointer; flex-shrink: 0;">
                        <label for="certification"
                            style="font-size: 0.9rem; line-height: 1.5; margin-bottom: 0; color: #475569; font-weight: normal; cursor: pointer;">
                            I certify that all the information provided in this application is true and correct. I understand
                            that any false statement or omission may disqualify me from employment.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Application</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>