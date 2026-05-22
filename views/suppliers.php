<?php
// views/suppliers.php – Supplier Management
if (!defined('BASE_PATH')) die('Access Denied');
$pageTitle = 'Suppliers';

// Ensure table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(150) NOT NULL, kategori VARCHAR(100) DEFAULT NULL,
        alamat TEXT DEFAULT NULL, telepon VARCHAR(50) DEFAULT NULL,
        email VARCHAR(120) DEFAULT NULL, kontak_pic VARCHAR(100) DEFAULT NULL,
        catatan TEXT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_nama (nama)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

$suppliers = [];
try {
    $suppliers = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM obat o WHERE o.supplier_id = s.id) AS total_obat FROM suppliers s ORDER BY s.nama ASC")->fetchAll();
} catch (Throwable $e) {}
?>
<script>document.getElementById('notification-count').textContent = '0';</script>

<div class="page-content page-enter">

    <!-- Header actions -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <div style="font-family:'Outfit',sans-serif;font-size:1.3rem;font-weight:800;color:var(--text-primary)"><?= count($suppliers) ?> Suppliers</div>
            <div style="font-size:.82rem;color:var(--text-muted)">Kelola daftar supplier obat</div>
        </div>
        <?php if (canManageObat()): ?>
        <button class="btn btn-primary" onclick="openModal('supp-modal')" aria-label="Add supplier">
            <i class="bi bi-plus-lg"></i> Tambah Supplier
        </button>
        <?php endif; ?>
    </div>

    <!-- Search -->
    <div class="search-input-wrap" style="max-width:320px;margin-bottom:20px">
        <i class="bi bi-search"></i>
        <input type="text" id="supp-search" placeholder="Cari supplier..." aria-label="Search suppliers">
    </div>

    <!-- Supplier Cards -->
    <div class="supplier-grid" id="supp-grid" role="list">
        <?php if (empty($suppliers)): ?>
        <div class="empty-state card" style="grid-column:1/-1;padding:48px">
            <i class="bi bi-truck"></i>
            <p>Belum ada supplier.<br>Klik "Tambah Supplier" untuk memulai.</p>
        </div>
        <?php endif; ?>

        <?php foreach ($suppliers as $s): ?>
        <div class="supplier-card" data-name="<?= strtolower(htmlspecialchars($s['nama'] ?? '')) ?>" role="listitem">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-icon-wrap indigo" style="width:42px;height:42px;font-size:1rem">
                    <i class="bi bi-building"></i>
                </div>
                <?php if (canManageObat()): ?>
                <div class="flex gap-1">
                    <button class="btn btn-ghost btn-icon btn-sm" onclick="editSupplier(<?= htmlspecialchars(json_encode($s)) ?>)" aria-label="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-ghost btn-icon btn-sm" style="color:var(--red)" onclick="deleteSupplier(<?= $s['id'] ?>, '<?= addslashes($s['nama']) ?>')" aria-label="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div style="font-family:'Outfit',sans-serif;font-weight:700;font-size:1rem;margin-bottom:4px"><?= htmlspecialchars($s['nama']) ?></div>
            <?php if ($s['kategori']): ?>
            <span class="badge badge-indigo" style="margin-bottom:10px"><?= htmlspecialchars($s['kategori']) ?></span>
            <?php endif; ?>

            <div style="display:flex;flex-direction:column;gap:6px;margin-top:10px;font-size:.8rem;color:var(--text-secondary)">
                <?php if ($s['telepon']): ?>
                <div><i class="bi bi-telephone" style="color:var(--accent);margin-right:6px"></i><?= htmlspecialchars($s['telepon']) ?></div>
                <?php endif; ?>
                <?php if ($s['email']): ?>
                <div><i class="bi bi-envelope" style="color:var(--accent);margin-right:6px"></i><?= htmlspecialchars($s['email']) ?></div>
                <?php endif; ?>
                <?php if ($s['alamat']): ?>
                <div><i class="bi bi-geo-alt" style="color:var(--accent);margin-right:6px"></i><?= htmlspecialchars($s['alamat']) ?></div>
                <?php endif; ?>
            </div>

            <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:.75rem;color:var(--text-muted)"><?= (int)$s['total_obat'] ?> produk terdaftar</span>
                <?php if ($s['telepon']): ?>
                <a href="tel:<?= htmlspecialchars($s['telepon']) ?>" class="btn btn-primary btn-sm" style="padding:5px 12px" aria-label="Call supplier">
                    <i class="bi bi-telephone"></i> Hubungi
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- ════ ADD/EDIT SUPPLIER MODAL ═════════════════════════════ -->
<div id="supp-modal" class="modal-backdrop hidden" role="dialog" aria-modal="true" aria-labelledby="supp-modal-title">
    <div class="modal-panel">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="supp-modal-title"><i class="bi bi-truck" style="color:var(--accent)"></i> <span id="supp-modal-text">Tambah Supplier</span></div>
            </div>
            <button class="modal-close" onclick="closeModal('supp-modal')" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <form class="modal-body" id="supp-form" onsubmit="saveSupplier(event)" novalidate>
            <input type="hidden" id="supp-id" name="id" value="">

            <div class="form-group">
                <label class="form-label" for="supp-nama">Nama Supplier <span class="req">*</span></label>
                <input type="text" id="supp-nama" name="nama" class="form-ctrl" required placeholder="PT. Pharma Indonesia">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="supp-kategori">Kategori</label>
                    <select id="supp-kategori" name="kategori" class="form-ctrl">
                        <option value="">Pilih Kategori</option>
                        <option>Distributor Nasional</option>
                        <option>Distributor Lokal</option>
                        <option>Produsen Langsung</option>
                        <option>Importir</option>
                        <option>Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="supp-kontak">PIC / Kontak</label>
                    <input type="text" id="supp-kontak" name="kontak_pic" class="form-ctrl" placeholder="Nama kontak person">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="supp-telepon">Telepon</label>
                    <input type="tel" id="supp-telepon" name="telepon" class="form-ctrl" placeholder="021-xxxx-xxxx">
                </div>
                <div class="form-group">
                    <label class="form-label" for="supp-email">Email</label>
                    <input type="email" id="supp-email" name="email" class="form-ctrl" placeholder="supplier@email.com">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="supp-alamat">Alamat</label>
                <textarea id="supp-alamat" name="alamat" class="form-ctrl" rows="2" placeholder="Alamat lengkap supplier"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="supp-catatan">Catatan</label>
                <textarea id="supp-catatan" name="catatan" class="form-ctrl" rows="2" placeholder="Catatan tambahan..."></textarea>
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('supp-modal')">Batal</button>
            <button type="submit" form="supp-form" class="btn btn-primary" id="supp-save-btn">
                <i class="bi bi-check-lg"></i> Simpan
            </button>
        </div>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-backdrop').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)closeModal(m.id);}));

