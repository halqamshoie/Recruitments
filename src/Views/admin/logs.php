<div class="mb-8 flex justify-between items-center">
    <h1>Audit Logs</h1>
    <a href="<?= BASE_URL ?>/?page=admin_dashboard" class="btn btn-outline">&larr; Dashboard</a>
</div>

<div class="glass-panel">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                <th style="padding: 1rem;">Time</th>
                <th style="padding: 1rem;">User</th>
                <th style="padding: 1rem;">Action</th>
                <th style="padding: 1rem;">Details</th>
                <th style="padding: 1rem;">IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 1rem; color: #64748b; font-size: 0.9rem; white-space: nowrap;">
                        <?php echo $log['created_at']; ?>
                    </td>
                    <td style="padding: 1rem; font-weight: 600;">
                        <?php echo htmlspecialchars($log['user_name'] ?? 'System/Guest'); ?>
                    </td>
                    <td style="padding: 1rem;">
                        <span class="tag" style="background: #f1f5f9; color: #334155;">
                            <?php echo htmlspecialchars($log['action']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem; color: #475569;">
                        <?php echo htmlspecialchars($log['details']); ?>
                    </td>
                    <td style="padding: 1rem; color: #94a3b8; font-size: 0.85rem;">
                        <?php echo htmlspecialchars($log['ip_address']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>