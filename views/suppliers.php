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

include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/sidebar.php';
?>

<main class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <h1 class="topbar-title">Suppliers</h1>
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
</main>

<style>
/* ============================================
   SUPPLIERS LAYOUT - BASE44 STYLE
   ============================================ */
.main-content { margin-left: 240px; min-height: 100vh; background: #f1f5f9; display: flex; flex-direction: column; transition: background 0.3s; }
.dark .main-content { background: #0f172a; }

/* ---- Top Bar ---- */
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
.suppliers-body { padding: 24px 28px; max-width: 1200px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; }

/* ---- Header ---- */
.sup-header { display: flex; justify-content: space-between; align-items: center; }
.sup-count { font-size: 0.875rem; color: #64748b; font-weight: 500; }
.dark .sup-count { color: #94a3b8; }
.btn-primary { background: #3b82f6; color: white; font-weight: 600; font-size: 0.875rem; padding: 10px 18px; border-radius: 10px; border: none; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: background 0.2s; font-family: 'Inter', sans-serif; }
.btn-primary:hover { background: #2563eb; }

/* ---- Grid ---- */
.sup-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.sup-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; transition: all 0.2s; display: flex; flex-direction: column; gap: 16px; }
.dark .sup-card { background: #1e293b; border-color: #334155; }
.sup-card:hover { box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); transform: translateY(-2px); border-color: #cbd5e1; }
.dark .sup-card:hover { box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); border-color: #475569; }

.sup-card-top { display: flex; justify-content: space-between; align-items: flex-start; }
.sup-icon-box { width: 44px; height: 44px; background: #eff6ff; color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.dark .sup-icon-box { background: rgba(59,130,246,0.1); }
.sup-actions { display: flex; gap: 8px; }
.icon-btn-sm { background: none; border: none; color: #cbd5e1; cursor: pointer; padding: 4px; font-size: 1.1rem; transition: color 0.2s; }
.dark .icon-btn-sm { color: #475569; }
.edit-btn:hover { color: #3b82f6; }
.delete-btn:hover { color: #ef4444; }

.sup-info { display: flex; flex-direction: column; gap: 4px; }
.sup-name { font-weight: 700; font-size: 1.1rem; color: #0f172a; }
.dark .sup-name { color: #f8fafc; }
.sup-person { font-size: 0.85rem; color: #64748b; }
.dark .sup-person { color: #94a3b8; }

.sup-contacts { display: flex; flex-wrap: wrap; gap: 12px; font-size: 0.8rem; color: #64748b; }
.dark .sup-contacts { color: #94a3b8; }
.contact-item { display: flex; align-items: center; gap: 4px; }
.contact-item.text-green { color: #16a34a; }
.dark .contact-item.text-green { color: #4ade80; }
.contact-item.text-slate { color: #64748b; }
.dark .contact-item.text-slate { color: #94a3b8; }

.sup-footer { margin-top: auto; padding-top: 12px; border-top: 1px solid #f1f5f9; }
.dark .sup-footer { border-color: #334155; }
.payment-terms { font-size: 0.8rem; color: #64748b; font-weight: 500; }
.dark .payment-terms { color: #94a3b8; }


/* ---- Modal ---- */
.modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 50; display: flex; align-items: center; justify-content: center; opacity: 1; transition: opacity 0.3s; padding: 20px; }
.modal-backdrop.hidden { opacity: 0; pointer-events: none; }
.modal-panel { background: #fff; width: 100%; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; transform: scale(1); transition: transform 0.3s; max-height: 90vh; display: flex; flex-direction: column; }
.dark .modal-panel { background: #1e293b; border: 1px solid #334155; }
.modal-backdrop.hidden .modal-panel { transform: scale(0.95); }

.modal-header { padding: 24px 30px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
.dark .modal-header { border-color: #334155; }
.modal-title-wrap { display: flex; align-items: center; gap: 16px; }
.modal-icon { width: 44px; height: 44px; background: #eff6ff; color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.dark .modal-icon { background: rgba(59,130,246,0.1); }
.modal-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0; font-family: 'Poppins', sans-serif;}
.dark .modal-title { color: #f8fafc; }
.modal-close { background: none; border: none; font-size: 1.1rem; color: #94a3b8; cursor: pointer; padding: 4px; }
.modal-close:hover { color: #0f172a; }
.dark .modal-close:hover { color: #f8fafc; }

.modal-body { padding: 30px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group.full-width { width: 100%; }
.form-group label { font-size: 0.875rem; font-weight: 600; color: #334155; }
.dark .form-group label { color: #cbd5e1; }
.form-group input { padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.875rem; color: #0f172a; background: #fff; outline: none; transition: border-color 0.2s; width: 100%; font-family: 'Inter', sans-serif;}
.dark .form-group input { background: #0f172a; border-color: #334155; color: #f8fafc; }
.form-group input:focus { border-color: #3b82f6; }

.modal-footer { padding: 20px 30px; border-top: 1px solid #f1f5f9; display: flex; justify-content: center; gap: 12px; background: #fff; }
.dark .modal-footer { border-color: #334155; background: #1e293b; }
.btn-cancel { background: white; border: 1px solid #e2e8f0; padding: 10px 40px; border-radius: 10px; font-size: 0.875rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; }
.dark .btn-cancel { background: #1e293b; border-color: #334155; color: #cbd5e1; }
.btn-cancel:hover { background: #f1f5f9; color: #0f172a; }
.dark .btn-cancel:hover { background: #334155; color: #f8fafc; }

@media (max-width: 768px) {
    .main-content { margin-left: 0; }
    .suppliers-body { padding: 16px; }
    .topbar { padding: 14px 16px; }
    .search-box { display: none; }
    .form-row { grid-template-columns: 1fr; gap: 16px; }
    .sup-grid { grid-template-columns: 1fr; }
}
</style>

<script>
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

<?php include BASE_PATH . '/includes/footer.php'; ?>
