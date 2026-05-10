<?php
// api/dashboard.php
require_once dirname(__DIR__) . '/init.php';
requireLogin();

use Controllers\ObatController;

$obatCtrl = new ObatController();
$data = $obatCtrl->getDashboardStats();

// Shortcut variables untuk UI agar tidak banyak ubah template
$summary = $data['summary'];
$totalObat = $summary['total'];
$stokKritis = $summary['kritis'];
$stokHabis = $summary['habis'];
$expired = $summary['expired'];

$trends = $data['trends'];
$chartLabels = $trends['labels'];
$masukData = $trends['masuk'];
$keluarData = $trends['keluar'];

/** @var mysqli $koneksi */
// Recent Activities tetap menggunakan query langsung untuk fleksibilitas detail
$recentActivities = mysqli_query($koneksi, "
    SELECT t.*, o.nama as obat_nama, u.nama as user_nama 
    FROM transaksi t 
    JOIN obat o ON t.obat_id = o.id 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC LIMIT 6
");

$user = me();
$pageTitle = 'Dashboard Analytics';
?>
<?php include 'includes/header.php'; ?>
<title><?= $pageTitle ?> — SiMoSoBa</title>
<body class="font-sans antialiased">

    <?php include 'includes/sidebar.php'; ?>

    <main class="ml-64 p-8">
        <!-- Top Bar -->
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-2xl font-bold text-navy"><?= $pageTitle ?></h1>
                <p class="text-slate-500 text-sm">Selamat datang kembali, <span class="font-bold text-emerald"><?= explode(' ', $user['nama'])[0] ?></span></p>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 bg-white rounded-xl border border-slate-200 text-sm font-medium text-slate-600 flex items-center gap-2">
                    <i class="bi bi-calendar3"></i>
                    <?= date('d F Y') ?>
                </div>
                <div class="w-10 h-10 bg-emerald/10 text-emerald rounded-xl flex items-center justify-center text-xl cursor-pointer hover:bg-emerald/20 transition-all">
                    <i class="bi bi-bell"></i>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Total -->
            <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-navy/5 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-navy/5 text-navy rounded-2xl flex items-center justify-center text-2xl group-hover:bg-navy group-hover:text-white transition-all">
                        <i class="bi bi-capsule"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Obat</span>
                </div>
                <h3 class="text-3xl font-bold text-navy"><?= $totalObat ?></h3>
                <p class="text-slate-400 text-xs mt-2">Jenis obat terdaftar</p>
            </div>
            <!-- Stok Kritis -->
            <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-amber/5 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber/10 text-amber rounded-2xl flex items-center justify-center text-2xl group-hover:bg-amber group-hover:text-white transition-all">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stok Kritis</span>
                </div>
                <h3 class="text-3xl font-bold text-amber"><?= $stokKritis ?></h3>
                <p class="text-slate-400 text-xs mt-2">Segera lakukan re-stock</p>
            </div>
            <!-- Expired -->
            <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-rose/5 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-rose/10 text-rose rounded-2xl flex items-center justify-center text-2xl group-hover:bg-rose group-hover:text-white transition-all">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Expired</span>
                </div>
                <h3 class="text-3xl font-bold text-rose"><?= $expired ?></h3>
                <p class="text-slate-400 text-xs mt-2">Obat kadaluarsa</p>
            </div>
            <!-- Prediction -->
            <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-emerald/5 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald/10 text-emerald rounded-2xl flex items-center justify-center text-2xl group-hover:bg-emerald group-hover:text-white transition-all">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Prediksi Kebutuhan</span>
                </div>
                <h3 class="text-3xl font-bold text-emerald"><?= $data['prediction_trend'] ?></h3>
                <p class="text-slate-400 text-xs mt-2">Tren bulan depan</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <!-- Line Chart -->
            <div class="lg:col-span-2 bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="font-bold text-navy text-lg">Tren Mutasi Obat</h4>
                        <p class="text-slate-400 text-xs">Perbandingan masuk vs keluar (7 hari terakhir)</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-emerald rounded-full"></div>
                            <span class="text-xs font-medium text-slate-600">Masuk</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-navy rounded-full"></div>
                            <span class="text-xs font-medium text-slate-600">Keluar</span>
                        </div>
                    </div>
                </div>
                <div class="h-[300px]">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
            <!-- Bar Chart (Predictions) -->
            <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
                <h4 class="font-bold text-navy text-lg mb-2">Prediksi Stok</h4>
                <p class="text-slate-400 text-xs mb-8">Estimasi kebutuhan obat mendatang</p>
                <div class="h-[300px]">
                    <canvas id="predictionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activities Table -->
        <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h4 class="font-bold text-navy text-xl">Aktivitas Terakhir</h4>
                <a href="laporan.php" class="text-emerald font-bold text-sm hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-widest border-b border-slate-50">
                            <th class="pb-4 font-bold">Waktu</th>
                            <th class="pb-4 font-bold">Obat</th>
                            <th class="pb-4 font-bold">Tipe</th>
                            <th class="pb-4 font-bold">Jumlah</th>
                            <th class="pb-4 font-bold">Oleh</th>
                            <th class="pb-4 font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while($row = mysqli_fetch_assoc($recentActivities)): ?>
                        <tr class="group hover:bg-slate-50 transition-all">
                            <td class="py-5 text-sm text-slate-500 font-medium">
                                <?= date('d/m, H:i', strtotime($row['created_at'])) ?>
                            </td>
                            <td class="py-5">
                                <span class="text-sm font-bold text-navy group-hover:text-emerald transition-colors"><?= htmlspecialchars($row['obat_nama']) ?></span>
                            </td>
                            <td class="py-5">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= $row['tipe'] == 'masuk' ? 'bg-emerald/10 text-emerald' : 'bg-rose/10 text-rose' ?>">
                                    <?= $row['tipe'] ?>
                                </span>
                            </td>
                            <td class="py-5 text-sm font-bold text-navy"><?= $row['jumlah'] ?></td>
                            <td class="py-5 text-sm text-slate-500"><?= htmlspecialchars($row['user_nama']) ?></td>
                            <td class="py-5">
                                <span class="flex items-center gap-1 text-emerald text-xs font-bold">
                                    <i class="bi bi-check-circle-fill"></i> Selesai
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // --- Line Chart: Tren Mutasi ---
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    {
                        label: 'Masuk',
                        data: <?= json_encode($masukData) ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Keluar',
                        data: <?= json_encode($keluarData) ?>,
                        borderColor: '#1e293b',
                        backgroundColor: 'rgba(30, 41, 59, 0.05)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } }
                }
            }
        });

        // --- Bar Chart: Prediksi Stok ---
        const predCtx = document.getElementById('predictionChart').getContext('2d');
        new Chart(predCtx, {
            type: 'bar',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                datasets: [{
                    data: [450, 520, 480, 610],
                    backgroundColor: '#10b981',
                    borderRadius: 12,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { display: false } },
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } }
                }
            }
        });
    </script>
</body>
</html>
