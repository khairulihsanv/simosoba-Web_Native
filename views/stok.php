<?php
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';

$all_obat = $obatModel->getAll();
$pageTitle = 'Data Stok Obat';
?>

<main class="ml-0 md:ml-64 min-h-screen">
            <header class="mb-10 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-navy">Manajemen Stok</h1>
                <button onclick="startScanner()" class="px-4 py-2 bg-emerald text-white rounded-xl font-bold flex items-center gap-2">
                    <i class="bi bi-qr-code-scan"></i> Scan QR Obat
                </button>
            </header>

            <!-- Scanner Modal -->
            <div id="scanner-modal" class="hidden fixed inset-0 bg-navy/80 backdrop-blur-sm z-50 flex items-center justify-center p-6">
                <div class="bg-white rounded-[32px] p-8 w-full max-w-md relative">
                    <button onclick="stopScanner()" class="absolute top-6 right-6 text-slate-400 hover:text-navy"><i class="bi bi-x-lg"></i></button>
                    <h3 class="font-bold text-xl mb-6">Scan Kode Obat</h3>
                    <div id="reader" style="width: 100%; border-radius: 20px; overflow: hidden;"></div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-[10px] text-slate-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-8 py-4">Nama Obat</th>
                            <th class="px-8 py-4">Kategori</th>
                            <th class="px-8 py-4">Stok</th>
                            <th class="px-8 py-4">Harga</th>
                            <th class="px-8 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($all_obat as $o): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-8 py-5 font-bold text-navy"><?= htmlspecialchars($o['nama']) ?></td>
                            <td class="px-8 py-5 text-sm text-slate-500 uppercase"><?= htmlspecialchars($o['kategori']) ?></td>
                            <td class="px-8 py-5 font-bold"><?= $o['stok'] ?></td>
                            <td class="px-8 py-5 text-slate-400">Rp <?= number_format($o['harga'], 0, ',', '.') ?></td>
                            <td class="px-8 py-5">
                                <a href="#" class="text-emerald hover:underline font-bold">Edit</a>
                            </td>
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
            document.getElementById('scanner-modal').classList.remove('hidden');
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
                    document.getElementById('scanner-modal').classList.add('hidden');
                }).catch(err => console.error(err));
            } else {
                document.getElementById('scanner-modal').classList.add('hidden');
            }
        }
    </script>
</main>
<?php include BASE_PATH . '/includes/footer.php'; ?>
