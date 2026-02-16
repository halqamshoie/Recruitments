<h1 class="mb-8" style="color: #1e293b;">My Dashboard</h1>

<div class="glass-panel" style="padding: 2rem;">
    <h3 class="mb-4" style="color: #00AAE6;">My Applications</h3>
    <?php if (empty($my_applications)): ?>
        <p class="text-muted">You haven't applied to any jobs yet. <a href="/">Browse Jobs</a></p>
    <?php else: ?>
        <div class="job-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php foreach ($my_applications as $app): ?>
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
                    <div>
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
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>