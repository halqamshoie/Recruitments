<?php
$pdo_s = Database::connect();
$stmtS = $pdo_s->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
$stmtS->execute(['email_enabled']);
$emailEnabled = $stmtS->fetchColumn() === '1';
$stmtS->execute(['notify_shortlisted']);
$shortlistEmailOn = $emailEnabled && ($stmtS->fetchColumn() === '1');
?>
<div class="mb-8 flex justify-between items-center">
    <h1>HR Dashboard</h1>
    <a href="/?page=job_create" class="btn btn-primary">Post New Job</a>
</div>

<?php
// Group applications by Job
$applicationsByJob = [];
foreach ($applications as $app) {
    $applicationsByJob[$app['job_title']][] = $app;
}
?>

<?php if (empty($applicationsByJob)): ?>
    <div class="glass-panel text-center">
        <p class="text-muted">No applications received yet.</p>
    </div>
<?php else: ?>
    <?php foreach ($applicationsByJob as $jobTitle => $apps): ?>
        <div class="glass-panel mb-8">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-4">
                    <h3 style="margin: 0; color: #1e293b;"><?php echo htmlspecialchars($jobTitle); ?></h3>
                    <span class="tag" style="background: #e0f2fe; color: #0284c7;"><?php echo count($apps); ?> Applicants</span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="/?action=download_all_cvs&job_title=<?php echo urlencode($jobTitle); ?>" class="btn btn-outline"
                        style="padding: 0.5rem 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; border-color: #cbd5e1; color: #475569;"
                        title="Download All CVs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        CVs & Files
                    </a>
                    <a href="/?action=export_csv&job_title=<?php echo urlencode($jobTitle); ?>" class="btn btn-outline"
                        style="padding: 0.5rem 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; border-color: #cbd5e1; color: #475569;"
                        title="Export to Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="9" y1="3" x2="9" y2="21"></line>
                        </svg>
                        Excel
                    </a>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 0.5rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--glass-border); text-align: left;">
                            <th style="padding: 1rem;">Applicant</th>
                            <th style="padding: 1rem;">Contact</th>
                            <th style="padding: 1rem;">CV</th>
                            <th style="padding: 1rem;">Qualifications</th>
                            <th style="padding: 1rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apps as $app): ?>
                            <tr style="border-bottom: 1px solid #cbd5e1;">
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #1e293b;">
                                        <?php echo htmlspecialchars($app['applicant_name']); ?>
                                    </div>
                                    <div class="text-sm text-muted"><?php echo htmlspecialchars($app['applicant_email']); ?></div>
                                </td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($app['phone'] ?? 'N/A'); ?></td>
                                <td style="padding: 1rem;">
                                    <?php if (!empty($app['resume_path'])): ?>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="/?action=review_cv&id=<?php echo $app['id']; ?>" target="_blank"
                                                class="btn btn-outline" onclick="setTimeout(() => window.location.reload(), 500)"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem; border-radius: 0.25rem;">Review</a>
                                            <a href="<?php echo $app['resume_path']; ?>" download class="btn btn-outline"
                                                title="Download"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem; border-radius: 0.25rem;">
                                                ⬇
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No CV</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php
                                    $qualFiles = json_decode($app['qualification_files'] ?? '[]', true);
                                    if (!empty($qualFiles)): ?>
                                        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                            <?php foreach ($qualFiles as $idx => $filePath): ?>
                                                <a href="<?php echo htmlspecialchars($filePath); ?>" download class="btn btn-outline"
                                                    style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 0.25rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                    📄 File <?php echo $idx + 1; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">None</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <form action="/" method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?php echo $app['id']; ?>">

                                        <!-- Visual Status Indicator -->
                                        <span class="status-dot" style="
                                            height: 8px; width: 8px; border-radius: 50%; display: inline-block;
                                            background: <?php echo $app['status'] === 'shortlisted' ? '#22c55e' : ($app['status'] === 'rejected' ? '#ef4444' : ($app['status'] === 'reviewed' ? '#f59e0b' : '#cbd5e1')); ?>;
                                        "></span>

                                        <select name="status"
                                            onchange="if(this.value === 'shortlisted' && <?php echo $shortlistEmailOn ? 'true' : 'false'; ?>) { if(!confirm('Changing status to Shortlisted will send an email notification. Continue?')) { this.value = '<?php echo $app['status']; ?>'; return; } } this.form.submit();"
                                            style="
                                            padding: 0.25rem 0.5rem; 
                                            border-radius: 0.25rem; 
                                            font-size: 0.875rem; 
                                            cursor: pointer; 
                                            border: 1px solid #cbd5e1;
                                            background: white;
                                            color: #334155;
                                        ">
                                            <option value="pending" <?php echo $app['status'] === 'pending' ? 'selected' : ''; ?>>
                                                Pending</option>
                                            <option value="reviewed" <?php echo $app['status'] === 'reviewed' ? 'selected' : ''; ?>>
                                                Reviewed</option>
                                            <option value="shortlisted" <?php echo $app['status'] === 'shortlisted' ? 'selected' : ''; ?>>Shortlisted
                                            </option>
                                            <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>
                                                Reject</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>