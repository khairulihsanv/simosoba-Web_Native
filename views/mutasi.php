<?php
// views/mutasi.php
if (!defined('BASE_PATH')) die('Access Denied');

// Handle Mutasi (Input/Output)
$msg = '';
$msg_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['obat_id'], $_POST['tipe'], $_POST['jumlah'])) {
    $obat_id = (int)$_POST['obat_id'];
    $tipe = $_POST['tipe'] === 'masuk' ? 'masuk' : 'keluar';
    $jumlah = (int)$_POST['jumlah'];
    $keterangan = $_POST['keterangan'] ?? '';
    
    if ($jumlah > 0) {
        if ($obatModel->updateStock($obat_id, $jumlah, $tipe, $_SESSION['user_id'], $keterangan)) {
            $msg = "✅ Stok berhasil diperbarui!";
            $msg_type = 'success';
        } else {
            $msg = "❌ Gagal memperbarui stok. Pastikan stok mencukupi jika tipe keluar.";
            $msg_type = 'error';
        }
    } else {
        $msg = "❌ Jumlah harus lebih dari 0.";
        $msg_type = 'error';
    }
}

$all_obat = $obatModel->getAll();

include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';
?>

<main class="ml-0 md:ml-64 min-h-screen">
    <div class="p-4 md:p-8 max-w-7xl mx-auto space-y-8">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-display font-bold text-navy">Mutasi Stok Obat</h1>
                <p class="text-slate-500 text-sm">Input obat masuk atau catat obat keluar</p>
            </div>
            <button onclick="startScanner()" class="px-6 py-3 bg-emerald text-white rounded-2xl font-bold flex items-center gap-2 hover:bg-emerald-600 transition-all shadow-lg shadow-emerald/20">
                <i class="bi bi-upc-scan"></i> Scan Barcode / QR
            </button>
        </header>

        <?php if ($msg): ?>
            <div class="p-4 rounded-2xl mb-6 <?= $msg_type === 'success' ? 'bg-emerald/10 text-emerald-600' : 'bg-rose/10 text-rose-600' ?> font-bold">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm p-6 md:p-10 max-w-2xl mx-auto">
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Pilih Obat</label>
                    <select name="obat_id" id="obat_id" class="w-full px-5 py-4 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald transition-all font-medium" onchange="updatePrev(this)" required>
                        <option value="">-- Cari atau Scan Obat --</option>
                        <?php foreach($all_obat as $o): ?>
                            <option value="<?= $o['id'] ?>" data-stok="<?= $o['stok'] ?>" data-sat="<?= $o['satuan'] ?>">
                                <?= htmlspecialchars($o['nama']) ?> (Sisa: <?= $o['stok'] ?> <?= htmlspecialchars($o['satuan']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="prev-box" class="hidden p-4 bg-navy/5 rounded-2xl border border-slate-100 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-500 uppercase">Stok Saat Ini:</span>
                    <span class="text-xl font-display font-bold text-navy" id="prev-val">0</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Tipe Mutasi</label>
                        <select name="tipe" class="w-full px-5 py-4 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald transition-all font-medium" required>
                            <option value="masuk">📥 Obat Masuk (Tambah Stok)</option>
                            <option value="keluar">📤 Obat Keluar (Kurangi Stok)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Jumlah</label>
                        <input type="number" name="jumlah" class="w-full px-5 py-4 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald transition-all font-medium" placeholder="Contoh: 10" min="1" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" class="w-full px-5 py-4 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald transition-all font-medium" placeholder="Contoh: Pembelian baru / Resep pasien">
                </div>

                <button type="submit" class="w-full py-4 bg-navy text-white font-bold rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-navy/10 flex items-center justify-center gap-2 mt-4">
                    <i class="bi bi-save"></i> Proses Mutasi Stok
                </button>
            </form>
        </div>
    </div>

    <!-- Scanner Modal -->
    <div id="scanner-modal" class="hidden fixed inset-0 bg-navy/80 backdrop-blur-sm z-50 flex items-center justify-center p-6">
        <div class="bg-white rounded-[32px] p-8 w-full max-w-md relative">
            <button onclick="stopScanner()" class="absolute top-6 right-6 text-slate-400 hover:text-navy text-xl"><i class="bi bi-x-lg"></i></button>
            <h3 class="font-bold text-xl mb-6 text-navy">Scan Barcode / QR</h3>
            <div id="reader" style="width: 100%; border-radius: 20px; overflow: hidden;" class="mb-4"></div>
            <p class="text-xs text-center text-slate-500">Arahkan kamera ke barcode EAN-13 atau QR Code obat.</p>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function updatePrev(sel) {
            const opt = sel.options[sel.selectedIndex];
            const box = document.getElementById('prev-box');
            const val = document.getElementById('prev-val');
            if(sel.value) {
                val.textContent = opt.dataset.stok + ' ' + opt.dataset.sat;
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
            }
        }

        let html5QrCode;

        function startScanner() {
            document.getElementById('scanner-modal').classList.remove('hidden');
            html5QrCode = new Html5Qrcode("reader");
            
            // Konfigurasi untuk mendukung berbagai format barcode termasuk EAN_13
            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE, Html5QrcodeSupportedFormats.EAN_13, Html5QrcodeSupportedFormats.UPC_A, Html5QrcodeSupportedFormats.CODE_128 ]
            };

            html5QrCode.start(
                { facingMode: "environment" }, 
                config,
                (decodedText, decodedResult) => {
                    stopScanner();
                    
                    // Logic to find the option matching the decoded text
                    // Typically, the barcode is the ID or we can find by name.
                    // For QR generated as "OBAT:1", extract the ID:
                    let idToSelect = decodedText;
                    if(decodedText.startsWith("OBAT:")) {
                        idToSelect = decodedText.split(":")[1];
                    }

                    const selectEl = document.getElementById('obat_id');
                    let found = false;
                    for (let i = 0; i < selectEl.options.length; i++) {
                        if (selectEl.options[i].value === idToSelect || selectEl.options[i].text.includes(decodedText)) {
                            selectEl.selectedIndex = i;
                            updatePrev(selectEl);
                            found = true;
                            break;
                        }
                    }

                    if(!found) {
                        alert("Obat dengan kode " + decodedText + " tidak ditemukan di sistem.");
                    }
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
