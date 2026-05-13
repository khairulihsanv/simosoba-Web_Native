<?php
// views/laporan.php
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';

$bulan = $_GET['bulan'] ?? date('Y-m'); 
$dari = $bulan . '-01'; 
$sampai = date('Y-m-t', strtotime($dari));
$fDivLog = getDivisiFilter('o');

// Stats
$stmt = $pdo->prepare("SELECT COALESCE(SUM(t.jumlah),0) FROM transaksi t JOIN obat o ON t.obat_id=o.id WHERE t.tipe IN ('masuk','input') AND $fDivLog AND t.created_at >= ? AND t.created_at <= ?");
$stmt->execute([$dari, $sampai . ' 23:59:59']);
$tMasuk = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(t.jumlah),0) FROM transaksi t JOIN obat o ON t.obat_id=o.id WHERE t.tipe IN ('keluar','output') AND $fDivLog AND t.created_at >= ? AND t.created_at <= ?");
$stmt->execute([$dari, $sampai . ' 23:59:59']);
$tKeluar = (int)$stmt->fetchColumn();

$fDiv = getDivisiFilter();
$stmt = $pdo->query("SELECT COUNT(*) FROM obat WHERE $fDiv");
$tObat = (int)$stmt->fetchColumn();

// Top 5
$stmt = $pdo->prepare("SELECT o.nama, SUM(t.jumlah) AS total FROM transaksi t JOIN obat o ON t.obat_id=o.id WHERE t.tipe IN ('keluar','output') AND $fDivLog AND t.created_at >= ? AND t.created_at <= ? GROUP BY t.obat_id ORDER BY total DESC LIMIT 5");
$stmt->execute([$dari, $sampai . ' 23:59:59']);
$topObat = $stmt->fetchAll();

// Log
$stmt = $pdo->prepare("SELECT t.*, o.nama AS nm, o.satuan, u.nama AS nu FROM transaksi t JOIN obat o ON t.obat_id=o.id JOIN users u ON t.user_id=u.id WHERE $fDivLog AND t.created_at >= ? AND t.created_at <= ? ORDER BY t.created_at DESC LIMIT 30");
$stmt->execute([$dari, $sampai . ' 23:59:59']);
$logs = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT DATE(t.created_at) as tgl, 
    SUM(CASE WHEN t.tipe IN ('masuk','input') THEN t.jumlah ELSE 0 END) as total_masuk,
    SUM(CASE WHEN t.tipe IN ('keluar','output') THEN t.jumlah ELSE 0 END) as total_keluar
    FROM transaksi t JOIN obat o ON t.obat_id=o.id 
    WHERE $fDivLog AND t.created_at >= ? AND t.created_at <= ? 
    GROUP BY DATE(t.created_at) ORDER BY tgl ASC");
$stmt->execute([$dari, $sampai . ' 23:59:59']);
$chartData = $stmt->fetchAll();

$chartLabels = [];
$chartMasuk = [];
$chartKeluar = [];
foreach($chartData as $row) {
    $chartLabels[] = date('d M', strtotime($row['tgl']));
    $chartMasuk[] = (int)$row['total_masuk'];
    $chartKeluar[] = (int)$row['total_keluar'];
}
?>

<main class="ml-0 md:ml-64 min-h-screen">
    <div class="p-4 md:p-8 max-w-7xl mx-auto space-y-8">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-display font-bold text-navy">Laporan Inventaris</h1>
                <p class="text-slate-500 text-sm">Analisis mutasi stok periode <?= date('F Y', strtotime($dari)) ?></p>
            </div>
            
            <form method="GET" class="flex items-center gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                <input type="hidden" name="page" value="laporan">
                <input type="month" name="bulan" class="bg-transparent border-none text-sm font-bold text-navy focus:ring-0" value="<?= $bulan ?>" max="<?= date('Y-m') ?>" onchange="this.form.submit()">
            </form>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Unit Masuk</p>
                <p class="text-3xl font-display font-bold text-emerald"><?= number_format($tMasuk) ?></p>
            </div>
            <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Unit Keluar</p>
                <p class="text-3xl font-display font-bold text-rose"><?= number_format($tKeluar) ?></p>
            </div>
            <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Jenis Obat</p>
                <p class="text-3xl font-display font-bold text-navy"><?= $tObat ?></p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm w-full">
            <h3 class="font-bold text-navy mb-6">📈 Grafik Mutasi Obat (Masuk vs Keluar)</h3>
            <div class="relative h-[300px] w-full">
                <canvas id="mutasiChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top 5 Chart -->
            <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
                <h3 class="font-bold text-navy mb-6">🏆 Obat Paling Banyak Keluar</h3>
                <?php if(empty($topObat)): ?>
                    <p class="text-center text-slate-400 py-10">Belum ada data keluar.</p>
                <?php else: 
                    $maxV = max(array_column($topObat, 'total')) ?: 1;
                    $colors = ['bg-emerald', 'bg-navy', 'bg-amber', 'bg-rose', 'bg-slate-400'];
                    foreach($topObat as $i => $r): 
                        $pct = round($r['total'] / $maxV * 100);
                ?>
                    <div class="mb-4">
                        <div class="flex justify-between text-xs font-bold mb-2">
                            <span><?= htmlspecialchars($r['nama']) ?></span>
                            <span class="text-slate-400"><?= $r['total'] ?> unit</span>
                        </div>
                        <div class="w-full h-2 bg-slate-50 rounded-full overflow-hidden">
                            <div class="h-full <?= $colors[$i] ?? 'bg-slate-300' ?> transition-all duration-1000" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- Log Transaction -->
            <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-50">
                    <h3 class="font-bold text-navy">🗒️ Log Transaksi Terakhir</h3>
                </div>
                <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($logs as $l): 
                                $is_in = in_array($l['tipe'], ['masuk', 'input']);
                            ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-8 py-4">
                                        <p class="font-bold text-navy"><?= htmlspecialchars($l['nm']) ?></p>
                                        <p class="text-[10px] text-slate-400"><?= date('d/m H:i', strtotime($l['created_at'])) ?></p>
                                    </td>
                                    <td class="px-8 py-4 text-center">
                                        <span class="px-2 py-1 <?= $is_in ? 'bg-emerald/10 text-emerald' : 'bg-rose/10 text-rose' ?> text-[10px] font-bold rounded-lg uppercase">
                                            <?= $is_in ? '+' : '-' ?><?= $l['jumlah'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-4 text-xs text-slate-500 italic"><?= htmlspecialchars($l['nu']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if(empty($logs)): ?>
                        <p class="text-center text-slate-400 py-10">Tidak ada transaksi di periode ini.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('mutasiChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                {
                    label: 'Obat Masuk',
                    data: <?= json_encode($chartMasuk) ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                },
                {
                    label: 'Obat Keluar',
                    data: <?= json_encode($chartKeluar) ?>,
                    backgroundColor: '#e11d48',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
