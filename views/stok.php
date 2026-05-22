<?php
// views/stok.php – Inventory Management
if (!defined('BASE_PATH')) die('Access Denied');

$pageTitle = 'Inventory';

// Fetch all obat
$all_obat = [];
try {
    $all_obat = $pdo->query("SELECT * FROM obat ORDER BY nama ASC")->fetchAll();
} catch (Throwable $e) {}

// Fetch suppliers for dropdown
$suppliers = [];
try {
    $suppliers = $pdo->query("SELECT id, nama FROM suppliers ORDER BY nama ASC")->fetchAll();
} catch (Throwable $e) {}

$total = count($all_obat);
$low_count = 0;
foreach ($all_obat as $o) {
    if ((int)($o['stok'] ?? 0) <= (int)($o['stok_min'] ?? 40)) $low_count++;
}
?>
<script>document.getElementById('notification-count').textContent = '<?= $low_count ?>';</script>

<div class="page-content page-enter">

    <!-- Filter Bar -->
    <div class="card" style="padding:16px">
        <div class="filter-bar" style="margin-bottom:0">
            <div class="search-input-wrap" style="max-width:320px" role="search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="text" id="inv-search" placeholder="Cari obat, barcode, kategori..." aria-label="Search inventory">
            </div>

            <select class="form-ctrl" id="filter-category" style="width:auto;min-width:140px" aria-label="Filter by category">
                <option value="">Semua Kategori</option>
                <?php
                $cats = array_unique(array_map(fn($o) => $o['kategori'] ?? 'Other', $all_obat));
                sort($cats);
                foreach ($cats as $c): if (!$c) continue; ?>
                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
            </select>

            <select class="form-ctrl" id="filter-status" style="width:auto;min-width:140px" aria-label="Filter by status">
                <option value="">Semua Status</option>
                <option value="in">In Stock</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
            </select>

            <div class="ml-auto flex gap-2 items-center">
                <span id="inv-count" style="font-size:.8rem;color:var(--text-muted)"><?= $total ?> obat</span>

                <div class="view-toggle">
                    <button class="view-toggle-btn active" id="btn-grid" onclick="setView('grid')" title="Grid view" aria-label="Grid view">
                        <i class="bi bi-grid-fill"></i>
                    </button>
                    <button class="view-toggle-btn" id="btn-list" onclick="setView('list')" title="List view" aria-label="List view">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>

                <?php if (canManageObat()): ?>
                <button class="btn btn-primary" onclick="openModal('add-modal')" id="btn-add-obat" aria-label="Add new medication">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Obat
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Inventory Grid -->
    <div class="med-grid" id="med-grid" role="list" aria-label="Medication inventory">
        <?php foreach ($all_obat as $o):
            $stok     = (int)($o['stok'] ?? 0);
            $stok_min = (int)($o['stok_min'] ?? 40);
            $exp_raw  = $o['exp_date'] ?? '';
            $exp_disp = $exp_raw ? date('M Y', strtotime($exp_raw)) : '-';
            $harga    = (float)($o['harga'] ?? 0);

            if ($stok <= 0) {
                $status = 'out'; $status_label = 'Out of Stock'; $badge_cls = 'badge-red';
            } elseif ($stok <= $stok_min) {
                $status = 'low'; $status_label = 'Low Stock'; $badge_cls = 'badge-amber';
            } else {
                $status = 'in'; $status_label = 'In Stock'; $badge_cls = 'badge-green';
            }

            $pct = $stok_min > 0 ? min(100, ($stok / ($stok_min * 2)) * 100) : 100;
        ?>
        <div class="med-card"
             role="listitem"
             data-name="<?= strtolower(htmlspecialchars($o['nama'] ?? '')) ?>"
             data-cat="<?= strtolower(htmlspecialchars($o['kategori'] ?? '')) ?>"
             data-status="<?= $status ?>"
             id="med-<?= $o['id'] ?>"
             onclick="openDetail(<?= htmlspecialchars(json_encode($o)) ?>)"
             aria-label="<?= htmlspecialchars($o['nama'] ?? 'Medication') ?>">
            <div class="flex items-center justify-between mb-3">
                <span class="badge <?= $badge_cls ?>">
                    <span class="status-dot" style="background:<?= $status==='in'?'var(--green)':($status==='low'?'var(--amber)':'var(--red)') ?>"></span>
                    <?= $status_label ?>
                </span>
                <?php if (canManageObat()): ?>
                <div class="flex gap-1" onclick="event.stopPropagation()">
                    <button class="btn btn-ghost btn-icon btn-sm" onclick="editObat(<?= htmlspecialchars(json_encode($o)) ?>)" aria-label="Edit <?= htmlspecialchars($o['nama']) ?>">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <button class="btn btn-ghost btn-icon btn-sm" style="color:var(--red)" onclick="deleteObat(<?= $o['id'] ?>, '<?= addslashes($o['nama']) ?>')" aria-label="Delete <?= htmlspecialchars($o['nama']) ?>">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div style="font-family:'Outfit',sans-serif;font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:2px;line-height:1.3">
                <?= htmlspecialchars($o['nama'] ?? '') ?>
            </div>
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:12px">
                <?= htmlspecialchars($o['kategori'] ?? 'Other') ?> · <?= htmlspecialchars($o['pabrik'] ?? '') ?>
            </div>

            <div style="font-family:'Outfit',sans-serif;font-size:1.6rem;font-weight:800;color:var(--text-primary)">
                <?= number_format($stok) ?>
                <span style="font-size:.8rem;font-weight:500;color:var(--text-muted)"><?= htmlspecialchars($o['satuan'] ?? 'unit') ?></span>
            </div>

            <div class="progress-track" style="margin:10px 0 8px">
                <div class="progress-fill <?= $status==='in'?'green':($status==='low'?'amber':'red') ?>" style="width:<?= round($pct) ?>%"></div>
            </div>

            <div class="flex items-center justify-between" style="font-size:.72rem;color:var(--text-muted)">
                <span><i class="bi bi-calendar3" aria-hidden="true"></i> <?= $exp_disp ?></span>
                <?php if ($harga > 0): ?>
                <span style="font-weight:600;color:var(--text-secondary)">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($all_obat)): ?>
        <div class="empty-state" style="grid-column:1/-1" role="status">
            <i class="bi bi-box-seam" aria-hidden="true"></i>
            <p>Belum ada data obat.<br>Klik "Tambah Obat" untuk memulai.</p>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /page-content -->

