<?php
// views/alerts.php – Smart Alerts
if (!defined('BASE_PATH')) die('Access Denied');
$pageTitle = 'Alerts';

// Generate alerts from DB
$allAlerts = [];
try {
    // Low stock alerts
    $lowItems = $pdo->query("SELECT * FROM obat WHERE stok <= stok_min ORDER BY stok ASC LIMIT 30")->fetchAll();
    foreach ($lowItems as $o) {
        $tone = ((int)$o['stok'] <= 0) ? 'critical' : (((int)$o['stok'] <= floor($o['stok_min']/2)) ? 'critical' : 'warning');
        $allAlerts[] = [
            'id' => 'low-' . $o['id'], 'tone' => $tone, 'type' => 'stock',
            'title' => $o['nama'],
            'desc'  => 'Stok: ' . (int)$o['stok'] . ' unit. Minimum: ' . (int)$o['stok_min'] . ' unit.',
            'badge' => ((int)$o['stok'] <= 0) ? 'Habis' : 'Stok Rendah',
            'time'  => 'Sekarang',
        ];
    }

    // Expiry alerts
    $expItems = $pdo->query("SELECT * FROM obat WHERE exp_date IS NOT NULL AND exp_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) ORDER BY exp_date ASC LIMIT 20")->fetchAll();
    foreach ($expItems as $o) {
        $days = max(0, (int)floor((strtotime($o['exp_date']) - time()) / 86400));
        $tone = $days <= 7 ? 'critical' : ($days <= 30 ? 'warning' : 'expiry');
        $allAlerts[] = [
            'id' => 'exp-' . $o['id'], 'tone' => $tone, 'type' => 'expiry',
            'title' => $o['nama'],
            'desc'  => 'Kadaluarsa: ' . date('d M Y', strtotime($o['exp_date'])) . ' (' . $days . ' hari lagi)',
            'badge' => $days <= 7 ? 'Kritis' : ($days <= 30 ? 'Segera' : 'Perhatian'),
            'time'  => date('d M Y', strtotime($o['exp_date'])),
        ];
    }
} catch (Throwable $e) {}

// Demo alerts if empty
if (empty($allAlerts)) {
    $allAlerts = [
        ['id'=>'demo-1','tone'=>'warning','type'=>'stock','title'=>'Ciprofloxacin 500mg','desc'=>'Stok: 15 unit. Minimum: 40 unit.','badge'=>'Stok Rendah','time'=>'Sekarang'],
        ['id'=>'demo-2','tone'=>'critical','type'=>'expiry','title'=>'Metformin 500mg','desc'=>'Kadaluarsa: 25 Mei 2026 (4 hari lagi)','badge'=>'Kritis','time'=>'25 Mei 2026'],
        ['id'=>'demo-3','tone'=>'critical','type'=>'stock','title'=>'Paracetamol 500mg','desc'=>'Stok: 8 unit. Minimum: 100 unit.','badge'=>'Habis','time'=>'Sekarang'],
    ];
}

$totalAlerts = count($allAlerts);
$criticalCount = count(array_filter($allAlerts, fn($a) => $a['tone'] === 'critical'));
$warningCount  = count(array_filter($allAlerts, fn($a) => $a['tone'] === 'warning'));
$expiryCount   = count(array_filter($allAlerts, fn($a) => $a['tone'] === 'expiry' && $a['type'] === 'expiry'));
?>
<script>document.getElementById('notification-count').textContent = '<?= $totalAlerts ?>';</script>

