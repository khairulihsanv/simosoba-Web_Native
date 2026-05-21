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

// For the notification badge in the topbar, we'll show the total alert count (or unread count?)
// We'll show the total alert count for now.
$notification_count = $total_alerts;
?>
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

<script>
    // Set the page title
    document.getElementById('page-title').textContent = 'Alerts';
    // Set the notification count
    document.getElementById('notification-count').textContent = <?= $notification_count ?>;
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