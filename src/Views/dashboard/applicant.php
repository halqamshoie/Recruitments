<h1 class="mb-8" style="color: #1e293b;">My Dashboard</h1>

<div class="glass-panel" style="padding: 2rem;">
    <h3 class="mb-4" style="color: #00AAE6;">My Applications</h3>
    
    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem; 
            background: <?php echo $_GET['msg'] === 'error' ? '#fee2e2' : '#dcfce7'; ?>; 
            color: <?php echo $_GET['msg'] === 'error' ? '#991b1b' : '#166534'; ?>; 
            border: 1px solid <?php echo $_GET['msg'] === 'error' ? '#f87171' : '#86efac'; ?>;">
            <?php 
            if ($_GET['msg'] === 'updated') echo "Application files updated successfully.";
            elseif ($_GET['msg'] === 'file_deleted') echo "File deleted successfully.";
            elseif ($_GET['msg'] === 'expired') echo "Cannot update application: Job application window has closed.";
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($my_applications)): ?>
        <p class="text-muted">You haven't applied to any jobs yet. <a href="<?= BASE_URL ?>/">Browse Jobs</a></p>
    <?php else: ?>
        <div class="job-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php foreach ($my_applications as $app): ?>
                <?php 
                    // Check if job is still open
                    $now = new DateTime();
                    $opening = !empty($app['opening_date']) ? new DateTime($app['opening_date'] . ' 08:00:00') : null;
                    $closing = !empty($app['closing_date']) ? new DateTime($app['closing_date'] . ' 23:59:59') : null;
                    
                    $isOpen = true;
                    if ($opening && $now < $opening) $isOpen = false;
                    if ($closing && $now > $closing) $isOpen = false;
                ?>

                <div class="job-card"
                    style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: auto; display: block;">
                    <div style="margin-bottom: 1rem;">
                        <h4 style="margin: 0 0 0.5rem 0; color: #1e293b; font-size: 1.1rem;">
                            <?php echo htmlspecialchars($app['job_title']); ?>
                        </h4>
                        <p class="text-muted text-sm" style="margin: 0;">Applied on
                            <?php echo date('M j, Y', strtotime($app['created_at'])); ?>
                        </p>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="tag" style="
                            display: inline-block; 
                            background: <?php echo $app['status'] === 'shortlisted' ? '#dcfce7' : ($app['status'] === 'rejected' ? '#fee2e2' : '#f1f5f9'); ?>; 
                            color: <?php echo $app['status'] === 'shortlisted' ? '#166534' : ($app['status'] === 'rejected' ? '#991b1b' : '#475569'); ?>;
                            padding: 0.25rem 0.75rem;
                            border-radius: 9999px;
                            font-size: 0.875rem;
                            font-weight: 500;
                        ">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                        
                        <?php if ($isOpen): ?>
                        <button onclick="document.getElementById('update-app-<?= $app['id'] ?>').style.display = document.getElementById('update-app-<?= $app['id'] ?>').style.display === 'none' ? 'block' : 'none'" 
                            class="btn btn-outline" style="font-size: 0.85rem; padding: 0.25rem 0.75rem;">
                            Update Files
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Update Form (Hidden) -->
                    <div id="update-app-<?= $app['id'] ?>" style="display: none; margin-top: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                        <h5 style="margin-bottom: 0.5rem; font-size: 0.95rem;">Successfully Uploaded:</h5>
                        <ul style="list-style: none; padding: 0; margin-bottom: 1rem; font-size: 0.85rem;">
                            <?php if (!empty($app['resume_path'])): ?>
                                <li style="margin-bottom: 0.25rem;">📄 <a href="<?= BASE_URL . htmlspecialchars($app['resume_path']) ?>" target="_blank">CV (PDF)</a></li>
                            <?php endif; ?>
                            <?php foreach (json_decode($app['qualification_files'] ?? '[]') as $k => $f): ?>
                                <li style="margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                    📎 <a href="<?= BASE_URL . htmlspecialchars($f) ?>" target="_blank">Doc <?= $k+1 ?></a>
                                    <a href="<?= BASE_URL ?>/?action=delete_qualification&id=<?= $app['id'] ?>&file=<?= urlencode($f) ?>" 
                                       onclick="return confirm('Are you sure you want to delete this file?')"
                                       style="color: red; text-decoration: none; font-size: 0.8rem;" title="Delete">
                                       ❌
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <form action="<?= BASE_URL ?>/?action=update_application&id=<?= $app['id'] ?>" method="POST" enctype="multipart/form-data">
                            <div class="form-group" style="margin-bottom: 0.75rem;">
                                <label style="font-size: 0.85rem;">Update CV (PDF)</label>
                                <input type="file" name="resume" accept=".pdf" class="form-control" style="font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label style="font-size: 0.85rem;">Add Documents (PDF/ZIP)</label>
                                <input type="file" name="qualifications[]" multiple accept=".pdf,.zip" class="form-control" style="font-size: 0.85rem;">
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 0.9rem;">Submit Updates</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>