<?php
// views/stok.php
if (!defined('BASE_PATH')) die('Access Denied');

$all_obat = $obatModel->getAll();
$pageTitle = 'Inventory';

// Hitung total obat untuk tampilan
$total_medications = count($all_obat);

include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';
?>

<main class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <h1 class="topbar-title">Inventory</h1>
        <div class="topbar-actions">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search medications..." id="global-search">
            </div>
            <button class="icon-btn" @click="toggleTheme()" title="Toggle Dark Mode">
                <i class="bi" :class="isDark ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
            </button>
            <div class="notif-btn">
                <i class="bi bi-bell"></i>
                <span class="notif-badge">4</span>
            </div>
        </div>
    </div>

    <div class="inventory-body">
        
        <!-- Header Row -->
        <div class="inv-header">
            <div class="inv-count"><?= $total_medications ?> of <?= $total_medications ?> medications</div>
            <?php if (canManageObat()): ?>
            <button class="btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-lg"></i> Add Medication
            </button>
            <?php endif; ?>
        </div>

        <!-- Filter Row -->
        <div class="inv-filters">
            <div class="search-filter">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search by name, barcode, manufacturer..." id="local-search">
            </div>
            
            <!-- Custom Dropdowns -->
            <div class="filter-dropdown">
                <select class="custom-select">
                    <option value="All">All</option>
                    <option value="Antibiotics">Antibiotics</option>
                    <option value="Analgesics">Analgesics</option>
                    <option value="Vitamins">Vitamins</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="filter-dropdown">
                <select class="custom-select">
                    <option value="All">All</option>
                    <option value="In Stock">In Stock</option>
                    <option value="Low Stock">Low Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>

            <div class="view-toggles">
                <button class="view-btn active"><i class="bi bi-grid-fill"></i></button>
                <button class="view-btn"><i class="bi bi-list-ul"></i></button>
            </div>
        </div>

        <!-- Grid of Cards -->
        <div class="med-grid">
            <?php foreach($all_obat as $o): 
                $stok = $o['stok'] ?? 0;
                $min_stok = $o['stok_minimum'] ?? 40; // fallback if not exist
                
                // Status Logic
                if ($stok <= 0) {
                    $status = 'Out of Stock';
                    $colorClass = 'red';
                } elseif ($stok <= $min_stok) {
                    $status = 'Low Stock';
                    $colorClass = 'amber';
                } else {
                    $status = 'In Stock';
                    $colorClass = 'green';
                }
                
                // Format price
                $price = number_format($o['harga'] ?? 0, 2, '.', ',');
            ?>
            <div class="med-card group">
                <div class="med-card-top">
                    <div>
                        <div class="med-name"><?= htmlspecialchars($o['nama'] ?? '') ?></div>
                        <div class="med-manu"><?= htmlspecialchars($o['pabrik'] ?? 'PharmaCorp') ?></div>
                    </div>
                    <button class="med-menu-btn"><i class="bi bi-three-dots-vertical"></i></button>
                </div>
                
                <div class="med-qty-row">
                    <span class="med-qty"><?= $stok ?></span>
                    <span class="med-unit"><?= htmlspecialchars($o['satuan'] ?? 'Capsule') ?></span>
                </div>
                
                <div class="med-bar-bg">
                    <div class="med-bar-fill <?= $colorClass ?>" style="width: <?= min(100, ($stok / max(1, $min_stok*2)) * 100) ?>%"></div>
                </div>
                
                <div class="med-card-footer">
                    <div class="status-badge <?= $colorClass ?>">
                        <span class="status-dot"></span> <?= $status ?>
                    </div>
                    <div class="med-footer-right">
                        <span class="med-date"><i class="bi bi-calendar3"></i> Aug 2026</span>
                        <span class="med-price">$<?= $price ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div><!-- /inventory-body -->

    <!-- Add Medication Modal -->
    <div id="add-modal" class="modal-backdrop hidden" aria-hidden="true">
        <div class="modal-panel">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-icon"><i class="bi bi-box"></i></div>
                    <div>
                        <h2 class="modal-title">Add Medication</h2>
                        <p class="modal-sub">Fill in the medication details</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeAddModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <form class="modal-body">
                <div class="form-group full-width">
                    <label>Medication Name *</label>
                    <input type="text" placeholder="e.g. Amoxicillin 500mg" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Barcode / SKU</label>
                        <div class="input-with-icon">
                            <input type="text" placeholder="Scan or enter barcode" id="barcode-input">
                            <button type="button" onclick="startScanner()" class="scan-btn" title="Scan QR/Barcode"><i class="bi bi-qr-code-scan"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Batch Number</label>
                        <input type="text" placeholder="LOT-2024-001">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select>
                            <option>Select category</option>
                            <option>Antibiotics</option>
                            <option>Analgesics</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <select>
                            <option>Tablet</option>
                            <option>Capsule</option>
                            <option>Inhaler</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Manufacturer</label>
                        <input type="text" placeholder="Manufacturer name">
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <input type="text" placeholder="Supplier name">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantity in Stock *</label>
                        <input type="number" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Min Stock Level</label>
                        <input type="number" value="10">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Price</label>
                        <input type="text" placeholder="$0.00">
                    </div>
                    <div class="form-group">
                        <label>Selling Price *</label>
                        <input type="text" placeholder="$0.00" required>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Medication</button>
            </div>
        </div>
    </div>
    
    <!-- Scanner Modal Overlay (Nested/Separate) -->
    <div id="scanner-modal" class="modal-backdrop hidden" style="z-index: 60;">
        <div class="modal-panel" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="border-bottom: none; justify-content: flex-end; padding-bottom: 0;">
                <button class="modal-close" onclick="stopScanner()"><i class="bi bi-x-lg"></i></button>
            </div>
            <h3 style="font-weight: 700; margin-bottom: 20px; color: #0f172a;" class="dark:text-white">Scan Barcode</h3>
            <div id="reader" style="width: 100%; border-radius: 16px; overflow: hidden; background: #fff; margin-bottom: 20px;"></div>
        </div>
    </div>
