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
            $msg = "âœ… Stok berhasil diperbarui!";
            $msg_type = 'success';
        } else {
            $msg = "âŒ Gagal memperbarui stok. Pastikan stok mencukupi jika tipe keluar.";
            $msg_type = 'error';
        }
    } else {
        $msg = "âŒ Jumlah harus lebih dari 0.";
        $msg_type = 'error';
    }
}

$all_obat = $obatModel->getAll();

// Hitung low_out count untuk notifikasi badge
$low_out = 0;
foreach ($all_obat as $o) {
    $stok = $o['stok'] ?? 0;
    $min_stok = $o['stok_min'] ?? ($o['stok_minimum'] ?? 40);
    if ($stok <= $min_stok) {
        $low_out++;
    }
}

// Fetch Transactions
$stmt = $pdo->query("SELECT t.*, o.nama as nama_obat, o.harga as harga, u.nama as nama_user
                     FROM transaksi t
                     JOIN obat o ON t.obat_id = o.id
                     JOIN users u ON t.user_id = u.id
                     ORDER BY t.created_at DESC LIMIT 50");
$transactions = $stmt->fetchAll();
$total_trx = count($transactions);
?>
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
                <button class="btn-primary" onclick="openTxnModal()">
                    <i class="bi bi-plus-lg"></i> New Transaction
                </button>
            </div>
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
                            <div class="trx-meta"><?= $date ?> Â· Ref: <?= $ref ?></div>
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
                        <option value="masuk">ðŸ“¥ Stock In (Add)</option>
                        <option value="keluar">ðŸ“¤ Stock Out (Deduct)</option>
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

<script>
    // Set the page title
    document.getElementById('page-title').textContent = 'Transactions';
    // Set the notification count
    document.getElementById('notification-count').textContent = <?= $low_out ?>;
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