<!-- ════ ADD / EDIT MODAL ════════════════════════════════════ -->
<div id="add-modal" class="modal-backdrop hidden" role="dialog" aria-modal="true" aria-labelledby="modal-title-add">
    <div class="modal-panel">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="modal-title-add"><i class="bi bi-box-seam" style="color:var(--accent)" aria-hidden="true"></i> <span id="modal-title-text">Tambah Obat Baru</span></div>
                <div class="modal-sub">Isi data obat lengkap</div>
            </div>
            <button class="modal-close" onclick="closeModal('add-modal')" aria-label="Close modal"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
        <form class="modal-body" id="obat-form" onsubmit="saveObat(event)" novalidate>
            <input type="hidden" id="obat-id" name="id" value="">

            <div class="form-group">
                <label class="form-label" for="obat-nama">Nama Obat <span class="req">*</span></label>
                <input type="text" id="obat-nama" name="nama" class="form-ctrl" placeholder="e.g. Amoxicillin 500mg" required aria-required="true">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="obat-barcode">Barcode / SKU</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-upc-scan input-icon" aria-hidden="true"></i>
                        <input type="text" id="obat-barcode" name="barcode" class="form-ctrl" placeholder="Scan atau masukkan barcode">
                        <button type="button" class="input-btn" onclick="startScanner()" aria-label="Scan barcode">
                            <i class="bi bi-camera" aria-hidden="true"></i> Scan
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="obat-batch">Batch / LOT</label>
                    <input type="text" id="obat-batch" name="batch" class="form-ctrl" placeholder="LOT-2024-001">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="obat-kategori">Kategori</label>
                    <select id="obat-kategori" name="kategori" class="form-ctrl">
                        <option value="">Pilih Kategori</option>
                        <?php foreach (['Antibiotics','Analgesics','Vitamins','Antacids','Antihypertensives','Antidiabetics','Respiratory','Cardiovascular','Dermatology','Other'] as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="obat-satuan">Satuan</label>
                    <select id="obat-satuan" name="satuan" class="form-ctrl">
                        <?php foreach (['Tablet','Capsule','Bottle','Vial','Ampul','Sachet','Tube','Inhaler'] as $s): ?>
                        <option value="<?= $s ?>"><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="obat-pabrik">Pabrik / Produsen</label>
                    <input type="text" id="obat-pabrik" name="pabrik" class="form-ctrl" placeholder="Nama pabrik">
                </div>
                <div class="form-group">
                    <label class="form-label" for="obat-supplier">Supplier</label>
                    <select id="obat-supplier" name="supplier_id" class="form-ctrl">
                        <option value="">Pilih Supplier</option>
                        <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="obat-stok">Jumlah Stok <span class="req">*</span></label>
                    <input type="number" id="obat-stok" name="stok" class="form-ctrl" value="0" min="0" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="obat-stok-min">Min. Stok</label>
                    <input type="number" id="obat-stok-min" name="stok_min" class="form-ctrl" value="10" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="obat-harga-beli">Harga Beli (Rp)</label>
                    <input type="number" id="obat-harga-beli" name="harga_beli" class="form-ctrl" value="0" min="0" step="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="obat-harga">Harga Jual (Rp) <span class="req">*</span></label>
                    <input type="number" id="obat-harga" name="harga" class="form-ctrl" value="0" min="0" step="100" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="obat-exp">Tanggal Kadaluarsa</label>
                    <input type="date" id="obat-exp" name="exp_date" class="form-ctrl" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="obat-lokasi">Lokasi Penyimpanan</label>
                    <input type="text" id="obat-lokasi" name="lokasi" class="form-ctrl" placeholder="Rak A-01">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="obat-catatan">Catatan</label>
                <textarea id="obat-catatan" name="catatan" class="form-ctrl" rows="2" placeholder="Catatan tambahan..."></textarea>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('add-modal')">Batal</button>
            <button type="submit" form="obat-form" class="btn btn-primary" id="save-btn">
                <i class="bi bi-check-lg" aria-hidden="true"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- ════ DETAIL MODAL ════════════════════════════════════════ -->
<div id="detail-modal" class="modal-backdrop hidden" role="dialog" aria-modal="true" aria-labelledby="detail-modal-title">
    <div class="modal-panel">
        <div class="modal-header">
            <div class="modal-title" id="detail-modal-title"><i class="bi bi-info-circle" style="color:var(--accent)" aria-hidden="true"></i> Detail Obat</div>
            <button class="modal-close" onclick="closeModal('detail-modal')" aria-label="Close detail"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body" id="detail-body"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('detail-modal')">Tutup</button>
        </div>
    </div>
</div>

<!-- ════ SCANNER MODAL ════════════════════════════════════════ -->
<div id="scanner-modal" class="modal-backdrop hidden" style="z-index:200" role="dialog" aria-modal="true" aria-label="Barcode scanner">
    <div class="modal-panel" style="max-width:400px;text-align:center">
        <div class="modal-header" style="border-bottom:none;justify-content:flex-end;padding-bottom:0">
            <button class="modal-close" onclick="stopScanner()" aria-label="Close scanner"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body" style="padding-top:0">
            <div style="font-family:'Outfit',sans-serif;font-size:1.1rem;font-weight:700;margin-bottom:12px;color:var(--text-primary)">
                <i class="bi bi-camera" aria-hidden="true"></i> Scan Barcode / QR
            </div>
            <div id="scanner-reader" style="border-radius:14px;overflow:hidden;border:2px solid var(--border)"></div>
            <p style="font-size:.78rem;color:var(--text-muted);margin-top:12px">Arahkan kamera ke barcode obat</p>
        </div>
    </div>
</div>

<script>
let html5QrCode = null;

/* ── Modal Helpers ──────────────────────────────────────── */
function openModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.style.overflow = '';
}
// Close on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(m => {
    m.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

