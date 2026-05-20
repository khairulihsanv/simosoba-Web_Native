<?php
// views/alerts.php
if (!defined('BASE_PATH')) die('Access Denied');

$pageTitle = 'Alerts';

// Build alerts from real data
$all_obat = $obatModel->getAll();
$alerts = [];

foreach ($all_obat as $o) {
    $stok = $o['stok'] ?? 0;
    $min_stok = $o['stok_min'] ?? ($o['stok_minimum'] ?? 40);
    $nama = $o['nama'] ?? 'Unknown';

    if ($stok <= 0) {
        $alerts[] = [
            'name' => $nama,
            'type' => 'Out of Stock',
            'color' => 'red',
            'icon' => 'bi-exclamation-triangle-fill',
            'desc' => "Stock level is at 0 units. Immediate restocking required.",
            'date' => date('M d, Y - h:i A'),
            'unread' => true,
        ];
    } elseif ($stok <= $min_stok) {
        $alerts[] = [
            'name' => $nama,
            'type' => 'Low Stock',
            'color' => 'amber',
            'icon' => 'bi-arrow-repeat',
            'desc' => "Stock level at {$stok} units. Minimum threshold is {$min_stok} units.",
            'date' => date('M d, Y - h:i A'),
            'unread' => true,
        ];
    }
}

// Add a sample "Expiring Soon" alert for demo purposes
$alerts[] = [
    'name' => 'Metformin 500mg',
    'type' => 'Expiring Soon',
    'color' => 'amber',
    'icon' => 'bi-clock-history',
    'desc' => 'Batch LOT-2024-MF005 expires on May 25, 2026. Only 6 days remaining.',
    'date' => date('M d, Y - h:i A'),
    'unread' => true,
];

// Add a sample "Expired" alert
$alerts[] = [
    'name' => 'Influenza Vaccine',
    'type' => 'Expired',
    'color' => 'red',
    'icon' => 'bi-x-lg',
    'desc' => 'Batch LOT-2024-IV012 expired on May 10, 2026. Remove from active stock.',
    'date' => date('M d, Y - h:i A'),
    'unread' => false,
];

$total_alerts = count($alerts);

include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';
?>

<main class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <h1 class="topbar-title">Alerts</h1>
        <div class="topbar-actions">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search medications..." id="global-search">
            </div>
            <button class="icon-btn" @click="toggleTheme()" title="Toggle Dark Mode">
                <i class="bi" :class="isDark ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
            </button>
            <div class="notif-btn">
                <i class="bi bi-bell"></i>
                <span class="notif-badge"><?= $total_alerts ?></span>
            </div>
        </div>
    </div>

    <div class="alerts-body">
        <!-- Header Row -->
        <div class="al-header">
            <div class="al-count"><?= $total_alerts ?> active alerts</div>
            <button class="btn-outline" onclick="resolveAll()">
                <i class="bi bi-check2-all"></i> Resolve All
            </button>
        </div>

        <!-- Section Label -->
        <div class="section-label">ACTIVE</div>

        <!-- Alert List -->
        <div class="al-list-container">
            <?php if (empty($alerts)): ?>
                <div class="empty-state">
                    <i class="bi bi-check-circle"></i>
                    <p>No active alerts. Everything looks good!</p>
                </div>
            <?php else: ?>
                <?php foreach($alerts as $i => $a): ?>
                <div class="al-row" id="alert-row-<?= $i ?>">
                    <div class="al-left">
                        <div class="al-icon-circle <?= $a['color'] ?>">
                            <i class="bi <?= $a['icon'] ?>"></i>
                        </div>
                        <div class="al-details">
                            <div class="al-title-row">
                                <span class="al-name"><?= htmlspecialchars($a['name']) ?></span>
                                <span class="al-type-badge <?= $a['color'] ?>"><?= $a['type'] ?></span>
                                <?php if ($a['unread']): ?>
                                    <span class="al-unread-dot"></span>
                                <?php endif; ?>
                            </div>
                            <div class="al-desc"><?= htmlspecialchars($a['desc']) ?></div>
                            <div class="al-date"><?= $a['date'] ?></div>
                        </div>
                    </div>
                    
                    <div class="al-actions">
                        <?php if ($a['unread']): ?>
                            <button class="btn-text" onclick="markRead(<?= $i ?>)">Mark Read</button>
                        <?php endif; ?>
                        <button class="btn-resolve" onclick="resolveOne(<?= $i ?>)">
                            <i class="bi bi-check2-circle"></i> Resolve
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div><!-- /alerts-body -->
</main>

<style>
/* ============================================
   ALERTS LAYOUT - BASE44 STYLE
   ============================================ */
