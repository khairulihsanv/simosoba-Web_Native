<?php
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';

$all_obat = $obatModel->getAll();
$pageTitle = 'Data Stok Obat';
?>

<main class="ml-0 md:ml-64 min-h-screen">
            <header class="mb-10 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-navy dark:text-white">Manajemen Stok</h1>
                <button onclick="startScanner()" class="px-4 py-2 bg-emerald text-white rounded-xl font-bold flex items-center gap-2 hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald/20" aria-label="Scan QR Code obat">
                    <i class="bi bi-qr-code-scan" aria-hidden="true"></i> Scan QR Obat
                </button>
            </header>

            <!-- Scanner Modal -->
            <div id="scanner-modal" class="hidden fixed inset-0 bg-navy/80 dark:bg-dark-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-6" role="dialog" aria-labelledby="scanner-title" aria-hidden="true">
                <div class="bg-white dark:bg-dark-800 rounded-[32px] p-8 w-full max-w-md relative border border-slate-100 dark:border-dark-700 shadow-2xl">
                    <button onclick="stopScanner()" class="absolute top-6 right-6 text-slate-400 hover:text-navy dark:hover:text-white" aria-label="Tutup dialog scanner"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                    <h3 class="font-bold text-xl mb-6 text-navy dark:text-white" id="scanner-title">Scan Kode Obat</h3>
                    <div id="reader" style="width: 100%; border-radius: 20px; overflow: hidden; background: #fff;"></div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-dark-800 rounded-[32px] shadow-sm border border-slate-100 dark:border-dark-700 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-dark-900/50 text-[10px] text-slate-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-8 py-4">Nama Obat</th>
                            <th class="px-8 py-4">Kategori</th>
                            <th class="px-8 py-4">Stok</th>
                            <th class="px-8 py-4">Harga</th>
                            <?php if (canManageObat()): ?>
                            <th class="px-8 py-4">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-dark-700">
                        <?php foreach($all_obat as $o): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-dark-700 transition">
                            <td class="px-8 py-5 font-bold text-navy dark:text-white"><?= htmlspecialchars($o['nama'] ?? '') ?></td>
                            <td class="px-8 py-5 text-sm text-slate-500 dark:text-slate-400 uppercase"><?= htmlspecialchars($o['kategori'] ?? 'UMUM') ?></td>
                            <td class="px-8 py-5 font-bold text-navy dark:text-white"><?= $o['stok'] ?? 0 ?></td>
                            <td class="px-8 py-5 text-slate-400 dark:text-slate-500">Rp <?= number_format($o['harga'] ?? 0, 0, ',', '.') ?></td>
                            <?php if (canManageObat()): ?>
                            <td class="px-8 py-5">
                                <a href="#" class="text-emerald hover:underline font-bold" aria-label="Edit obat <?= htmlspecialchars($o['nama'] ?? '') ?>">Edit</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode;

        function startScanner() {
            const modal = document.getElementById('scanner-modal');
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" }, 
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText, decodedResult) => {
                    alert("Obat Terdeteksi: " + decodedText);
                    stopScanner();
                },
                (errorMessage) => { /* scanning... */ }
            );
        }

        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    const modal = document.getElementById('scanner-modal');
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                }).catch(err => console.error(err));
            } else {
                const modal = document.getElementById('scanner-modal');
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
            }
        }
    </script>
</main>
<?php include BASE_PATH . '/includes/footer.php'; ?>
