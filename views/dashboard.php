<?php
// views/dashboard.php
if (!defined('BASE_PATH')) die('Access Denied');

$user = me();
$stats = $obatModel->getStats();
$critical_stock = $obatModel->getLowStock();

// Ambil aktivitas terakhir (transaksi)
$stmt = $pdo->query("SELECT t.*, o.nama as nama_obat, u.nama as nama_user 
                     FROM transaksi t 
                     JOIN obat o ON t.obat_id = o.id 
                     JOIN users u ON t.user_id = u.id 
                     ORDER BY t.created_at DESC LIMIT 5");
$recent_activity = $stmt->fetchAll();
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';
?>

<main class="ml-0 md:ml-64 min-h-screen transition-all">
<div class="p-4 md:p-8 max-w-7xl mx-auto space-y-8">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-display font-bold text-navy dark:text-white">Halo, <?= htmlspecialchars($user['nama']) ?> 👋</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm md:text-base">Selamat datang di pusat kendali SiMoSoBa.</p>
        </div>
        <div class="px-6 py-3 bg-white dark:bg-dark-800 rounded-2xl border border-slate-100 dark:border-dark-700 shadow-sm flex items-center gap-3">
            <div class="w-2 h-2 bg-emerald rounded-full animate-ping" aria-hidden="true"></div>
            <span class="text-xs font-bold text-navy uppercase tracking-widest"><?= date('l, d F Y') ?></span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <?php 
        $cards = [
            ['Total Obat', $stats['total'], 'bi-capsule', 'text-navy dark:text-white', 'bg-slate-50 dark:bg-slate-800'],
            ['Stok Kritis', $stats['kritis'], 'bi-exclamation-triangle', 'text-amber-500', 'bg-amber-50 dark:bg-amber-500/10'],
            ['Habis', $stats['habis'], 'bi-x-octagon', 'text-rose-500', 'bg-rose-50 dark:bg-rose-500/10'],
            ['Kadaluarsa', $stats['expired'], 'bi-calendar-x', 'text-rose-600', 'bg-rose-100 dark:bg-rose-500/20']
        ];
        foreach ($cards as [$label, $val, $icon, $color, $bg]): ?>
            <div class="bg-white dark:bg-dark-800 p-6 rounded-[32px] shadow-sm border border-slate-100 dark:border-dark-700 flex items-center justify-between hover:shadow-md transition-all group">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1"><?= $label ?></p>
                    <p class="text-3xl font-display font-bold <?= $color ?>"><?= $val ?></p>
                </div>
                <div class="w-12 h-12 <?= $bg ?> rounded-2xl flex items-center justify-center <?= $color ?> text-2xl group-hover:scale-110 transition-transform">
                    <i class="bi <?= $icon ?>" aria-hidden="true"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Monitoring Table (Left) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-dark-800 rounded-[32px] border border-slate-100 dark:border-dark-700 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-50 dark:border-dark-700 flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-xl text-navy dark:text-white">Monitoring Stok Kritis</h2>
                        <p class="text-xs text-slate-400">Daftar obat yang perlu segera dipesan ulang.</p>
                    </div>
                    <a href="index.php?page=stok" class="text-xs font-bold text-emerald hover:underline" aria-label="Lihat semua data stok obat">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[500px]">
                        <thead class="bg-slate-50 dark:bg-dark-900/50 text-[10px] text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-4">Nama Obat</th>
                                <th class="px-8 py-4">Sisa Stok</th>
                                <th class="px-8 py-4">QR Code</th>
                                <th class="px-8 py-4">Prediksi Habis</th>
                            </tr>
                        </thead>
                        <tbody id="stock-tbody" class="divide-y divide-slate-50 dark:divide-dark-700">
                            <?php foreach($critical_stock as $o): 
                                $qr_url = generateQR("OBAT:".$o['id'], "qr_obat_".$o['id']);
                            ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-dark-700 transition group">
                                    <td class="px-8 py-5 font-bold text-navy dark:text-white group-hover:text-emerald transition-colors"><?= htmlspecialchars($o['nama']) ?></td>
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1 bg-rose-50 dark:bg-rose-500/10 text-rose-500 font-bold rounded-lg text-sm"><?= $o['stok'] ?></span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <img src="<?= $qr_url ?>" alt="QR Code untuk obat <?= htmlspecialchars($o['nama']) ?>" class="w-10 h-10 rounded-lg shadow-sm hover:scale-150 transition-transform cursor-zoom-in">
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-emerald font-bold italic text-sm mb-1"><?= $prediksiModel->predictDaysRemaining($o['id'], $o['stok']) ?> Hari</p>
                                        <?php $rek = $prediksiModel->getSeasonalRecommendation($o['kategori'], $o['stok']); 
                                        if($rek !== 'Normal'): ?>
                                            <p class="text-[10px] text-amber-600 bg-amber-50 px-2 py-1 rounded-md inline-block"><?= $rek ?></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($critical_stock)): ?>
                                <tr><td colspan="4" class="p-12 text-center text-slate-400 font-medium">✅ Semua stok dalam kondisi aman.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Activity Feed (Right) -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-dark-800 rounded-[32px] border border-slate-100 dark:border-dark-700 shadow-sm p-8">
                <h2 class="font-bold text-xl text-navy dark:text-white mb-6">Aktivitas Terakhir</h2>
                <div class="space-y-6">
                    <?php foreach($recent_activity as $act): 
                        $is_out = $act['tipe'] === 'keluar' || $act['tipe'] === 'output';
                        $icon = $is_out ? 'bi-box-arrow-up-right' : 'bi-box-arrow-in-down-left';
                        $color = $is_out ? 'text-rose' : 'text-emerald';
                        $bg = $is_out ? 'bg-rose/10' : 'bg-emerald/10';
                    ?>
                        <div class="flex gap-4 relative">
                            <div class="w-10 h-10 <?= $bg ?> <?= $color ?> rounded-xl flex items-center justify-center shrink-0">
                                <i class="bi <?= $icon ?>" aria-hidden="true"></i>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-bold text-navy dark:text-white">
                                    <?= $is_out ? 'Pengeluaran' : 'Pemasukan' ?> <?= htmlspecialchars($act['nama_obat'] ?? 'Obat') ?>
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    <span class="font-bold"><?= $act['jumlah'] ?? 0 ?> unit</span> oleh <?= htmlspecialchars($act['nama_user'] ?? 'System') ?>
                                </p>
                                <p class="text-[10px] text-slate-400 font-medium"><?= date('H:i • d M Y', strtotime($act['created_at'] ?? 'now')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($recent_activity)): ?>
                        <p class="text-center text-slate-400 text-sm py-10">Belum ada aktivitas.</p>
                    <?php endif; ?>
                </div>
                <button class="w-full mt-8 py-3 border-2 border-slate-100 dark:border-dark-700 text-slate-400 text-xs font-bold rounded-2xl hover:border-emerald dark:hover:border-emerald hover:text-emerald dark:hover:text-emerald transition-all" aria-label="Lihat semua riwayat aktivitas">Lihat Semua Riwayat</button>
            </div>
        </div>
    </div>
</div>
</main>
<?php include BASE_PATH . '/includes/footer.php'; ?>
