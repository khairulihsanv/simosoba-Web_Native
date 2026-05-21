<?php
// views/suppliers.php
if (!defined('BASE_PATH')) die('Access Denied');

$pageTitle = 'Suppliers';

// Dummy data for visual representation (since there might not be a DB table for it yet)
$suppliers = [
    [
        'name' => 'QuickMed',
        'person' => 'James Lee',
        'phone' => '+1 555 0202',
        'terms' => 'Net 15'
    ],
    [
        'name' => 'CardioSupply',
        'person' => 'Emily Chen',
        'phone' => '+1 555 0303',
        'terms' => 'Net 45'
    ],
    [
        'name' => 'VitaSupply',
        'person' => 'Michael Brown',
        'phone' => '+1 555 0404',
        'terms' => 'COD'
    ],
    [
        'name' => 'MediSupply Ltd',
        'person' => 'Sarah Johnson',
        'phone' => '+1 555 0101',
        'terms' => 'Net 30'
    ]
];

$total_suppliers = count($suppliers);

// For notification badge, we can set to 0 as there's no alert for suppliers
$low_out = 0;
?>
<div class="suppliers-body">
    <!-- Header Row -->
    <div class="sup-header">
        <div class="sup-count"><?= $total_suppliers ?> suppliers</div>
        <button class="btn-primary" onclick="openSupModal()">
            <i class="bi bi-plus-lg"></i> Add Supplier
        </button>
    </div>

    <!-- Cards Grid -->
    <div class="sup-grid">
        <?php foreach($suppliers as $s): ?>
        <div class="sup-card group">
            <div class="sup-card-top">
                <div class="sup-icon-box">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="sup-actions">
                    <button class="icon-btn-sm edit-btn"><i class="bi bi-pencil-square"></i></button>
                    <button class="icon-btn-sm delete-btn"><i class="bi bi-trash"></i></button>
                </div>
            </div>

            <div class="sup-info">
                <div class="sup-name"><?= htmlspecialchars($s['name']) ?></div>
                <div class="sup-person"><?= htmlspecialchars($s['person']) ?></div>
            </div>

            <div class="sup-contacts">
                <div class="contact-item">
                    <i class="bi bi-telephone"></i>
                    <span><?= htmlspecialchars($s['phone']) ?></span>
                </div>
                <div class="contact-item text-green">
                    <i class="bi bi-whatsapp"></i>
                    <span>WhatsApp</span>
                </div>
                <div class="contact-item text-slate">
                    <i class="bi bi-envelope"></i>
                    <span>Email</span>
                </div>
            </div>

            <div class="sup-footer">
                <span class="payment-terms">Payment: <?= htmlspecialchars($s['terms']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div><!-- /suppliers-body -->

<!-- Add Supplier Modal -->
<div id="sup-modal" class="modal-backdrop hidden" aria-hidden="true">
    <div class="modal-panel" style="max-width: 600px;">
        <div class="modal-header">
            <div class="modal-title-wrap">
                <div class="modal-icon"><i class="bi bi-truck"></i></div>
                <h2 class="modal-title">Add Supplier</h2>
            </div>
            <button class="modal-close" onclick="closeSupModal()"><i class="bi bi-x-lg"></i></button>
        </div>

        <form class="modal-body">
            <div class="form-group full-width">
                <label>Company Name *</label>
                <input type="text" placeholder="Supplier Co." required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" placeholder="+1 234 567 8900">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" placeholder="supplier@example.com">
                </div>
                <div class="form-group">
                    <label>WhatsApp</label>
                    <input type="text" placeholder="+1 234 567 8900">
                </div>
            </div>

            <div class="form-group full-width">
                <label>Address</label>
                <input type="text" placeholder="123 Main St, City">
            </div>

            <div class="form-group full-width">
                <label>Payment Terms</label>
                <input type="text" placeholder="Net 30">
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeSupModal()">Cancel</button>
            <button type="submit" class="btn-primary">Add Supplier</button>
        </div>
    </div>
</div>

<script>
    // Set the page title
    document.getElementById('page-title').textContent = 'Suppliers';
    // Set the notification count
    document.getElementById('notification-count').textContent = <?= $low_out ?>;
    function openSupModal() {
        const modal = document.getElementById('sup-modal');
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeSupModal() {
        const modal = document.getElementById('sup-modal');
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
</script>