<?php
// api/dashboard.php - Konten Dashboard (Fragment)
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
    requireLogin();
}

use Controllers\ObatController;

$obatCtrl = new ObatController();
$data = $obatCtrl->getDashboardStats();

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
$recentActivities = mysqli_query($koneksi, "
    SELECT t.*, o.nama as obat_nama, u.nama as user_nama 
    FROM transaksi t 
    JOIN obat o ON t.obat_id = o.id 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC LIMIT 6
");

$user = me();
?>

<!-- Konten Dashboard Tanpa Tag Body/Head -->
<div class="flex items-center justify-between mb-10">
    <div>
        <h1 class="text-2xl font-bold text-navy">Dashboard Analytics</h1>
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
    <div class="lg:col-span-2 bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="h-[300px]"><canvas id="trendsChart"></canvas></div>
    </div>
    <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="h-[300px]"><canvas id="predictionChart"></canvas></div>
    </div>
</div>

<script>
    // Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                { label: 'Masuk', data: <?= json_encode($masukData) ?>, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.4 },
                { label: 'Keluar', data: <?= json_encode($keluarData) ?>, borderColor: '#1e293b', backgroundColor: 'rgba(30, 41, 59, 0.05)', fill: true, tension: 0.4 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Prediction Chart
    const predCtx = document.getElementById('predictionChart').getContext('2d');
    new Chart(predCtx, {
        type: 'bar',
        data: {
            labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
            datasets: [{ data: [450, 520, 480, 610], backgroundColor: '#10b981', borderRadius: 12 }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
