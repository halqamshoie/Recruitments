<?php
$currentDate = date('Y-m-d');
?>
<div class="header-section text-center mb-8">
    <h1 style="font-size: 3rem; margin-bottom: 1rem; color: #1e293b;">
        Find Your Dream Job
    </h1>
    <p class="text-muted" style="font-size: 1.2rem; max-width: 600px; margin: 0 auto;">
        Browse through open positions at SQCCCRC.
    </p>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'hr' || $_SESSION['role'] === 'admin')): ?>
        <div style="margin-top: 1.5rem;">
            <a href="/?page=job_create" class="btn btn-primary">Post New Job</a>
        </div>
    <?php endif; ?>
</div>

<!-- Search Filter -->
<div class="glass-panel mb-8" style="padding: 1.5rem;">
    <form action="/" method="GET" class="flex gap-4" style="flex-wrap: wrap; align-items: center;">
        <input type="hidden" name="page" value="jobs">
        <div style="flex: 2; min-width: 200px;">
            <input type="text" name="q" placeholder="Search by Job Title or Keywords..." class="form-control"
                value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
                style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <input type="text" name="location" placeholder="Location..." class="form-control"
                value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>"
                style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1;">
        </div>

        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'hr' || $_SESSION['role'] === 'admin')): ?>
            <div style="flex: 1; min-width: 150px;">
                <select name="status" class="form-control"
                    style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: white;">
                    <option value="">All Statuses</option>
                    <option value="open" <?php echo (isset($_GET['status']) && $_GET['status'] === 'open') ? 'selected' : ''; ?>>Open</option>
                    <option value="draft" <?php echo (isset($_GET['status']) && $_GET['status'] === 'draft') ? 'selected' : ''; ?>>Draft (Unpublished)</option>
                    <option value="archived" <?php echo (isset($_GET['status']) && $_GET['status'] === 'archived') ? 'selected' : ''; ?>>Archived</option>
                    <option value="closed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'closed') ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary"
            style="height: 42px; display: flex; align-items: center;">Search</button>
        <?php if (!empty($_GET['q']) || !empty($_GET['location']) || !empty($_GET['status'])): ?>
            <a href="/" class="btn btn-outline" style="height: 42px; display: flex; align-items: center;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($jobs)): ?>
    <div class="glass-panel text-center" style="padding: 4rem;">
        <p class="text-muted">No job openings found matching your criteria.</p>
    </div>
<?php else: ?>
    <div class="job-grid">
        <?php foreach ($jobs as $job): ?>
            <?php
            // Skip if current date is outside range (unless HR)
            $isHR = isset($_SESSION['role']) && $_SESSION['role'] === 'hr';
            if (!$isHR) {
                if (!empty($job['opening_date']) && $job['opening_date'] > $currentDate)
                    continue;
                if (!empty($job['closing_date']) && $job['closing_date'] < $currentDate)
                    continue;
            }
            ?>
            <div class="job-card glass-panel"
                style="padding: 0; border-radius: 1rem; border: 1px solid #e2e8f0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">

                <div style="padding: 1.5rem; flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <h3 style="margin: 0; color: #1e293b; font-size: 1.25rem; font-weight: 600;">
                            <?php echo htmlspecialchars($job['title']); ?>
                        </h3>
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'hr' || $_SESSION['role'] === 'admin')): ?>
                            <span style="font-size: 0.7rem; padding: 0.2rem 0.6rem; border-radius: 99px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
                                background: <?php echo match ($job['status']) {
                                    'open' => '#dcfce7',
                                    'draft' => '#f3f4f6',
                                    'archived' => '#f1f5f9',
                                    'closed' => '#fee2e2',
                                    default => '#f3f4f6'
                                }; ?>; 
                                color: <?php echo match ($job['status']) {
                                    'open' => '#166534',
                                    'draft' => '#4b5563',
                                    'archived' => '#64748b',
                                    'closed' => '#991b1b',
                                    default => '#4b5563'
                                }; ?>;">
                                <?php echo strtoupper($job['status']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="job-tags" style="margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem; display: flex;">
                        <span class="tag"
                            style="background: #f1f5f9; color: #475569; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">
                            📍 <?php echo htmlspecialchars($job['location']); ?>
                        </span>
                        <span class="tag"
                            style="background: #e0f2fe; color: #0369a1; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">
                            💼 <?php echo htmlspecialchars($job['type']); ?>
                        </span>
                        <?php if (!empty($job['closing_date'])): ?>
                            <span class="tag"
                                style="background: #fff1f2; color: #e11d48; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">
                                ⏳ Closes: <?php echo htmlspecialchars($job['closing_date']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <p class="text-muted"
                        style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 0; font-size: 0.95rem; line-height: 1.6; color: #64748b;">
                        <?php echo htmlspecialchars(mb_strimwidth(strip_tags($job['description']), 0, 200, '...')); ?>
                    </p>
                </div>

                <!-- Footer / Actions -->
                <div
                    style="background: #f8fafc; padding: 1rem; border-top: 1px solid #e2e8f0; display: flex; gap: 0.5rem; align-items: center; justify-content: space-between;">
                    <!-- Public View -->
                    <a href="/?page=job_detail&id=<?php echo $job['id']; ?>"
                        style="color: #00AAE6; font-weight: 500; text-decoration: none; font-size: 0.95rem;">
                        View Details &rarr;
                    </a>

                    <!-- HR Controls -->
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'hr' || $_SESSION['role'] === 'admin')): ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <!-- Actions Dropdown or Row? Let's do a simple row of icons to save space but make them clean -->

                            <a href="/?page=job_edit&id=<?php echo $job['id']; ?>" title="Edit"
                                style="color: #64748b; padding: 0.25rem; border-radius: 0.25rem; hover:bg-slate-200;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>

                            <!-- Publish/Unpublish -->
                            <a href="/?action=toggle_job_status&id=<?php echo $job['id']; ?>"
                                title="<?php echo $job['status'] === 'open' ? 'Unpublish' : 'Publish'; ?>"
                                style="color: <?php echo $job['status'] === 'open' ? '#f59e0b' : '#22c55e'; ?>; padding: 0.25rem;">
                                <?php if ($job['status'] === 'open'): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                        </path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12s2.545-5 7-5c4.454 0 7 5 7 5s-2.546 5-7 5c-4.455 0-7-5-7-5z"></path>
                                        <path d="M12 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"></path>
                                    </svg>
                                <?php endif; ?>
                            </a>

                            <!-- Duplicate -->
                            <a href="/?action=duplicate_job&id=<?php echo $job['id']; ?>" title="Duplicate"
                                style="color: #64748b; padding: 0.25rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                            </a>

                            <!-- Archive -->
                            <a href="/?action=archive_job&id=<?php echo $job['id']; ?>" title="Archive"
                                style="color: #64748b; padding: 0.25rem;" onclick="return confirm('Archive this job?');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="21 8 21 21 3 21 3 8"></polyline>
                                    <rect x="1" y="3" width="22" height="5"></rect>
                                    <line x1="10" y1="12" x2="14" y2="12"></line>
                                </svg>
                            </a>

                            <!-- Delete -->
                            <a href="/?action=delete_job&id=<?php echo $job['id']; ?>" title="Delete"
                                style="color: #ef4444; padding: 0.25rem;" onclick="return confirm('Permanently delete this job?');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>