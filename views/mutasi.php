<?php
// views/mutasi.php – Transaction Management
if (!defined('BASE_PATH')) die('Access Denied');

$pageTitle = 'Transactions';

// Fetch transactions
$txns = [];
$total_in = 0; $total_out = 0;
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS transaksi (
        id INT AUTO_INCREMENT PRIMARY KEY, obat_id INT NOT NULL,
        tipe ENUM('masuk','keluar','input','output') DEFAULT 'masuk',
        jumlah INT NOT NULL DEFAULT 0, keterangan TEXT DEFAULT NULL,
        user_id INT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_obat (obat_id), INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $sql = "SELECT t.*, o.nama AS nama_obat, o.harga AS harga_obat, o.satuan
            FROM transaksi t LEFT JOIN obat o ON t.obat_id = o.id
            ORDER BY t.created_at DESC LIMIT 100";
    $txns = $pdo->query($sql)->fetchAll();

    $total_in  = (int)$pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM transaksi WHERE tipe IN ('masuk','input')")->fetchColumn();
    $total_out = (int)$pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM transaksi WHERE tipe IN ('keluar','output')")->fetchColumn();
} catch (Throwable $e) {}

// Fetch medications for dropdown
$obatList = [];
try {
    $obatList = $pdo->query("SELECT id, nama, stok, satuan, harga FROM obat ORDER BY nama ASC")->fetchAll();
} catch (Throwable $e) {}
?>
<script>document.getElementById('notification-count').textContent = '0';</script>

<div class="page-content page-enter">

    <!-- Stats Row -->
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
        <div class="stat-card">
            <div class="stat-icon-wrap indigo"><i class="bi bi-arrow-left-right"></i></div>
            <div class="stat-value"><?= number_format(count($txns)) ?></div>
            <div class="stat-label">Total Transaksi</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap green"><i class="bi bi-arrow-down-left"></i></div>
            <div class="stat-value"><?= number_format($total_in) ?></div>
            <div class="stat-label">Total Masuk (unit)</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap red"><i class="bi bi-arrow-up-right"></i></div>
            <div class="stat-value"><?= number_format($total_out) ?></div>
            <div class="stat-label">Total Keluar (unit)</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-arrow-left-right" style="color:var(--accent)"></i> Riwayat Transaksi</div>
                <div class="card-sub">Semua mutasi stok masuk dan keluar</div>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-secondary btn-sm" onclick="exportCSV()" aria-label="Export CSV">
                    <i class="bi bi-download"></i> Export CSV
                </button>
                <button class="btn btn-primary btn-sm" onclick="openModal('txn-modal')" id="btn-add-txn" aria-label="Add transaction">
                    <i class="bi bi-plus-lg"></i> Tambah
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap">
            <div class="search-input-wrap" style="min-width:220px;max-width:280px">
                <i class="bi bi-search"></i>
                <input type="text" id="txn-search" placeholder="Cari transaksi..." aria-label="Search transactions">
            </div>
            <select class="form-ctrl" id="txn-type" style="width:auto" aria-label="Filter type">
                <option value="">Semua Tipe</option>
                <option value="masuk">Stok Masuk</option>
                <option value="keluar">Stok Keluar</option>
            </select>
            <input type="date" id="txn-date-from" class="form-ctrl" style="width:auto" aria-label="Date from">
            <input type="date" id="txn-date-to" class="form-ctrl" style="width:auto" aria-label="Date to">
        </div>

        <div class="table-wrap">
            <table class="data-table" id="txn-table" aria-label="Transactions table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Obat</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Nilai</th>
                        <th>Keterangan</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody id="txn-body">
                    <?php if (empty($txns)): ?>
                    <tr><td colspan="7">
                        <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada transaksi.</p></div>
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($txns as $i => $t):
                        $isIn  = in_array($t['tipe'] ?? '', ['masuk','input']);
                        $nilai = (float)($t['harga_obat'] ?? 0) * (int)($t['jumlah'] ?? 0);
                    ?>
                    <tr data-name="<?= strtolower(htmlspecialchars($t['nama_obat'] ?? '')) ?>"
                        data-type="<?= $isIn ? 'masuk' : 'keluar' ?>"
                        data-date="<?= substr($t['created_at'] ?? '', 0, 10) ?>">
                        <td style="color:var(--text-muted);font-size:.78rem"><?= $i+1 ?></td>
                        <td>
                            <div style="font-weight:600;font-size:.84rem"><?= htmlspecialchars($t['nama_obat'] ?? 'Unknown') ?></div>
                            <div style="font-size:.7rem;color:var(--text-muted)"><?= htmlspecialchars($t['satuan'] ?? '') ?></div>
                        </td>
                        <td>
                            <span class="badge <?= $isIn ? 'badge-green' : 'badge-red' ?>">
                                <i class="bi bi-arrow-<?= $isIn ? 'down-left' : 'up-right' ?>"></i>
                                <?= $isIn ? 'Masuk' : 'Keluar' ?>
                            </span>
                        </td>
                        <td style="font-weight:700;color:<?= $isIn ? 'var(--green)' : 'var(--red)' ?>">
                            <?= $isIn ? '+' : '-' ?><?= number_format((int)$t['jumlah']) ?>
                        </td>
                        <td style="font-size:.82rem;color:var(--text-secondary)">
                            <?= $nilai > 0 ? 'Rp '.number_format($nilai,0,',','.') : '-' ?>
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted);max-width:200px">
                            <?= htmlspecialchars($t['keterangan'] ?? '-') ?>
                        </td>
                        <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap">
                            <?= $t['created_at'] ? date('d M Y H:i', strtotime($t['created_at'])) : '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ════ ADD TRANSACTION MODAL ═══════════════════════════════ -->