.main-content { margin-left: 240px; min-height: 100vh; background: #f1f5f9; display: flex; flex-direction: column; transition: background 0.3s; }
.dark .main-content { background: #0f172a; }

/* ---- Top Bar ---- */
.topbar { display: flex; align-items: center; justify-content: space-between; padding: 18px 28px; background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 30; transition: all 0.3s; }
.dark .topbar { background: #1e293b; border-color: #334155; }
.topbar-title { font-size: 1.35rem; font-weight: 700; color: #0f172a; font-family: 'Poppins', sans-serif; }
.dark .topbar-title { color: #f8fafc; }
.topbar-actions { display: flex; align-items: center; gap: 12px; }
.search-box { display: flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 14px; }
.dark .search-box { background: #334155; border-color: #475569; }
.search-box i { color: #94a3b8; font-size: 0.85rem; }
.search-box input { background: none; border: none; outline: none; font-size: 0.85rem; color: #334155; width: 200px; font-family: 'Inter', sans-serif; }
.dark .search-box input { color: #cbd5e1; }
.icon-btn, .notif-btn { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; cursor: pointer; color: #64748b; position: relative; }
.dark .icon-btn, .dark .notif-btn { background: #334155; border-color: #475569; color: #94a3b8; }
.notif-badge { position: absolute; top: -4px; right: -4px; background: #ef4444; color: white; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; }
.dark .notif-badge { border-color: #1e293b; }

/* ---- Body ---- */
.alerts-body { padding: 24px 28px; max-width: 1200px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 16px; }

/* ---- Header ---- */
.al-header { display: flex; justify-content: space-between; align-items: center; }
.al-count { font-size: 0.875rem; color: #64748b; font-weight: 500; }
.dark .al-count { color: #94a3b8; }

.btn-outline { background: #fff; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; color: #334155; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; font-family: 'Inter', sans-serif; }
.btn-outline:hover { background: #f1f5f9; border-color: #cbd5e1; }
.dark .btn-outline { background: #1e293b; border-color: #334155; color: #f8fafc; }
.dark .btn-outline:hover { background: #334155; }

/* ---- Section Label ---- */
.section-label { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; color: #94a3b8; text-transform: uppercase; }
.dark .section-label { color: #64748b; }

/* ---- Alert List ---- */
.al-list-container { display: flex; flex-direction: column; gap: 0; }

.al-row { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; margin-bottom: 12px; transition: all 0.2s; }
.dark .al-row { background: #1e293b; border-color: #334155; }
.al-row:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
.dark .al-row:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.2); }

/* Resolved animation */
.al-row.resolved {
    opacity: 0;
    transform: translateX(40px);
    max-height: 0;
    padding: 0 24px;
    margin-bottom: 0;
    border: none;
    overflow: hidden;
    transition: all 0.4s ease;
}

.al-left { display: flex; align-items: flex-start; gap: 16px; flex: 3; }

.al-icon-circle { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.al-icon-circle.amber { background: #fff7ed; color: #f59e0b; }
.al-icon-circle.red { background: #fef2f2; color: #ef4444; }
.al-icon-circle.green { background: #f0fdf4; color: #22c55e; }
.dark .al-icon-circle.amber { background: rgba(245,158,11,0.15); color: #fbbf24; }
.dark .al-icon-circle.red { background: rgba(239,68,68,0.15); color: #f87171; }
.dark .al-icon-circle.green { background: rgba(34,197,94,0.15); color: #4ade80; }

.al-details { display: flex; flex-direction: column; gap: 4px; }
.al-title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.al-name { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
.dark .al-name { color: #f8fafc; }

.al-type-badge { padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.03em; }
.al-type-badge.amber { background: #fff7ed; color: #f59e0b; }
.al-type-badge.red { background: #fef2f2; color: #ef4444; }
.dark .al-type-badge.amber { background: rgba(245,158,11,0.1); color: #fbbf24; }
.dark .al-type-badge.red { background: rgba(239,68,68,0.1); color: #f87171; }

.al-unread-dot { width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; flex-shrink: 0; }

.al-desc { font-size: 0.85rem; color: #64748b; line-height: 1.4; }
.dark .al-desc { color: #94a3b8; }

.al-date { font-size: 0.75rem; color: #94a3b8; }
.dark .al-date { color: #64748b; }

.al-actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

.btn-text { background: none; border: none; color: #64748b; font-size: 0.85rem; cursor: pointer; font-weight: 500; transition: color 0.2s; font-family: 'Inter', sans-serif; }
.btn-text:hover { color: #0f172a; }
.dark .btn-text { color: #94a3b8; }
.dark .btn-text:hover { color: #f8fafc; }

.btn-resolve { background: #3b82f6; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.2s; font-family: 'Inter', sans-serif; }
.btn-resolve:hover { background: #2563eb; }

.empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
.empty-state i { font-size: 3rem; display: block; margin-bottom: 12px; color: #22c55e; }
.empty-state p { font-size: 1rem; }

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .main-content { margin-left: 0; }
    .alerts-body { padding: 16px; }
    .topbar { padding: 14px 16px; }
    .search-box { display: none; }
    .al-row { flex-direction: column; align-items: flex-start; gap: 14px; }
    .al-actions { width: 100%; justify-content: flex-end; }
}
</style>

<script>
function resolveOne(idx) {
    const row = document.getElementById('alert-row-' + idx);
    if (row) {
        row.classList.add('resolved');
        setTimeout(() => row.remove(), 500);
    }
}

function markRead(idx) {
    const row = document.getElementById('alert-row-' + idx);
    if (row) {
        const dot = row.querySelector('.al-unread-dot');
        if (dot) dot.remove();
        const btn = row.querySelector('.btn-text');
        if (btn) btn.remove();
    }
}

function resolveAll() {
    const rows = document.querySelectorAll('.al-row');
    rows.forEach((row, i) => {
        setTimeout(() => {
            row.classList.add('resolved');
            setTimeout(() => row.remove(), 500);
        }, i * 100);
    });
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