/* ── Inventory Filter + Search ─────────────────────────── */
function filterInventory() {
    const q = (document.getElementById('inv-search').value || '').toLowerCase();
    const cat = (document.getElementById('filter-category').value || '').toLowerCase();
    const status = document.getElementById('filter-status').value || '';
    const cards = document.querySelectorAll('#med-grid .med-card');
    let visible = 0;

    cards.forEach(c => {
        const name = (c.dataset.name || '').toLowerCase();
        const ccat = (c.dataset.cat || '').toLowerCase();
        const cstat = c.dataset.status || '';

        const matchQ   = !q    || name.includes(q) || ccat.includes(q);
        const matchCat = !cat  || ccat.includes(cat);
        const matchSt  = !status || cstat === status;
        const show = matchQ && matchCat && matchSt;

        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const countEl = document.getElementById('inv-count');
    if (countEl) countEl.textContent = visible + ' obat';
}

document.getElementById('inv-search')?.addEventListener('input', filterInventory);
document.getElementById('filter-category')?.addEventListener('change', filterInventory);
document.getElementById('filter-status')?.addEventListener('change', filterInventory);

/* ── View Toggle ────────────────────────────────────────── */
function setView(v) {
    const grid = document.getElementById('med-grid');
    const g = document.getElementById('btn-grid');
    const l = document.getElementById('btn-list');

    if (v === 'list') {
        grid.classList.add('list-view');
        g.classList.remove('active'); l.classList.add('active');
    } else {
        grid.classList.remove('list-view');
        l.classList.remove('active'); g.classList.add('active');
    }
}

/* ── Detail View ────────────────────────────────────────── */
function openDetail(o) {
    const fields = [
        ['Kategori', o.kategori], ['Pabrik', o.pabrik],
        ['Stok', (o.stok || 0) + ' ' + (o.satuan || 'unit')],
        ['Min. Stok', o.stok_min || 0], ['Harga', 'Rp ' + Number(o.harga || 0).toLocaleString('id-ID')],
        ['Exp. Date', o.exp_date || '-'], ['Lokasi', o.lokasi || '-'],
        ['Barcode', o.barcode || '-'], ['Catatan', o.catatan || '-'],
    ];
    const status = (o.stok <= 0) ? '<span class="badge badge-red">Out of Stock</span>' :
                   (o.stok <= o.stok_min) ? '<span class="badge badge-amber">Low Stock</span>' :
                   '<span class="badge badge-green">In Stock</span>';
    let html = `<div style="margin-bottom:12px"><h3 style="font-family:Outfit,sans-serif;font-size:1.2rem;font-weight:800;color:var(--text-primary)">${o.nama || ''}</h3>${status}</div><div class="grid-2" style="gap:12px">`;
    fields.forEach(([k, v]) => {
        html += `<div><div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;font-weight:700;color:var(--text-muted);margin-bottom:3px">${k}</div><div style="font-size:.9rem;font-weight:600;color:var(--text-primary)">${v || '-'}</div></div>`;
    });
    html += '</div>';
    document.getElementById('detail-body').innerHTML = html;
    openModal('detail-modal');
}

/* ── Edit Obat ──────────────────────────────────────────── */
function editObat(o) {
    document.getElementById('modal-title-text').textContent = 'Edit Obat';
    const form = document.getElementById('obat-form');
    form.reset();
    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    setVal('obat-id', o.id);
    setVal('obat-nama', o.nama);
    setVal('obat-barcode', o.barcode);
    setVal('obat-batch', o.batch);
    setVal('obat-kategori', o.kategori);
    setVal('obat-satuan', o.satuan);
    setVal('obat-pabrik', o.pabrik);
    setVal('obat-supplier', o.supplier_id);
    setVal('obat-stok', o.stok);
    setVal('obat-stok-min', o.stok_min);
    setVal('obat-harga-beli', o.harga_beli);
    setVal('obat-harga', o.harga);
    setVal('obat-exp', o.exp_date);
    setVal('obat-lokasi', o.lokasi);
    setVal('obat-catatan', o.catatan);
    openModal('add-modal');
}

/* ── Save Obat (AJAX) ───────────────────────────────────── */
async function saveObat(e) {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading-spinner"></span> Menyimpan...';

    const formData = new FormData(document.getElementById('obat-form'));
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('<?= BASE_URL ?>/api/stok_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Obat berhasil disimpan!', 'success');
            closeModal('add-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Gagal menyimpan.', 'error');
        }
    } catch (err) {
        showToast('Network error: ' + err.message, 'error');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan';
}

/* ── Delete Obat ────────────────────────────────────────── */
async function deleteObat(id, name) {
    if (!confirm(`Hapus "${name}"? Tindakan ini tidak bisa dibatalkan.`)) return;

    try {
        const res = await fetch(`<?= BASE_URL ?>/api/stok_api.php?action=delete&id=${id}`, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (json.success) {
            showToast('Obat berhasil dihapus.', 'success');
            document.getElementById('med-' + id)?.remove();
        } else {
            showToast(json.error || 'Gagal menghapus.', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
}

/* ── Barcode Scanner ────────────────────────────────────── */
function startScanner() {
    openModal('scanner-modal');
    if (typeof Html5Qrcode === 'undefined') {
        showToast('Scanner library tidak tersedia.', 'error');
        closeModal('scanner-modal');
        return;
    }
    html5QrCode = new Html5Qrcode('scanner-reader');
    html5QrCode.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 250, height: 200 } },
        (text) => {
            document.getElementById('obat-barcode').value = text;
            stopScanner();
            showToast('Barcode terdeteksi: ' + text, 'success');
        },
        () => {}
    ).catch(err => {
        showToast('Camera error: ' + err, 'error');
        closeModal('scanner-modal');
    });
}

function stopScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => closeModal('scanner-modal')).catch(() => closeModal('scanner-modal'));
        html5QrCode = null;
    } else {
        closeModal('scanner-modal');
    }
}

/* ── Reset modal on open ────────────────────────────────── */
document.getElementById('btn-add-obat')?.addEventListener('click', () => {
    document.getElementById('modal-title-text').textContent = 'Tambah Obat Baru';
    document.getElementById('obat-form').reset();
    document.getElementById('obat-id').value = '';
});
</script>