<div id="txn-modal" class="modal-backdrop hidden" role="dialog" aria-modal="true" aria-labelledby="txn-modal-title">
    <div class="modal-panel">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="txn-modal-title"><i class="bi bi-arrow-left-right" style="color:var(--accent)"></i> Tambah Transaksi</div>
                <div class="modal-sub">Catat mutasi stok masuk atau keluar</div>
            </div>
            <button class="modal-close" onclick="closeModal('txn-modal')" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <form class="modal-body" id="txn-form" onsubmit="saveTxn(event)" novalidate>
            <div class="form-group">
                <label class="form-label" for="txn-obat">Pilih Obat <span class="req">*</span></label>
                <select id="txn-obat" name="obat_id" class="form-ctrl" required onchange="updateStokInfo(this)" aria-required="true">
                    <option value="">-- Pilih Obat --</option>
                    <?php foreach ($obatList as $o): ?>
                    <option value="<?= $o['id'] ?>"
                            data-stok="<?= $o['stok'] ?>"
                            data-satuan="<?= htmlspecialchars($o['satuan'] ?? 'unit') ?>"
                            data-harga="<?= $o['harga'] ?>">
                        <?= htmlspecialchars($o['nama']) ?> (Stok: <?= $o['stok'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="stok-info" class="hidden" style="padding:12px;border-radius:10px;background:var(--accent-light);border:1px solid rgba(99,102,241,.2);font-size:.82rem">
                <strong>Stok Tersedia:</strong> <span id="stok-val">-</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="txn-tipe">Tipe <span class="req">*</span></label>
                    <select id="txn-tipe" name="tipe" class="form-ctrl" required aria-required="true">
                        <option value="masuk">Stok Masuk</option>
                        <option value="keluar">Stok Keluar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="txn-jumlah">Jumlah <span class="req">*</span></label>
                    <input type="number" id="txn-jumlah" name="jumlah" class="form-ctrl" min="1" value="1" required aria-required="true">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="txn-ket">Keterangan</label>
                <textarea id="txn-ket" name="keterangan" class="form-ctrl" rows="2" placeholder="Tujuan / sumber mutasi..."></textarea>
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('txn-modal')">Batal</button>
            <button type="submit" form="txn-form" class="btn btn-primary" id="txn-save-btn">
                <i class="bi bi-check-lg"></i> Simpan Transaksi
            </button>
        </div>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-backdrop').forEach(m => { m.addEventListener('click',e=>{ if(e.target===m)closeModal(m.id); }); });

/* Stock info */
function updateStokInfo(sel) {
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('stok-info');
    if (sel.value) {
        document.getElementById('stok-val').textContent = opt.dataset.stok + ' ' + opt.dataset.satuan;
        info.classList.remove('hidden');
    } else {
        info.classList.add('hidden');
    }
}

/* Save transaction */
async function saveTxn(e) {
    e.preventDefault();
    const btn = document.getElementById('txn-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading-spinner"></span>';

    const fd = new FormData(document.getElementById('txn-form'));
    const data = Object.fromEntries(fd.entries());

    try {
        const res = await fetch('<?= BASE_URL ?>/api/transaksi_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Transaksi disimpan!', 'success');
            closeModal('txn-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Gagal menyimpan.', 'error');
        }
    } catch (err) { showToast('Error: ' + err.message, 'error'); }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan Transaksi';
}

/* Filter */
function filterTxn() {
    const q    = (document.getElementById('txn-search').value || '').toLowerCase();
    const type = document.getElementById('txn-type').value || '';
    const from = document.getElementById('txn-date-from').value || '';
    const to   = document.getElementById('txn-date-to').value || '';
    document.querySelectorAll('#txn-body tr[data-name]').forEach(r => {
        const name = r.dataset.name || '';
        const t    = r.dataset.type || '';
        const d    = r.dataset.date || '';
        const show = (!q || name.includes(q)) &&
                     (!type || t === type) &&
                     (!from || d >= from) &&
                     (!to   || d <= to);
        r.style.display = show ? '' : 'none';
    });
}
['txn-search','txn-type','txn-date-from','txn-date-to'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', filterTxn);
    document.getElementById(id)?.addEventListener('input', filterTxn);
});

/* Export CSV */
function exportCSV() {
    const rows = [['#','Obat','Tipe','Jumlah','Nilai','Keterangan','Tanggal']];
    document.querySelectorAll('#txn-body tr[data-name]').forEach((r,i) => {
        if (r.style.display !== 'none') {
            const tds = r.querySelectorAll('td');
            rows.push([i+1, tds[1]?.innerText?.trim(), tds[2]?.innerText?.trim(),
                       tds[3]?.innerText?.trim(), tds[4]?.innerText?.trim(),
                       tds[5]?.innerText?.trim(), tds[6]?.innerText?.trim()]);
        }
    });
    const csv = rows.map(r => r.map(c => `"${(c||'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
    a.download = 'transaksi_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}
</script>