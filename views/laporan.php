<?php
// views/laporan.php – Monthly Reports with PDF Export
if (!defined('BASE_PATH')) die('Access Denied');
$pageTitle = 'Reports';

$selMonth = $_GET['bulan'] ?? date('m');
$selYear  = $_GET['tahun'] ?? date('Y');
$monthStart = "$selYear-$selMonth-01";
$monthEnd   = date('Y-m-t', strtotime($monthStart));
$monthLabel = date('F Y', strtotime($monthStart));

// ── Fetch Report Data ────────────────────────────────────────
$all_obat = [];
try { $all_obat = $pdo->query("SELECT * FROM obat ORDER BY nama ASC")->fetchAll(); } catch(Throwable $e) {}

$total_skus   = count($all_obat);
$stock_value  = array_sum(array_map(fn($o) => ($o['stok']??0)*($o['harga']??0), $all_obat));
$low_stock    = count(array_filter($all_obat, fn($o) => ($o['stok']??0) <= ($o['stok_min']??40)));
$expiring_90d = count(array_filter($all_obat, fn($o) => !empty($o['exp_date']) && strtotime($o['exp_date']) <= strtotime('+90 days') && strtotime($o['exp_date']) >= time()));

// Monthly transactions
$monthTxn = []; $monthIn = 0; $monthOut = 0;
try {
    $stmt = $pdo->prepare("SELECT t.*, o.nama AS nama_obat, o.harga FROM transaksi t LEFT JOIN obat o ON t.obat_id=o.id WHERE t.created_at BETWEEN ? AND ? ORDER BY t.created_at DESC");
    $stmt->execute([$monthStart.' 00:00:00', $monthEnd.' 23:59:59']);
    $monthTxn = $stmt->fetchAll();
    foreach ($monthTxn as $t) {
        if (in_array($t['tipe'], ['masuk','input'])) $monthIn += (int)$t['jumlah'];
        else $monthOut += (int)$t['jumlah'];
    }
} catch(Throwable $e) {}

// Category distribution
$categories = [];
foreach ($all_obat as $o) {
    $c = $o['kategori'] ?: 'Other';
    $categories[$c] = ($categories[$c] ?? 0) + 1;
}
if (empty($categories)) $categories = ['Antibiotics'=>4,'Analgesics'=>4,'Vitamins'=>3,'Other'=>2];

// Top 8 products by value
$topProducts = array_filter($all_obat, fn($o) => ($o['stok']??0)*($o['harga']??0) > 0);
usort($topProducts, fn($a,$b) => $b['stok']*$b['harga'] - $a['stok']*$a['harga']);
$topProducts = array_slice($topProducts, 0, 8);

// Format
function fmtRp(float $v): string {
    if ($v >= 1000000000) return 'Rp ' . number_format($v/1000000000,1) . 'B';
    if ($v >= 1000000)    return 'Rp ' . number_format($v/1000000,1) . 'M';
    if ($v >= 1000)       return 'Rp ' . number_format($v/1000,1) . 'K';
    return 'Rp ' . number_format($v, 0);
}
?>
<script>document.getElementById('notification-count').textContent = '<?= $low_stock ?>';</script>

