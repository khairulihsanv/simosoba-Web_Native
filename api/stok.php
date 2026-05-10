<?php
require_once 'server/session_handler.php';
session_start();
require_once 'Core/Autoloader.php';
include 'server/koneksi.php';
include 'server/auth.php';
requireLogin();

/** @var mysqli $koneksi */
$user = me(); $fDiv = getDivisiFilter();

$msgs = ['added'=>['ok','✅ Obat berhasil ditambahkan!'],'deleted'=>['ok','✅ Obat dihapus!'],
         'output'=>['ok','✅ Pengeluaran stok berhasil!'],'invalid'=>['err','❌ Data tidak valid!'],
         'db'=>['err','❌ Gagal simpan ke database!'],'stok_kurang'=>['err','❌ Stok tidak cukup!']];
$notif = isset($_GET['success']) ? ($msgs[$_GET['success']]??null) : (isset($_GET['error']) ? ($msgs[$_GET['error']]??null) : null);

$keyword = trim($_GET['cari']??''); $fStatus = $_GET['status']??'semua';
$where = "WHERE $fDiv";
if ($keyword) { $kw = mysqli_real_escape_string($koneksi,$keyword); $where .= " AND (nama LIKE '%$kw%' OR kategori LIKE '%$kw%')"; }
if ($fStatus==='Aman')    $where .= " AND stok>=stok_min AND stok>0";
if ($fStatus==='Menipis') $where .= " AND stok>0 AND stok<stok_min";
if ($fStatus==='Habis')   $where .= " AND stok=0";

$allObat  = mysqli_query($koneksi,"SELECT * FROM obat $where ORDER BY nama ASC");
$selObat  = mysqli_query($koneksi,"SELECT id,nama,stok,satuan FROM obat WHERE $fDiv ORDER BY nama ASC");

