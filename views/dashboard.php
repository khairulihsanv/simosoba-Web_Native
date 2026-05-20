<?php
// views/dashboard.php
if (!defined('BASE_PATH')) die('Access Denied');

$user = me();
$stats = $obatModel->getStats();
$critical_stock = $obatModel->getLowStock();

// Ambil aktivitas terakhir
$stmt = $pdo->query("SELECT t.*, o.nama as nama_obat, u.nama as nama_user 
                     FROM transaksi t 
                     JOIN obat o ON t.obat_id = o.id 
                     JOIN users u ON t.user_id = u.id 
                     ORDER BY t.created_at DESC LIMIT 5");
$recent_activity = $stmt->fetchAll();

// Ambil data obat per kategori untuk chart
$stmt_cat = $pdo->query("SELECT kategori, COUNT(*) as jumlah FROM obat GROUP BY kategori ORDER BY jumlah DESC LIMIT 5");
$by_category = $stmt_cat->fetchAll();

include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';
?>

<main class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <h1 class="topbar-title">Dashboard</h1>
        <div class="topbar-actions">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Cari obat..." id="global-search">
            </div>
            <button class="icon-btn" @click="toggleTheme()" title="Toggle Dark Mode">
                <i class="bi" :class="isDark ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
            </button>
            <div class="notif-btn">
                <i class="bi bi-bell"></i>
                <?php if ($stats['kritis'] > 0): ?>
                <span class="notif-badge"><?= $stats['kritis'] ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="dashboard-body">

        <!-- ===== ACTIVE ALERTS ===== -->
        <?php if (!empty($critical_stock)): ?>
        <div class="section-label">ACTIVE ALERTS</div>
        <div class="alerts-stack" id="alerts-container">
            <?php foreach(array_slice($critical_stock, 0, 3) as $i => $o):
                $rek = $prediksiModel->getSeasonalRecommendation($o['kategori'], $o['stok']);
                $msg = "Stok level kritis pada " . $o['stok'] . " unit. Minimum threshold adalah " . ($o['stok_minimum'] ?? 40) . " unit.";
                if ($rek !== 'Normal') $msg = $rek . " — " . $msg;
            ?>
            <div class="alert-item" id="alert-<?= $i ?>">
                <div class="alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="alert-body">
                    <div class="alert-title"><?= htmlspecialchars($o['nama']) ?></div>
                    <div class="alert-desc"><?= $msg ?></div>
                </div>
                <button class="alert-close" onclick="dismissAlert('alert-<?= $i ?>')">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ===== STATS CARDS ===== -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-capsule"></i></div>
                <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                <div class="stat-label">Total Obat</div>
                <div class="stat-sub">Dalam inventaris</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-currency-dollar"></i></div>
                <div class="stat-value">
                    <?php
                    $total_val = $pdo->query("SELECT SUM(stok * harga) as total FROM obat")->fetchColumn();
                    $tv = $total_val ?? 0;
                    echo $tv >= 1000000 ? 'Rp' . number_format($tv/1000000, 1) . 'jt' : 'Rp' . number_format($tv/1000, 1) . 'k';
                    ?>
                </div>
                <div class="stat-label">Nilai Stok</div>
                <div class="stat-sub">Nilai retail</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-value"><?= ($stats['kritis'] ?? 0) + ($stats['habis'] ?? 0) ?></div>
                <div class="stat-label">Kritis / Habis</div>
                <div class="stat-sub">Perlu perhatian</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-clock-history"></i></div>
                <div class="stat-value"><?= $stats['expired'] ?? 0 ?></div>
                <div class="stat-label">Kadaluarsa</div>
                <div class="stat-sub">Dalam 30 hari</div>
            </div>
        </div>

        <!-- ===== CHARTS ROW ===== -->
        <div class="charts-row">
            <!-- Stock Movement Chart -->
            <div class="card chart-card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Pergerakan Stok</div>
                        <div class="card-sub">7 hari terakhir</div>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>

            <!-- By Category Chart -->
            <div class="card chart-card-sm">
                <div class="card-header">
                    <div>
                        <div class="card-title">Per Kategori</div>
                        <div class="card-sub">Top 5 kategori</div>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ===== RECENT TRANSACTIONS ===== -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Transaksi Terbaru</div>
                <a href="index.php?page=mutasi" class="view-all-btn">Lihat Semua</a>
            </div>
            <div class="transactions-list">
                <?php if (empty($recent_activity)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada transaksi.</p>
                    </div>
                <?php else: ?>
                <?php foreach($recent_activity as $act):
                    $is_out = in_array($act['tipe'] ?? '', ['keluar', 'output']);
                    $harga_total = ($act['jumlah'] ?? 0) * ($act['harga_satuan'] ?? 0);
                ?>
                <div class="txn-row">
                    <div class="txn-icon <?= $is_out ? 'out' : 'in' ?>">
                        <span><?= $is_out ? '−' : '+' ?></span>
                    </div>
                    <div class="txn-info">
                        <div class="txn-name"><?= htmlspecialchars($act['nama_obat'] ?? 'Obat') ?></div>
                        <div class="txn-meta"><?= $is_out ? 'Stock Out' : 'Stock In' ?> · <?= date('Y-m-d', strtotime($act['created_at'] ?? 'now')) ?></div>
                    </div>
                    <div class="txn-right">
                        <span class="txn-qty"><?= $act['jumlah'] ?? 0 ?> unit</span>
                        <?php if ($harga_total > 0): ?>
                        <span class="txn-price">Rp<?= number_format($harga_total, 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /dashboard-body -->
</main>

<style>
/* ============================================
   DASHBOARD LAYOUT - BASE44 STYLE
   ============================================ */
.main-content {
    margin-left: 240px;
    min-height: 100vh;
    background: #f1f5f9;
    display: flex;
    flex-direction: column;
    transition: background 0.3s;
}
.dark .main-content { background: #0f172a; }

/* ---- Top Bar ---- */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 28px;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 30;
    transition: background 0.3s, border-color 0.3s;
}
.dark .topbar { background: #1e293b; border-color: #334155; }

.topbar-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    font-family: 'Poppins', sans-serif;
}
.dark .topbar-title { color: #f8fafc; }

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 14px;
    transition: all 0.2s;
}
.dark .search-box { background: #334155; border-color: #475569; }
.search-box i { color: #94a3b8; font-size: 0.85rem; }
.search-box input {
    background: none;
    border: none;
    outline: none;
    font-size: 0.85rem;
    color: #334155;
    width: 200px;
    font-family: 'Inter', sans-serif;
}
.dark .search-box input { color: #cbd5e1; }
.search-box input::placeholder { color: #94a3b8; }

.icon-btn, .notif-btn {
    width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    color: #64748b;
    position: relative;
    transition: all 0.2s;
}
.dark .icon-btn, .dark .notif-btn { background: #334155; border-color: #475569; color: #94a3b8; }
.icon-btn:hover, .notif-btn:hover { background: #e2e8f0; color: #1e293b; }
.dark .icon-btn:hover, .dark .notif-btn:hover { background: #475569; color: #f8fafc; }

.notif-badge {
    position: absolute;
    top: -4px; right: -4px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 700;
    width: 18px; height: 18px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #fff;
}
.dark .notif-badge { border-color: #1e293b; }

/* ---- Dashboard Body ---- */
.dashboard-body {
    padding: 24px 28px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-width: 1400px;
    width: 100%;
}

/* ---- Section Label ---- */
.section-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: #94a3b8;
    text-transform: uppercase;
    margin-bottom: -8px;
}

/* ---- Alert Items ---- */
.alerts-stack { display: flex; flex-direction: column; gap: 8px; }
.alert-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 14px 16px;
    transition: all 0.3s;
    animation: slideIn 0.3s ease;
}
.dark .alert-item { background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.25); }

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.alert-icon { color: #f59e0b; font-size: 1rem; padding-top: 1px; flex-shrink: 0; }
.alert-body { flex: 1; }
.alert-title { font-weight: 600; font-size: 0.875rem; color: #92400e; }
.dark .alert-title { color: #fbbf24; }
.alert-desc { font-size: 0.78rem; color: #a16207; margin-top: 2px; }
.dark .alert-desc { color: #d97706; }
.alert-close {
    background: none; border: none; cursor: pointer;
    color: #a16207; font-size: 1rem; padding: 0 4px;
    line-height: 1; transition: color 0.2s;
}
.alert-close:hover { color: #92400e; }
.dark .alert-close { color: #fbbf24; }

/* ---- Stats Grid ---- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}
.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px 20px;
    transition: all 0.2s;
    cursor: default;
}
.stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.07); transform: translateY(-1px); }
.dark .stat-card { background: #1e293b; border-color: #334155; }

.stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    margin-bottom: 14px;
}
.stat-icon.blue  { background: #eff6ff; color: #3b82f6; }
.stat-icon.green { background: #f0fdf4; color: #22c55e; }
.stat-icon.amber { background: #fffbeb; color: #f59e0b; }
.stat-icon.red   { background: #fff1f2; color: #f43f5e; }
.dark .stat-icon.blue  { background: rgba(59,130,246,0.12); }
.dark .stat-icon.green { background: rgba(34,197,94,0.12); }
.dark .stat-icon.amber { background: rgba(245,158,11,0.12); }
.dark .stat-icon.red   { background: rgba(244,63,94,0.12); }

.stat-value {
    font-size: 1.85rem;
    font-weight: 700;
    color: #0f172a;
    font-family: 'Poppins', sans-serif;
    line-height: 1;
    margin-bottom: 6px;
}
.dark .stat-value { color: #f8fafc; }
.stat-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 2px;
}
.dark .stat-label { color: #cbd5e1; }
.stat-sub { font-size: 0.75rem; color: #94a3b8; }

/* ---- Charts Row ---- */
.charts-row {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 16px;
}

/* ---- Generic Card ---- */
.card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    transition: background 0.3s, border-color 0.3s;
}
.dark .card { background: #1e293b; border-color: #334155; }

.chart-card { min-height: 260px; }
.chart-card-sm { min-height: 260px; }

.card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 18px 20px 12px;
    border-bottom: 1px solid #f1f5f9;
}
.dark .card-header { border-color: #334155; }
.card-title { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
.dark .card-title { color: #f8fafc; }
.card-sub { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }

.chart-wrap {
    padding: 16px 20px 20px;
    position: relative;
    height: 220px;
}

/* ---- View All ---- */
.view-all-btn {
    font-size: 0.78rem;
    font-weight: 600;
    color: #3b82f6;
    text-decoration: none;
    white-space: nowrap;
    padding-top: 2px;
}
.view-all-btn:hover { text-decoration: underline; }

/* ---- Recent Transactions ---- */
.transactions-list { padding: 4px 0; }
.txn-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.dark .txn-row { border-color: #334155; }
.txn-row:last-child { border-bottom: none; }
.txn-row:hover { background: #f8fafc; }
.dark .txn-row:hover { background: #334155; }

.txn-icon {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    flex-shrink: 0;
}
.txn-icon.in  { background: #f0fdf4; color: #22c55e; }
.txn-icon.out { background: #fff1f2; color: #f43f5e; }
.dark .txn-icon.in  { background: rgba(34,197,94,0.12); }
.dark .txn-icon.out { background: rgba(244,63,94,0.12); }

.txn-info { flex: 1; min-width: 0; }
.txn-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dark .txn-name { color: #f1f5f9; }
.txn-meta { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }

.txn-right { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; }
.txn-qty { font-weight: 700; font-size: 0.875rem; color: #334155; }
.dark .txn-qty { color: #cbd5e1; }
.txn-price { font-size: 0.8rem; color: #64748b; }
.dark .txn-price { color: #94a3b8; }

/* ---- Empty State ---- */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
}
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: 10px; }
.empty-state p { font-size: 0.875rem; }

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1100px) {
    .charts-row { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .main-content { margin-left: 0; }
    .dashboard-body { padding: 16px; }
    .topbar { padding: 14px 16px; }
    .search-box { display: none; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .charts-row { grid-template-columns: 1fr; }
    .topbar-title { font-size: 1.1rem; }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .stat-value { font-size: 1.4rem; }
}
</style>

<script>
// Chart.js - Stock Movement
document.addEventListener('DOMContentLoaded', function() {
    // ---- Warna berdasar tema ----
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
    const labelColor = isDark ? '#64748b' : '#94a3b8';

    // ---- Stock Movement Line Chart ----
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    const days = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
    
    // Data dari PHP (7 hari terakhir — generate dari DB jika ada, fallback ke dummy)
    <?php
    try {
        $stockIn = []; $stockOut = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $in  = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM transaksi WHERE DATE(created_at)='$date' AND tipe IN('masuk','input')")->fetchColumn();
            $out = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM transaksi WHERE DATE(created_at)='$date' AND tipe IN('keluar','output')")->fetchColumn();
            $stockIn[]  = (int)$in;
            $stockOut[] = (int)$out;
        }
    } catch(Exception $e) {
        $stockIn  = [30,80,60,120,200,350,600];
        $stockOut = [10,20,15,40,60,80,50];
    }
    ?>
    const stockInData  = <?= json_encode($stockIn) ?>;
    const stockOutData = <?= json_encode($stockOut) ?>;

    new Chart(stockCtx, {
        type: 'line',
        data: {
            labels: days,
            datasets: [
                {
                    label: 'Masuk',
                    data: stockInData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                    borderWidth: 2.5
                },
                {
                    label: 'Keluar',
                    data: stockOutData,
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244,63,94,0.04)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#f43f5e',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: labelColor,
                        font: { family: 'Inter', size: 11 },
                        boxWidth: 10, boxHeight: 10,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#f8fafc' : '#0f172a',
                    bodyColor:  isDark ? '#94a3b8' : '#64748b',
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 4
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: labelColor, font: { family: 'Inter', size: 11 } }
                },
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: labelColor, font: { family: 'Inter', size: 11 } },
                    beginAtZero: true
                }
            }
        }
    });

    // ---- By Category Horizontal Bar Chart ----
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    <?php
    $catLabels = array_column($by_category, 'kategori');
    $catData   = array_column($by_category, 'jumlah');
    if (empty($catLabels)) {
        $catLabels = ['Antibiotik','Analgesik','Lainnya','Kardio','Respirasi'];
        $catData   = [5, 4, 3, 2, 1];
    }
    $catColors = ['#3b82f6','#22c55e','#f59e0b','#f43f5e','#8b5cf6'];
    ?>
    new Chart(catCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($catLabels) ?>,
            datasets: [{
                data: <?= json_encode($catData) ?>,
                backgroundColor: <?= json_encode(array_slice($catColors, 0, count($catLabels))) ?>,
                borderRadius: 5,
                borderSkipped: false,
                barThickness: 16
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#f8fafc' : '#0f172a',
                    bodyColor:  isDark ? '#94a3b8' : '#64748b',
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 10
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: labelColor, font: { family: 'Inter', size: 11 }, stepSize: 1 }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: labelColor, font: { family: 'Inter', size: 11 } }
                }
            }
        }
    });
});

// Dismiss Alert
function dismissAlert(id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.transition = 'all 0.3s';
        el.style.opacity = '0';
        el.style.transform = 'translateX(16px)';
        setTimeout(() => { el.remove(); }, 300);
    }
}

// Observe dark mode toggle for chart re-render
const observer = new MutationObserver(() => location.reload());
// observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
