<div class="mb-8 flex justify-between items-center">
    <h1>Recruitment Analytics</h1>
    <div class="flex gap-2">
        <!-- Export CSV Button -->
        <a href="<?= BASE_URL ?>/?action=export_analytics_report" class="btn btn-outline"
            style="display: flex; align-items: center; gap: 0.5rem; color: #166534; border-color: #166534;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline"
            style="display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Print Report
        </button>
    </div>
</div>

<!-- Key Metrics -->
<div class="grid mb-8" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
    <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
        <h3 class="text-muted text-sm mb-2">Total Applications</h3>
        <div style="font-size: 2.5rem; font-weight: 700; color: #00AAE6;">
            <?php echo $stats['total_applications']; ?>
        </div>
    </div>
    <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
        <h3 class="text-muted text-sm mb-2">Active Jobs</h3>
        <div style="font-size: 2.5rem; font-weight: 700; color: #7A4398;">
            <?php echo $stats['active_jobs']; ?>
        </div>
    </div>
    <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
        <h3 class="text-muted text-sm mb-2">Shortlisted</h3>
        <div style="font-size: 2.5rem; font-weight: 700; color: #22c55e;">
            <?php echo $stats['shortlisted_count']; ?>
        </div>
    </div>
    <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
        <h3 class="text-muted text-sm mb-2">Pending Review</h3>
        <div style="font-size: 2.5rem; font-weight: 700; color: #f59e0b;">
            <?php echo $stats['pending_count']; ?>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid mb-8" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">

    <!-- Application Status Breakdown -->
    <div class="glass-panel">
        <h3 class="mb-4">Application Status</h3>
        <div style="height: 300px;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Top Jobs -->
    <div class="glass-panel">
        <h3 class="mb-4">Top Jobs by Applications</h3>
        <div style="height: 300px;">
            <canvas id="jobsChart"></canvas>
        </div>
    </div>
</div>

<div class="glass-panel mb-8">
    <h3 class="mb-4">Application Trend (Last 30 Days)</h3>
    <div style="height: 300px;">
        <canvas id="trendChart"></canvas>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($status_data)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($status_data)); ?>,
                    backgroundColor: ['#f59e0b', '#00AAE6', '#ef4444', '#22c55e'], // Pending, Reviewed, Rejected, Shortlisted
                    borderWidth: 0
            }]
        },
    options: {
        responsive: true,
            maintainAspectRatio: false,
                plugins: {
            legend: { position: 'bottom' }
        }
    }
    });

    // Jobs Chart
    const jobsCtx = document.getElementById('jobsChart').getContext('2d');
    new Chart(jobsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($top_jobs, 'title')); ?>,
                datasets: [{
                    label: 'Applications',
                    data: <?php echo json_encode(array_column($top_jobs, 'count')); ?>,
                    backgroundColor: '#7A4398',
                    borderRadius: 4
            }]
        },
    options: {
        responsive: true,
            maintainAspectRatio: false,
                indexAxis: 'y',
                    plugins: {
            legend: { display: false }
        }
    }
    });

    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($trend_data, 'date')); ?>,
                datasets: [{
                    label: 'New Applications',
                    data: <?php echo json_encode(array_column($trend_data, 'count')); ?>,
                    borderColor: '#00AAE6',
                    backgroundColor: 'rgba(0, 170, 230, 0.1)',
                    fill: true,
                    tension: 0.4
            }]
        },
    options: {
        responsive: true,
            maintainAspectRatio: false,
                scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
    });
</script>