<div class="page-content page-enter">

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card">
            <div class="stat-icon-wrap red"><i class="bi bi-bell-fill"></i></div>
            <div class="stat-value"><?= $totalAlerts ?></div>
            <div class="stat-label">Total Alerts</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap red"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="stat-value"><?= $criticalCount ?></div>
            <div class="stat-label">Critical</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap amber"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value"><?= $warningCount ?></div>
            <div class="stat-label">Warning</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap purple"><i class="bi bi-clock-history"></i></div>
            <div class="stat-value"><?= $expiryCount ?></div>
            <div class="stat-label">Expiring</div>
        </div>
    </div>

    <!-- Alert List Card -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-bell" style="color:var(--red)"></i> Active Alerts</div>
                <div class="card-sub">Semua alert aktif dari sistem monitoring</div>
            </div>
            <div class="flex gap-2">
                <select class="form-ctrl" id="filter-alert-type" style="width:auto" onchange="filterAlerts()" aria-label="Filter alert type">
                    <option value="">Semua</option>
                    <option value="critical">Critical</option>
                    <option value="warning">Warning</option>
                    <option value="expiry">Expiry</option>
                </select>
                <button class="btn btn-secondary btn-sm" onclick="location.reload()" aria-label="Refresh alerts">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>

        <div class="card-body" style="padding-top:0">
            <div id="alert-list" style="display:flex;flex-direction:column;gap:8px;padding-top:16px">
                <?php if (empty($allAlerts)): ?>
                <div class="empty-state">
                    <i class="bi bi-check-circle" style="color:var(--green)"></i>
                    <p>Tidak ada alert aktif. Semua dalam kondisi baik!</p>
                </div>
                <?php endif; ?>

                <?php foreach ($allAlerts as $a): ?>
                <div class="alert-item <?= htmlspecialchars($a['tone']) ?>"
                     id="al-<?= htmlspecialchars($a['id']) ?>"
                     data-tone="<?= htmlspecialchars($a['tone']) ?>"
                     role="alert">
                    <div class="alert-icon" aria-hidden="true">
                        <i class="bi bi-<?= $a['type'] === 'expiry' ? 'clock-history' : 'exclamation-triangle' ?>"></i>
                    </div>
                    <div class="alert-body">
                        <div class="flex items-center gap-8 mb-1">
                            <div class="alert-title"><?= htmlspecialchars($a['title']) ?></div>
                            <span class="badge badge-<?= $a['tone'] === 'critical' ? 'red' : ($a['tone'] === 'warning' ? 'amber' : 'purple') ?>" style="font-size:.65rem">
                                <?= htmlspecialchars($a['badge']) ?>
                            </span>
                        </div>
                        <div class="alert-desc"><?= htmlspecialchars($a['desc']) ?></div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:6px"><?= htmlspecialchars($a['time']) ?></div>
                        <button class="alert-close" onclick="dismissAlert('al-<?= htmlspecialchars($a['id']) ?>')" aria-label="Dismiss alert">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-box-seam" style="color:var(--amber)"></i> Low Stock Items</div>
            </div>
            <div class="card-body" style="padding-top:0">
                <?php
                $lowOnes = array_filter($allAlerts, fn($a) => $a['type'] === 'stock');
                foreach (array_slice($lowOnes, 0, 5) as $a): ?>
                <div class="txn-row">
                    <div class="txn-icon <?= $a['tone'] === 'critical' ? 'out' : '' ?>"
                         style="<?= $a['tone'] === 'warning' ? 'background:var(--amber-light);color:var(--amber)' : '' ?>"
                         aria-hidden="true">
                        <i class="bi bi-exclamation"></i>
                    </div>
                    <div class="txn-info">
                        <div class="txn-name"><?= htmlspecialchars($a['title']) ?></div>
                        <div class="txn-meta"><?= htmlspecialchars($a['desc']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($lowOnes)): ?>
                <div class="empty-state" style="padding:24px"><i class="bi bi-check-circle" style="color:var(--green)"></i><p>Semua stok aman.</p></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-clock-history" style="color:var(--purple)"></i> Expiring Items</div>
            </div>
            <div class="card-body" style="padding-top:0">
                <?php
                $expOnes = array_filter($allAlerts, fn($a) => $a['type'] === 'expiry');
                foreach (array_slice($expOnes, 0, 5) as $a): ?>
                <div class="txn-row">
                    <div class="txn-icon" style="background:var(--purple-light);color:var(--purple)" aria-hidden="true">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="txn-info">
                        <div class="txn-name"><?= htmlspecialchars($a['title']) ?></div>
                        <div class="txn-meta"><?= htmlspecialchars($a['desc']) ?></div>
                    </div>
                    <span class="badge badge-<?= $a['tone'] === 'critical' ? 'red' : 'purple' ?>"><?= htmlspecialchars($a['badge']) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($expOnes)): ?>
                <div class="empty-state" style="padding:24px"><i class="bi bi-check-circle" style="color:var(--green)"></i><p>Tidak ada obat kadaluarsa.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
function dismissAlert(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.transition = 'all .25s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateX(20px)';
    setTimeout(() => el.remove(), 250);
}

function filterAlerts() {
    const type = document.getElementById('filter-alert-type').value;
    document.querySelectorAll('#alert-list .alert-item').forEach(el => {
        el.style.display = (!type || el.dataset.tone === type) ? '' : 'none';
    });
}
</script>