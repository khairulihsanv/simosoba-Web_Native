<?php
// views/mutasi.php
if (!defined('BASE_PATH')) die('Access Denied');

$msg = '';
$msg_type = '';

// Handle Mutasi (Input/Output) Form Submit
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

// Fetch Transactions
$stmt = $pdo->query("SELECT t.*, o.nama as nama_obat, o.harga as harga, u.nama as nama_user 
                     FROM transaksi t 
                     JOIN obat o ON t.obat_id = o.id 
                     JOIN users u ON t.user_id = u.id 
                     ORDER BY t.created_at DESC LIMIT 50");
$transactions = $stmt->fetchAll();
$total_trx = count($transactions);

include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';
?>

<main class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <h1 class="topbar-title">Transactions</h1>
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

    <div class="transaction-body">
        
        <?php if ($msg): ?>
            <div class="alert-msg <?= $msg_type === 'success' ? 'success' : 'error' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <!-- Header Row -->
        <div class="trx-header">
            <div class="trx-count"><?= $total_trx ?> transactions</div>
            <div class="trx-actions">
                <div class="filter-dropdown">
                    <select class="custom-select">
                        <option value="All">All</option>
                        <option value="Stock In">Stock In</option>
                        <option value="Stock Out">Stock Out</option>
                    </select>
                </div>
                <button class="btn-primary" onclick="openTxnModal()">
                    <i class="bi bi-plus-lg"></i> New Transaction
                </button>
            </div>
        </div>

        <!-- Transactions List -->
        <div class="trx-list-container">
            <?php if (empty($transactions)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No transactions found.</p>
                </div>
            <?php else: ?>
                <?php foreach($transactions as $t): 
                    $is_in = in_array($t['tipe'] ?? '', ['masuk', 'input']);
                    $date = date('Y-m-d', strtotime($t['created_at'] ?? 'now'));
                    $ref = $is_in ? "PO-" . date('Y', strtotime($t['created_at'])) . "-" . sprintf('%03d', $t['id']) 
                                  : "SO-" . date('Y', strtotime($t['created_at'])) . "-" . sprintf('%03d', $t['id']);
                    
                    $price = ($t['harga'] ?? 0);
                    $total_price = $price * ($t['jumlah'] ?? 0);
                ?>
                <div class="trx-row group">
                    <div class="trx-left">
                        <div class="trx-icon-circle <?= $is_in ? 'in' : 'out' ?>">
                            <i class="bi <?= $is_in ? 'bi-arrow-down' : 'bi-arrow-up' ?>"></i>
                        </div>
                        <div class="trx-details">
                            <div class="trx-name"><?= htmlspecialchars($t['nama_obat'] ?? '') ?></div>
                            <div class="trx-meta"><?= $date ?> · Ref: <?= $ref ?></div>
                        </div>
                    </div>
                    
                    <div class="trx-mid">
                        <div class="trx-badge <?= $is_in ? 'in' : 'out' ?>">
                            <?= $is_in ? 'Stock In' : 'Stock Out' ?>
                        </div>
                    </div>

                    <div class="trx-right">
                        <div class="trx-qty"><?= $t['jumlah'] ?? 0 ?> units</div>
                        <?php if ($total_price > 0): ?>
                        <div class="trx-price">$<?= number_format($total_price, 2, '.', ',') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div><!-- /transaction-body -->

    <!-- Add Transaction Modal -->
    <div id="txn-modal" class="modal-backdrop hidden" aria-hidden="true">
        <div class="modal-panel" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-icon"><i class="bi bi-arrow-left-right"></i></div>
                    <div>
                        <h2 class="modal-title">Record Transaction</h2>
                        <p class="modal-sub">Input stock in or stock out</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeTxnModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <form class="modal-body" method="POST">
                <div class="form-group full-width">
                    <label>Select Medication *</label>
                    <select name="obat_id" id="obat_id" required>
                        <option value="">-- Search or Select --</option>
                        <?php foreach($all_obat as $o): ?>
                            <option value="<?= $o['id'] ?>">
                                <?= htmlspecialchars($o['nama']) ?> (Sisa: <?= $o['stok'] ?> <?= htmlspecialchars($o['satuan']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Transaction Type *</label>
                        <select name="tipe" required>
                            <option value="masuk">📥 Stock In (Add)</option>
                            <option value="keluar">📤 Stock Out (Deduct)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="jumlah" min="1" placeholder="e.g. 50" required>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Notes / Ref</label>
                    <input type="text" name="keterangan" placeholder="Optional notes">
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 12px;">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
/* ============================================
   TRANSACTIONS LAYOUT - BASE44 STYLE
   ============================================ */
.main-content {
    margin-left: 240px; min-height: 100vh; background: #f1f5f9; display: flex; flex-direction: column; transition: background 0.3s;
}
.dark .main-content { background: #0f172a; }

/* ---- Top Bar (Reused) ---- */
.topbar { display: flex; align-items: center; justify-content: space-between; padding: 18px 28px; background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 30; transition: all 0.3s; }
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

/* ---- Body ---- */
.transaction-body { padding: 24px 28px; max-width: 1200px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; }

/* ---- Message ---- */
.alert-msg { padding: 14px 20px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; }
.alert-msg.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.alert-msg.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.dark .alert-msg.success { background: rgba(34,197,94,0.1); color: #4ade80; border-color: rgba(34,197,94,0.2); }
.dark .alert-msg.error { background: rgba(239,68,68,0.1); color: #f87171; border-color: rgba(239,68,68,0.2); }

/* ---- Header ---- */
.trx-header { display: flex; justify-content: space-between; align-items: center; }
.trx-count { font-size: 0.875rem; color: #64748b; font-weight: 500; }
.dark .trx-count { color: #94a3b8; }
.trx-actions { display: flex; gap: 12px; align-items: center; }

.btn-primary { background: #3b82f6; color: white; font-weight: 600; font-size: 0.875rem; padding: 10px 18px; border-radius: 10px; border: none; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: background 0.2s; font-family: 'Inter', sans-serif; }
.btn-primary:hover { background: #2563eb; }

.filter-dropdown select.custom-select { appearance: none; background: #fff url('data:image/svg+xml;utf8,<svg fill="%2364748b" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 12px center; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 36px 10px 16px; font-size: 0.875rem; color: #334155; cursor: pointer; outline: none; min-width: 120px; }
.dark .filter-dropdown select.custom-select { background-color: #1e293b; border-color: #334155; color: #f8fafc; background-image: url('data:image/svg+xml;utf8,<svg fill="%2394a3b8" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>'); }

/* ---- List ---- */
.trx-list-container { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.dark .trx-list-container { background: #1e293b; border-color: #334155; }

.trx-row { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
.dark .trx-row { border-color: #334155; }
.trx-row:last-child { border-bottom: none; }
.trx-row:hover { background: #f8fafc; }
.dark .trx-row:hover { background: #334155; }

.trx-left { display: flex; align-items: center; gap: 16px; flex: 2; }
.trx-icon-circle { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.trx-icon-circle.in { background: #dcfce7; color: #16a34a; }
.trx-icon-circle.out { background: #fee2e2; color: #dc2626; }
.dark .trx-icon-circle.in { background: rgba(34,197,94,0.15); color: #4ade80; }
.dark .trx-icon-circle.out { background: rgba(239,68,68,0.15); color: #f87171; }

.trx-name { font-weight: 600; font-size: 0.95rem; color: #0f172a; margin-bottom: 2px; }
.dark .trx-name { color: #f8fafc; }
.trx-meta { font-size: 0.8rem; color: #94a3b8; }

.trx-mid { flex: 1; display: flex; justify-content: center; }
.trx-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; }
.trx-badge.in { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
.trx-badge.out { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
.dark .trx-badge.in { background: rgba(34,197,94,0.1); color: #4ade80; border-color: rgba(34,197,94,0.2); }
.dark .trx-badge.out { background: rgba(239,68,68,0.1); color: #f87171; border-color: rgba(239,68,68,0.2); }

.trx-right { flex: 1; display: flex; flex-direction: column; align-items: flex-end; gap: 2px; }
.trx-qty { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
.dark .trx-qty { color: #f8fafc; }
.trx-price { font-size: 0.8rem; color: #64748b; }
.dark .trx-price { color: #94a3b8; }

.empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
.empty-state i { font-size: 3rem; display: block; margin-bottom: 12px; }
.empty-state p { font-size: 1rem; }

/* ---- Modal Styles (Reused) ---- */
.modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 50; display: flex; align-items: center; justify-content: center; opacity: 1; transition: opacity 0.3s; padding: 20px; }
.modal-backdrop.hidden { opacity: 0; pointer-events: none; }
.modal-panel { background: #fff; width: 100%; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; transform: scale(1); transition: transform 0.3s; max-height: 90vh; display: flex; flex-direction: column; }
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
.form-group input, .form-group select { padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.875rem; color: #0f172a; background: #fff; outline: none; transition: border-color 0.2s; width: 100%; font-family: 'Inter', sans-serif;}
.dark .form-group input, .dark .form-group select { background: #0f172a; border-color: #334155; color: #f8fafc; }
.form-group input:focus, .form-group select:focus { border-color: #3b82f6; }

@media (max-width: 768px) {
    .main-content { margin-left: 0; }
    .transaction-body { padding: 16px; }
    .topbar { padding: 14px 16px; }
    .search-box { display: none; }
    .trx-row { flex-direction: column; align-items: flex-start; gap: 12px; }
    .trx-mid { justify-content: flex-start; width: 100%; }
    .trx-right { align-items: flex-start; width: 100%; }
    .trx-header { flex-direction: column; align-items: flex-start; gap: 12px; }
}
</style>

<script>
function openTxnModal() {
    const modal = document.getElementById('txn-modal');
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeTxnModal() {
    const modal = document.getElementById('txn-modal');
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