$pageTitle = 'Manajemen Data Obat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — SiMoSoBa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { navy: '#1e293b', emerald: '#10b981', softgrey: '#f8fafc', rose: '#e11d48', amber: '#f59e0b' },
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-softgrey font-sans antialiased">

    <?php include 'includes/sidebar.php'; ?>

    <main class="ml-64 p-8">
        <!-- Top Bar -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-navy"><?= $pageTitle ?></h1>
                <p class="text-slate-500 text-sm">Kelola inventaris obat divisi Anda</p>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($notif): ?>
                    <div class="px-4 py-2 <?= $notif[0] == 'ok' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100' ?> border rounded-xl text-sm font-bold flex items-center gap-2 animate-bounce">
                        <?= $notif[1] ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left: Forms -->
            <div class="lg:col-span-4 space-y-8">
                <?php if (canManageObat()): ?>
                <!-- Add New -->
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
                    <h4 class="font-bold text-navy mb-6 flex items-center gap-2">
                        <i class="bi bi-plus-circle text-emerald"></i> Input Obat Baru
                    </h4>
                    <form action="server/prosesObat.php" method="POST" class="space-y-4">
                        <input type="hidden" name="aksi" value="tambah"/>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama Obat</label>
                            <input type="text" name="nama" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="Paracetamol 500mg" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Kategori</label>
                                <select name="kategori" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" required>
                                    <option value="">Pilih</option>
                                    <?php foreach(['Analgesik','Antibiotik','Antasida','Vitamin','Lainnya'] as $k): ?>
                                        <option value="<?= $k ?>"><?= $k ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Satuan</label>
                                <select name="satuan" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all">
                                    <option value="Tablet">Tablet</option>
                                    <option value="Kapsul">Kapsul</option>
                                    <option value="Botol">Botol</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Stok Awal</label>
                                <input type="number" name="stok" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="0" min="0" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Stok Min</label>
                                <input type="number" name="stok_min" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="10" min="1" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Harga Beli</label>
                            <input type="number" name="harga" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="0" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Exp. Date</label>
                            <input type="date" name="exp_date" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <button type="submit" class="w-full py-4 bg-navy text-white font-bold rounded-2xl hover:bg-slate-800 transition-all shadow-lg shadow-navy/10">
                            Simpan Data Obat
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Output Form -->
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
                    <h4 class="font-bold text-navy mb-6 flex items-center gap-2">
                        <i class="bi bi-box-arrow-up-right text-rose"></i> Output Stok
                    </h4>
                    <form action="server/prosesObat.php" method="POST" class="space-y-4">
                        <input type="hidden" name="aksi" value="output"/>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Pilih Obat</label>
                            <select name="obat_id" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" onchange="updatePrev(this)" required>
                                <option value="">-- Pilih Obat --</option>
                                <?php mysqli_data_seek($selObat, 0); while($o=mysqli_fetch_assoc($selObat)): ?>
                                    <option value="<?= $o['id'] ?>" data-stok="<?= $o['stok'] ?>" data-sat="<?= $o['satuan'] ?>">
                                        <?= htmlspecialchars($o['nama']) ?> (<?= $o['stok'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div id="prev-box" class="hidden p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                            <p class="text-xs text-emerald-600 font-bold uppercase">Stok Tersedia</p>
                            <p class="text-xl font-display font-bold text-emerald" id="prev-val">0</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Jumlah Keluar</label>
                            <input type="number" name="jumlah" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="0" min="1" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Keterangan</label>
                            <input type="text" name="keterangan" class="w-full px-4 py-3 bg-softgrey border border-slate-100 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="Tujuan pengeluaran">
                        </div>
                        <button type="submit" class="w-full py-4 bg-rose text-white font-bold rounded-2xl hover:bg-rose-600 transition-all shadow-lg shadow-rose/10">
                            Proses Pengeluaran
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Table -->
            <div class="lg:col-span-8">
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
                    <div class="flex flex-col md:row items-center justify-between mb-8 gap-4">
                        <h4 class="font-bold text-navy text-xl">Daftar Inventaris</h4>
                        <form method="GET" class="flex gap-2">
                            <input type="text" name="cari" class="px-4 py-2 bg-softgrey border border-slate-100 rounded-xl text-sm focus:outline-none focus:border-emerald" placeholder="Cari obat..." value="<?= htmlspecialchars($keyword) ?>">
                            <button class="px-4 py-2 bg-navy text-white rounded-xl text-sm font-bold">Cari</button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-slate-400 text-[10px] uppercase tracking-widest border-b border-slate-50">
                                    <th class="pb-4">Nama Obat</th>
                                    <th class="pb-4 text-center">Stok</th>
                                    <th class="pb-4 text-center">Min.</th>
                                    <th class="pb-4">Exp. Date</th>
                                    <th class="pb-4">Status</th>
                                    <th class="pb-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php while($o=mysqli_fetch_assoc($allObat)): 
                                    $low = $o['stok'] < $o['stok_min'];
                                    $habis = $o['stok'] == 0;
                                ?>
                                <tr class="group hover:bg-slate-50 transition-all">
                                    <td class="py-5">
                                        <p class="font-bold text-navy group-hover:text-emerald transition-colors"><?= htmlspecialchars($o['nama']) ?></p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase"><?= htmlspecialchars($o['kategori']) ?></p>
                                    </td>
                                    <td class="py-5 text-center font-display font-bold text-navy"><?= $o['stok'] ?></td>
                                    <td class="py-5 text-center text-xs text-slate-400"><?= $o['stok_min'] ?></td>
                                    <td class="py-5 text-sm text-slate-500"><?= date('d/m/y', strtotime($o['exp_date'])) ?></td>
                                    <td class="py-5">
                                        <?php if($habis): ?>
                                            <span class="px-3 py-1 bg-rose/10 text-rose text-[10px] font-bold rounded-full uppercase">Habis</span>
                                        <?php elseif($low): ?>
                                            <span class="px-3 py-1 bg-amber/10 text-amber text-[10px] font-bold rounded-full uppercase">Kritis</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-emerald/10 text-emerald text-[10px] font-bold rounded-full uppercase">Aman</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-5">
                                        <div class="flex items-center gap-2">
                                            <a href="server/prosesObat.php?aksi=hapus&id=<?= $o['id'] ?>" class="w-8 h-8 flex items-center justify-center bg-rose/10 text-rose rounded-lg hover:bg-rose hover:text-white transition-all" onclick="return confirm('Hapus data ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
    </script>
</body>
</html>