<div class="page-content page-enter">

    <!-- Header -->
    <div class="card" style="padding:16px 20px">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div style="font-family:'Outfit',sans-serif;font-size:1.1rem;font-weight:800">Laporan Inventori Farmasi</div>
                <div style="font-size:.82rem;color:var(--text-muted)">Generated: <?= date('d M Y H:i') ?></div>
            </div>
            <div class="flex gap-2 items-center flex-wrap">
                <!-- Month/Year Selector -->
                <form method="GET" class="flex gap-2" style="align-items:center">
                    <input type="hidden" name="page" value="laporan">
                    <select name="bulan" class="form-ctrl" style="width:auto" aria-label="Select month">
                        <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>" <?= $selMonth == $m ? 'selected' : '' ?>>
                            <?= date('F', mktime(0,0,0,$m,1,2024)) ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                    <select name="tahun" class="form-ctrl" style="width:auto" aria-label="Select year">
                        <?php for ($y=date('Y'); $y>=(date('Y')-3); $y--): ?>
                        <option value="<?= $y ?>" <?= $selYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="bi bi-calendar3"></i> Tampilkan
                    </button>
                </form>

                <button class="btn btn-primary btn-sm" onclick="exportPDF()" id="pdf-btn" aria-label="Export PDF">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </button>
                <button class="btn btn-secondary btn-sm" onclick="window.print()" aria-label="Print report">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>

    <!-- PDF area starts -->
    <div id="pdf-area">

    <!-- Summary Stats -->
    <div class="rpt-stats-row">
        <div class="rpt-stat-card">
            <div class="rpt-stat-label">Nilai Stok</div>
            <div class="rpt-stat-value" style="font-size:1.4rem"><?= fmtRp($stock_value) ?></div>
        </div>
        <div class="rpt-stat-card">
            <div class="rpt-stat-label">Total SKU</div>
            <div class="rpt-stat-value"><?= $total_skus ?></div>
        </div>
        <div class="rpt-stat-card">
            <div class="rpt-stat-label">Stok Masuk (<?= $monthLabel ?>)</div>
            <div class="rpt-stat-value" style="color:var(--green)"><?= number_format($monthIn) ?></div>
        </div>
        <div class="rpt-stat-card">
            <div class="rpt-stat-label">Stok Keluar (<?= $monthLabel ?>)</div>
            <div class="rpt-stat-value" style="color:var(--red)"><?= number_format($monthOut) ?></div>
        </div>
        <div class="rpt-stat-card">
            <div class="rpt-stat-label">Stok Rendah</div>
            <div class="rpt-stat-value" style="color:var(--amber)"><?= $low_stock ?></div>
        </div>
        <div class="rpt-stat-card">
            <div class="rpt-stat-label">Kadaluarsa ≤90 Hari</div>
            <div class="rpt-stat-value" style="color:var(--purple)"><?= $expiring_90d ?></div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <!-- Top Products Bar Chart -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-bar-chart" style="color:var(--accent)"></i> Top Produk by Nilai</div>
                <div class="card-sub">Stok × Harga</div>
            </div>
            <div class="card-body">
                <div class="chart-wrap" style="height:240px">
                    <canvas id="topChart" role="img" aria-label="Top products chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Category Pie Chart -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-pie-chart" style="color:var(--accent)"></i> Distribusi Kategori</div>
                <div class="card-sub">Jumlah SKU per kategori</div>
            </div>
            <div class="card-body">
                <div class="chart-wrap" style="height:240px">
                    <canvas id="catChart" role="img" aria-label="Category distribution chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Transactions Table -->
    <?php if (!empty($monthTxn)): ?>
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-table" style="color:var(--accent)"></i> Transaksi <?= $monthLabel ?></div>
            <span class="badge badge-indigo"><?= count($monthTxn) ?> transaksi</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Obat</th><th>Tipe</th><th>Jumlah</th><th>Nilai</th><th>Keterangan</th><th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthTxn as $t):
                        $isIn = in_array($t['tipe']??'', ['masuk','input']);
                        $nilai = (float)($t['harga']??0) * (int)($t['jumlah']??0);
                    ?>
                    <tr>
                        <td style="font-weight:600"><?= htmlspecialchars($t['nama_obat']??'') ?></td>
                        <td><span class="badge <?= $isIn ? 'badge-green' : 'badge-red' ?>"><?= $isIn ? 'Masuk' : 'Keluar' ?></span></td>
                        <td style="font-weight:700;color:<?= $isIn?'var(--green)':'var(--red)' ?>"><?= $isIn?'+':'-' ?><?= (int)$t['jumlah'] ?></td>
                        <td style="font-size:.8rem"><?= $nilai>0 ? 'Rp '.number_format($nilai,0,',','.') : '-' ?></td>
                        <td style="font-size:.78rem;color:var(--text-muted)"><?= htmlspecialchars($t['keterangan']??'-') ?></td>
                        <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Expiring Items Table -->
    <?php
    $expItems = array_filter($all_obat, fn($o) => !empty($o['exp_date']) && strtotime($o['exp_date']) <= strtotime('+90 days') && strtotime($o['exp_date']) >= time());
    if (!empty($expItems)): ?>
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-clock-history" style="color:var(--purple)"></i> Obat Kadaluarsa ≤90 Hari</div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Nama Obat</th><th>Kategori</th><th>Stok</th><th>Kadaluarsa</th><th>Sisa Hari</th></tr></thead>
                <tbody>
                    <?php foreach ($expItems as $o):
                        $days = max(0, (int)floor((strtotime($o['exp_date']) - time()) / 86400));
                        $urgency = $days <= 7 ? 'badge-red' : ($days <= 30 ? 'badge-amber' : 'badge-purple');
                    ?>
                    <tr>
                        <td style="font-weight:600"><?= htmlspecialchars($o['nama']) ?></td>
                        <td><?= htmlspecialchars($o['kategori']??'') ?></td>
                        <td><?= (int)$o['stok'] ?></td>
                        <td><?= date('d M Y', strtotime($o['exp_date'])) ?></td>
                        <td><span class="badge <?= $urgency ?>"><?= $days ?> hari</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    </div><!-- /pdf-area -->

