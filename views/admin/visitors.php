<?php $title = 'Visitor Analytics'; include_admin_header($title); ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="glass-card" style="margin-bottom: 2rem;">
    <div class="admin-header-flex">
        <div>
            <h1>Visitor Analytics</h1>
            <p class="text-muted-small">Real-time and daily traffic statistics</p>
        </div>
        
        <!-- Cleanup Component -->
        <form action="/admin/visitors/cleanup" method="POST" onsubmit="return confirm('Are you sure you want to delete old logs? This action cannot be undone.')">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div style="display: flex; background: rgba(0,0,0,0.2); padding: 4px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                <select name="period" class="form-select form-select-sm" style="background: transparent !important; border: none !important; width: auto;">
                    <option value="3" style="background: #1e293b;">Older than 3 Months</option>
                    <option value="6" style="background: #1e293b;">Older than 6 Months</option>
                    <option value="12" style="background: #1e293b;">Older than 12 Months</option>
                </select>
                <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600;">
                    <i class="fa-solid fa-trash-can"></i> Cleanup
                </button>
            </div>
        </form>
    </div>

    <div class="admin-grid" style="margin-bottom: 2rem;">
        <div class="stat-card" style="background: rgba(30, 41, 59, 0.5); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="text-muted-small" style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 5px;">
                <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: inline-block; box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);"></span>
                Live Visitors
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #10b981;"><?= number_format($stats['active']) ?></div>
        </div>
        <div class="stat-card" style="background: rgba(30, 41, 59, 0.5); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="text-muted-small" style="margin-bottom: 0.5rem;">Today</div>
            <div class="text-primary-weight" style="font-size: 2rem;"><?= number_format($stats['today']) ?></div>
        </div>
        <div class="stat-card" style="background: rgba(30, 41, 59, 0.5); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="text-muted-small" style="margin-bottom: 0.5rem;">Yesterday</div>
            <div style="font-size: 2rem; font-weight: 700; color: #f8fafc;"><?= number_format($stats['yesterday']) ?></div>
        </div>
        <div class="stat-card" style="background: rgba(30, 41, 59, 0.5); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="text-muted-small" style="margin-bottom: 0.5rem;">Total Visitors</div>
            <div style="font-size: 2rem; font-weight: 700; color: #f8fafc;"><?= number_format($stats['total']) ?></div>
        </div>
    </div>

    <div style="background: rgba(15, 23, 42, 0.5); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border);">
        <h3 style="margin-bottom: 1.5rem; color: #f8fafc; font-weight: 500;">Traffic Trend (Last 15 Days)</h3>
        <div style="height: 300px; width: 100%;">
            <canvas id="visitorChart"></canvas>
        </div>
    </div>
</div>

<div class="glass-card" style="margin-bottom: 2rem;">
    <div class="admin-header-flex">
        <h1>Recent Access Logs</h1>
        <p class="text-muted-small">Latest 100 detailed access records</p>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover table-striped text-center">
            <thead>
                <tr>
                    <th>Access Time</th>
                    <th>IP Address</th>
                    <th>Country</th>
                    <th>Browser / User Agent</th>
                    <th>Traffic Source</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentLogs as $log): ?>
                <tr>
                    <td>
                        <div class="text-light" style="font-size: 0.875rem;"><?= $log['visit_date'] ?></div>
                        <div class="text-muted-small"><?= $log['visit_time'] ?></div>
                    </td>
                    <td>
                        <span class="badge-role" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; font-family: monospace;">
                            <?= $log['ip_address'] ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <img src="https://flagcdn.com/20x15/<?= strtolower($log['country'] === 'Unknown' ? 'un' : $log['country']) ?>.png" 
                                 onerror="this.src='https://flagcdn.com/20x15/un.png'"
                                 style="border-radius: 2px;">
                            <span class="text-muted-small"><?= $log['country'] ?></span>
                        </div>
                    </td>
                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['user_agent']) ?>">
                        <span class="text-muted-small"><?= htmlspecialchars($log['user_agent']) ?></span>
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['referer'] ?: 'Direct') ?>">
                        <span class="text-muted-small" style="color: #94a3b8;"><?= htmlspecialchars($log['referer'] ?: 'Direct') ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentLogs)): ?>
                <tr>
                    <td colspan="5" style="padding: 4rem 0;">
                        <i class="fas fa-info-circle text-muted" style="display: block; font-size: 2rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p class="text-muted-small">No access logs found.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?= get_pagination($page, $totalPages) ?>
</div>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3 class="admin-section-header">
        <i class="fa-solid fa-users-gear"></i> Access Control
    </h3>
    <form action="/admin/visitors/save-ips" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="admin-grid">
            <div>
                <label class="form-label">
                    <i class="fa-solid fa-check-circle" style="color: #10b981;"></i> Allowed IPs (Whitelist)
                </label>
                <textarea name="allowed_ips" class="form-control" rows="5" placeholder="Enter IP addresses to ALLOW (one per line).&#13;&#10;If empty, all IPs are allowed." style="font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($config['allowed_ips'] ?? '') ?></textarea>
                <small class="text-muted-small" style="margin-top: 0.5rem; display: block;">Only listed IPs will be allowed access.</small>
            </div>
            <div>
                <label class="form-label">
                    <i class="fa-solid fa-ban" style="color: #ef4444;"></i> Blocked IPs (Blacklist)
                </label>
                <textarea name="blocked_ips" class="form-control" rows="5" placeholder="Enter IP addresses to BLOCK (one per line)." style="font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($config['blocked_ips'] ?? '') ?></textarea>
                <small class="text-muted-small" style="margin-top: 0.5rem; display: block;">Listed IPs will be denied access.</small>
            </div>
        </div>
        <div style="margin-top: 2rem; text-align: right;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Save Rules
            </button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#link-visitors').addClass('active');

        const ctx = document.getElementById('visitorChart').getContext('2d');
        const dailyData = <?= json_encode($dailyData) ?>;
        
        // Helper to format date labels
        const labels = dailyData.map(d => {
            const date = new Date(d.visit_date);
            return (date.getMonth() + 1) + '/' + date.getDate();
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '접속자 수',
                    data: dailyData.map(d => d.count),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { color: '#64748b', font: { size: 11 }, stepSize: 1, padding: 10 }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#64748b', font: { size: 11 }, padding: 10 }
                    }
                }
            }
        });
    });
</script>


<?php include_admin_footer(); ?>
