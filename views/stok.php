<?php
// views/stok.php
if (!defined('BASE_PATH')) die('Access Denied');

$all_obat = $obatModel->getAll();
$pageTitle = 'Inventory';

// Hitung total obat untuk tampilan
$total_medications = count($all_obat);

// Hitung low_out count untuk notifikasi badge
$low_out = 0;
foreach ($all_obat as $o) {
    $stok = $o['stok'] ?? 0;
    $min_stok = $o['stok_min'] ?? ($o['stok_minimum'] ?? 40);
    if ($stok <= $min_stok) {
        $low_out++;
    }
}
?>
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
            <input type="text" placeholder="Search by name, barcode, manufacturer..." id="local-search-h">
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
            <button class="view-btn active" type="button" data-view="grid" onclick="setInventoryView('grid')" title="Grid view"><i class="bi bi-grid-fill"></i></button>
            <button class="view-btn" type="button" data-view="list" onclick="setInventoryView('list')" title="List view"><i class="bi bi-list-ul"></i></button>
        </div>
    </div>

    <!-- Grid of Cards -->
    <div class="med-grid">
        <?php foreach($all_obat as $o):
            $stok = $o['stok'] ?? 0;
            $min_stok = $o['stok_min'] ?? ($o['stok_minimum'] ?? 40);
            $expiryRaw = $o['exp_date'] ?? ($o['kadaluarsa'] ?? '');
            $expiryDisplay = $expiryRaw ? date('M Y', strtotime($expiryRaw)) : 'Aug 2026';

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
                    <span class="med-date"><i class="bi bi-calendar3"></i> <?= htmlspecialchars($expiryDisplay) ?></span>
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
                        <button type="button" onclick="startScanner()" class="scan-btn" title="Scan QR Code/Barcode"><i class="bi bi-qr-code-scan"></i></button>
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

<script>
    // Set the page title
    document.getElementById('page-title').textContent = 'Inventory';
    // Set the notification count
    document.getElementById('notification-count').textContent = <?= $low_out ?>;
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

    function setInventoryView(view) {
        const grid = document.querySelector('.med-grid');
        if (!grid) return;

        grid.classList.toggle('list-view', view === 'list');
        document.querySelectorAll('.view-btn').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });
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