</div>

<script>
const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const textColor = isDark ? '#64748b' : '#94a3b8';
const gridColor = isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.04)';
const tooltipCfg = {
    backgroundColor: isDark ? '#1a1f2e' : '#fff',
    titleColor: isDark ? '#f1f5f9' : '#0f172a',
    bodyColor: isDark ? '#94a3b8' : '#475569',
    borderColor: isDark ? '#1e2a3a' : '#e2e8f0',
    borderWidth: 1, cornerRadius: 10, padding: 10
};

/* Top Products */
const topLabels = <?= json_encode(array_column($topProducts, 'nama')) ?>;
const topVals   = <?= json_encode(array_map(fn($o) => round($o['stok']*$o['harga']), $topProducts)) ?>;
new Chart(document.getElementById('topChart').getContext('2d'), {
    type: 'bar',
    data: { labels: topLabels, datasets: [{ label: 'Nilai (Rp)', data: topVals, backgroundColor: '#6366f1', borderRadius: 6, barThickness: 20 }] },
    options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { ...tooltipCfg, callbacks: { label: c => 'Rp ' + c.raw.toLocaleString('id-ID') } } },
        scales: { x: { grid: { color: gridColor }, border: { display: false }, ticks: { color: textColor, font:{size:10}, callback: v => 'Rp' + (v/1000).toFixed(0)+'K' } }, y: { grid: { display: false }, border: { display: false }, ticks: { color: textColor, font:{size:10} } } }
    }
});

/* Category Pie */
const catLabels = <?= json_encode(array_keys($categories)) ?>;
const catVals   = <?= json_encode(array_values($categories)) ?>;
const catColors = ['#6366f1','#22c55e','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#ec4899'];
new Chart(document.getElementById('catChart').getContext('2d'), {
    type: 'doughnut',
    data: { labels: catLabels, datasets: [{ data: catVals, backgroundColor: catColors.slice(0,catLabels.length), borderWidth: 2, borderColor: isDark ? '#141826' : '#fff' }] },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right', labels: { color: textColor, font: { size: 11 }, padding: 12 } },
            tooltip: { ...tooltipCfg }
        }
    }
});

/* PDF Export */
function exportPDF() {
    const btn = document.getElementById('pdf-btn');
    const orig = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner"></span> Generating...';
    btn.disabled = true;

    const opt = {
        margin: [8, 6, 8, 6],
        filename: 'SiMoSoBa_Laporan_<?= $monthLabel ?>.pdf',
        image: { type: 'jpeg', quality: 0.97 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['avoid-all', 'css'] }
    };

    if (typeof html2pdf === 'undefined') { window.print(); btn.innerHTML = orig; btn.disabled = false; return; }
    html2pdf().set(opt).from(document.getElementById('pdf-area')).save()
        .then(() => { btn.innerHTML = orig; btn.disabled = false; })
        .catch(() => { window.print(); btn.innerHTML = orig; btn.disabled = false; });
}
</script>