</main>

<style>
/* ============================================
   INVENTORY LAYOUT - BASE44 STYLE
   ============================================ */
.main-content {
    margin-left: 240px;
    min-height: 100vh;
    background: #f1f5f9;
    display: flex;
    flex-direction: column;
    transition: background 0.3s;
}
.dark .main-content { background: #0f172a; }

/* ---- Top Bar (Reused from Dashboard) ---- */
.topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 28px; background: #fff; border-bottom: 1px solid #e2e8f0;
    position: sticky; top: 0; z-index: 30; transition: all 0.3s;
}
.dark .topbar { background: #1e293b; border-color: #334155; }
.topbar-title { font-size: 1.35rem; font-weight: 700; color: #0f172a; font-family: 'Poppins', sans-serif; }
.dark .topbar-title { color: #f8fafc; }
.topbar-actions { display: flex; align-items: center; gap: 12px; }
.search-box { display: flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 14px; }
.dark .search-box { background: #334155; border-color: #475569; }
.search-box i { color: #94a3b8; font-size: 0.85rem; }
.search-box input { background: none; border: none; outline: none; font-size: 0.85rem; color: #334155; width: 200px; font-family: 'Inter', sans-serif; }
.dark .search-box input { color: #cbd5e1; }
.icon-btn, .notif-btn { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; cursor: pointer; color: #64748b; position: relative; }
.dark .icon-btn, .dark .notif-btn { background: #334155; border-color: #475569; color: #94a3b8; }
.notif-badge { position: absolute; top: -4px; right: -4px; background: #ef4444; color: white; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; }
.dark .notif-badge { border-color: #1e293b; }

/* ---- Inventory Body ---- */
.inventory-body { padding: 24px 28px; display: flex; flex-direction: column; gap: 20px; max-width: 1400px; width: 100%; margin: 0 auto; }

/* ---- Header & Actions ---- */
.inv-header { display: flex; justify-content: space-between; align-items: center; }
.inv-count { font-size: 0.875rem; color: #64748b; font-weight: 500; }
.dark .inv-count { color: #94a3b8; }

.btn-primary { background: #3b82f6; color: white; font-weight: 600; font-size: 0.875rem; padding: 10px 18px; border-radius: 10px; border: none; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: background 0.2s; font-family: 'Inter', sans-serif; }
.btn-primary:hover { background: #2563eb; }

/* ---- Filters ---- */
.inv-filters { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }

.search-filter { flex: 1; min-width: 250px; display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 16px; transition: all 0.2s; }
.dark .search-filter { background: #1e293b; border-color: #334155; }
.search-filter i { color: #94a3b8; }
.search-filter input { background: none; border: none; outline: none; width: 100%; font-size: 0.875rem; color: #334155; }
.dark .search-filter input { color: #f8fafc; }
.search-filter input::placeholder { color: #cbd5e1; }
.dark .search-filter input::placeholder { color: #64748b; }

.filter-dropdown select.custom-select { appearance: none; background: #fff url('data:image/svg+xml;utf8,<svg fill="%2364748b" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 12px center; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 36px 10px 16px; font-size: 0.875rem; color: #334155; cursor: pointer; outline: none; min-width: 140px; }
.dark .filter-dropdown select.custom-select { background-color: #1e293b; border-color: #334155; color: #f8fafc; background-image: url('data:image/svg+xml;utf8,<svg fill="%2394a3b8" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>'); }

.view-toggles { display: flex; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
.dark .view-toggles { background: #1e293b; border-color: #334155; }
.view-btn { background: none; border: none; padding: 10px 14px; cursor: pointer; color: #94a3b8; transition: all 0.2s; }
.view-btn:hover { color: #334155; background: #f8fafc; }
.dark .view-btn:hover { color: #f8fafc; background: #334155; }
.view-btn.active { background: #3b82f6; color: white; }

/* ---- Med Grid ---- */
.med-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }

.med-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; transition: all 0.2s; position: relative; }
.dark .med-card { background: #1e293b; border-color: #334155; }
.med-card:hover { box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); transform: translateY(-2px); border-color: #cbd5e1; }
.dark .med-card:hover { box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); border-color: #475569; }

.med-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.med-name { font-weight: 700; font-size: 1.05rem; color: #0f172a; margin-bottom: 2px; }
.dark .med-name { color: #f8fafc; }
.med-manu { font-size: 0.75rem; color: #94a3b8; }
.med-menu-btn { background: none; border: none; color: #cbd5e1; cursor: pointer; padding: 4px; transition: color 0.2s; }
.med-menu-btn:hover { color: #64748b; }
.dark .med-menu-btn { color: #475569; }
.dark .med-menu-btn:hover { color: #94a3b8; }

.med-qty-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 12px; }
.med-qty { font-size: 2rem; font-weight: 700; font-family: 'Poppins', sans-serif; color: #0f172a; line-height: 1; }
.dark .med-qty { color: #f8fafc; }
.med-unit { font-size: 0.875rem; color: #94a3b8; }

.med-bar-bg { height: 6px; background: #f1f5f9; border-radius: 4px; overflow: hidden; margin-bottom: 16px; }
.dark .med-bar-bg { background: #334155; }
.med-bar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
.med-bar-fill.green { background: #22c55e; }
.med-bar-fill.amber { background: #f59e0b; }
.med-bar-fill.red { background: #ef4444; }

.med-card-footer { display: flex; justify-content: space-between; align-items: center; }
.status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.status-badge.green { background: #f0fdf4; color: #22c55e; }
.status-badge.amber { background: #fffbeb; color: #f59e0b; }
.status-badge.red { background: #fff1f2; color: #ef4444; }
.dark .status-badge.green { background: rgba(34,197,94,0.1); }
.dark .status-badge.amber { background: rgba(245,158,11,0.1); }
.dark .status-badge.red { background: rgba(239,68,68,0.1); }

.status-dot { width: 6px; height: 6px; border-radius: 50%; }
.status-badge.green .status-dot { background: #22c55e; }
.status-badge.amber .status-dot { background: #f59e0b; }
.status-badge.red .status-dot { background: #ef4444; }

.med-footer-right { display: flex; gap: 12px; align-items: center; }
.med-date { font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 4px; }
.med-price { font-size: 0.875rem; font-weight: 700; color: #334155; }
.dark .med-price { color: #cbd5e1; }

/* ---- Modal Styles ---- */
.modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 50; display: flex; align-items: center; justify-content: center; opacity: 1; transition: opacity 0.3s; padding: 20px; }
.modal-backdrop.hidden { opacity: 0; pointer-events: none; }

.modal-panel { background: #fff; width: 100%; max-width: 650px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; transform: scale(1); transition: transform 0.3s; max-height: 90vh; display: flex; flex-direction: column; }
.dark .modal-panel { background: #1e293b; border: 1px solid #334155; }
.modal-backdrop.hidden .modal-panel { transform: scale(0.95); }

.modal-header { padding: 24px 30px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: flex-start; }
.dark .modal-header { border-color: #334155; }
.modal-title-wrap { display: flex; align-items: center; gap: 16px; }
.modal-icon { width: 48px; height: 48px; background: #eff6ff; color: #3b82f6; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.dark .modal-icon { background: rgba(59,130,246,0.1); }
.modal-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; font-family: 'Poppins', sans-serif;}
.dark .modal-title { color: #f8fafc; }
.modal-sub { font-size: 0.85rem; color: #64748b; margin-top: 2px; }
.dark .modal-sub { color: #94a3b8; }
.modal-close { background: none; border: none; font-size: 1.1rem; color: #94a3b8; cursor: pointer; transition: color 0.2s; padding: 4px; }
.modal-close:hover { color: #0f172a; }
.dark .modal-close:hover { color: #f8fafc; }

.modal-body { padding: 30px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group.full-width { width: 100%; }
.form-group label { font-size: 0.875rem; font-weight: 600; color: #334155; }
.dark .form-group label { color: #cbd5e1; }
.form-group input, .form-group select { padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.875rem; color: #0f172a; background: #fff; outline: none; transition: border-color 0.2s; width: 100%; font-family: 'Inter', sans-serif;}
.dark .form-group input, .dark .form-group select { background: #0f172a; border-color: #334155; color: #f8fafc; }
.form-group input:focus, .form-group select:focus { border-color: #3b82f6; }
.input-with-icon { display: flex; gap: 8px; }
.input-with-icon input { flex: 1; }
.scan-btn { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; width: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; transition: all 0.2s; }
.scan-btn:hover { background: #e2e8f0; color: #0f172a; }
.dark .scan-btn { background: #334155; border-color: #475569; color: #94a3b8; }
.dark .scan-btn:hover { background: #475569; color: #f8fafc; }

.modal-footer { padding: 20px 30px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 12px; background: #f8fafc; }
.dark .modal-footer { border-color: #334155; background: #0f172a; }
.btn-cancel { background: white; border: 1px solid #e2e8f0; padding: 10px 20px; border-radius: 10px; font-size: 0.875rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; }
.dark .btn-cancel { background: #1e293b; border-color: #334155; color: #cbd5e1; }
.btn-cancel:hover { background: #f1f5f9; color: #0f172a; }
.dark .btn-cancel:hover { background: #334155; color: #f8fafc; }


/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 768px) {
    .main-content { margin-left: 0; }
    .inventory-body { padding: 16px; }
    .topbar { padding: 14px 16px; }
    .search-box { display: none; }
    .topbar-title { font-size: 1.1rem; }
    .inv-filters { flex-direction: column; align-items: stretch; }
    .search-filter { width: 100%; }
    .form-row { grid-template-columns: 1fr; gap: 16px; }
}
</style>

<!-- Scanner Script -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrCode;

function openAddModal() {
    const modal = document.getElementById('add-modal');
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    // Prevent background scrolling
    document.body.style.overflow = 'hidden';
}

function closeAddModal() {
    const modal = document.getElementById('add-modal');
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function startScanner() {
    const modal = document.getElementById('scanner-modal');
    modal.classList.remove('hidden');
    
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" }, 
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText, decodedResult) => {
            // Set input value
            document.getElementById('barcode-input').value = decodedText;
            stopScanner();
        },
        (errorMessage) => { /* scanning... */ }
    ).catch((err) => {
        alert("Camera error: " + err);
        stopScanner();
    });
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

<?php include BASE_PATH . '/includes/footer.php'; ?>