/* Search */
document.getElementById('supp-search')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#supp-grid .supplier-card').forEach(c => {
        c.style.display = (c.dataset.name||'').includes(q) ? '' : 'none';
    });
});

/* Edit */
function editSupplier(s) {
    document.getElementById('supp-modal-text').textContent = 'Edit Supplier';
    document.getElementById('supp-form').reset();
    const set=(id,v)=>{const e=document.getElementById(id);if(e)e.value=v||'';};
    set('supp-id',s.id); set('supp-nama',s.nama); set('supp-kategori',s.kategori);
    set('supp-kontak',s.kontak_pic); set('supp-telepon',s.telepon);
    set('supp-email',s.email); set('supp-alamat',s.alamat); set('supp-catatan',s.catatan);
    openModal('supp-modal');
}

/* Save */
async function saveSupplier(e) {
    e.preventDefault();
    const btn = document.getElementById('supp-save-btn');
    btn.disabled = true;
    const fd = new FormData(document.getElementById('supp-form'));
    const data = Object.fromEntries(fd.entries());
    try {
        const res = await fetch('<?= BASE_URL ?>/api/supplier_api.php',{
            method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body:JSON.stringify(data)
        });
        const json = await res.json();
        if (json.success) { showToast(json.message||'Tersimpan!','success'); closeModal('supp-modal'); setTimeout(()=>location.reload(),800); }
        else showToast(json.error||'Error','error');
    } catch(err) { showToast('Error: '+err.message,'error'); }
    btn.disabled = false;
}

/* Delete */
async function deleteSupplier(id, name) {
    if (!confirm(`Hapus supplier "${name}"?`)) return;
    try {
        const res = await fetch(`<?= BASE_URL ?>/api/supplier_api.php?action=delete&id=${id}`,{
            method:'DELETE',headers:{'X-Requested-With':'XMLHttpRequest'}
        });
        const json = await res.json();
        if (json.success) { showToast('Supplier dihapus.','success'); location.reload(); }
        else showToast(json.error||'Error','error');
    } catch(err) { showToast('Error','error'); }
}
</script>