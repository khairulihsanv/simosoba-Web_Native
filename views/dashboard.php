<?php
// views/dashboard.php
if (!defined('BASE_PATH')) die('Access Denied');

$stats = $obatModel->getStats();
$critical_stock = $obatModel->getLowStock();
?>
<div class="p-8 max-w-7xl mx-auto space-y-10">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-navy">Analytics Dashboard</h1>
        <div class="text-slate-400 text-sm font-medium"><?= date('l, d F Y') ?></div>
    </div>

    <!-- Stats Cards (KISS) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <?php 
        $cards = [
            ['Total', $stats['total'], 'bi-capsule', 'text-navy'],
            ['Kritis', $stats['kritis'], 'bi-exclamation-triangle', 'text-amber-500'],
            ['Habis', $stats['habis'], 'bi-x-octagon', 'text-rose-500'],
            ['Expired', $stats['expired'], 'bi-calendar-x', 'text-rose-600']
        ];
        foreach ($cards as [$label, $val, $icon, $color]): ?>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= $label ?></p>
                    <p class="text-3xl font-bold <?= $color ?>"><?= $val ?></p>
                </div>
                <div class="text-3xl opacity-20"><i class="bi <?= $icon ?>"></i></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Monitoring Table -->
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center">
            <h2 class="font-bold text-xl text-navy">Monitoring Stok Kritis</h2>
            <div id="ajax-badge" class="hidden px-3 py-1 bg-rose-500 text-white text-[10px] font-bold rounded-full animate-pulse">Update Otomatis</div>
        </div>
        <table class="w-full text-left">
            <thead class="bg-slate-50 text-[10px] text-slate-400 uppercase tracking-widest">
                <tr>
                    <th class="px-8 py-4">Nama Obat</th>
                    <th class="px-8 py-4">Sisa Stok</th>
                    <th class="px-8 py-4">Estimasi Habis (Prediksi)</th>
                </tr>
            </thead>
            <tbody id="stock-tbody" class="divide-y divide-slate-50">
                <?php foreach($critical_stock as $o): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-8 py-5 font-bold text-navy"><?= htmlspecialchars($o['nama']) ?></td>
                        <td class="px-8 py-5 text-rose-500 font-bold"><?= $o['stok'] ?></td>
                        <td class="px-8 py-5 text-emerald font-bold italic">
                            <?= $prediksiModel->predictDaysRemaining($o['id'], $o['stok']) ?> Hari
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($critical_stock)): ?>
                    <tr><td colspan="3" class="p-12 text-center text-slate-400">✅ Semua stok dalam kondisi aman.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Real-time Reminder (AJAX) -->
<script>
    function checkCriticalStock() {
        fetch('actions/check_stock.php')
            .then(response => response.json())
            .then(data => {
                if (data.critical_count > 0) {
                    document.getElementById('ajax-badge').classList.remove('hidden');
                    // Notification Logic could be added here
                } else {
                    document.getElementById('ajax-badge').classList.add('hidden');
                }
            })
            .catch(err => console.error('Gagal memantau stok:', err));
    }

    // Periksa setiap 10 detik
    setInterval(checkCriticalStock, 10000);
</script>
