<?php
/**
 * includes/sidebar.php - Navigation Sidebar
 */
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<aside class="sidebar-wrapper">
    <!-- Logo Section -->
    <div class="sidebar-logo-container">
        <div class="sidebar-logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
              <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/>
              <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/>
            </svg>
        </div>
        <div>
            <div class="sidebar-brand-name">Simosoba</div>
            <div class="sidebar-brand-sub">Stock Monitor</div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="sidebar-nav">
        <a href="index.php?page=dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <i class="bi bi-grid-1x2"></i>
                <span>Dashboard</span>
            </div>
            <?php if($currentPage === 'dashboard'): ?>
                <i class="bi bi-chevron-right nav-chevron"></i>
            <?php endif; ?>
        </a>

        <?php if (in_array($_SESSION['role'] ?? '', ['super_admin', 'admin_staff'])): ?>
        <a href="index.php?page=stok" class="nav-item <?= $currentPage === 'stok' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <i class="bi bi-box"></i>
                <span>Inventory</span>
            </div>
            <?php if($currentPage === 'stok'): ?>
                <i class="bi bi-chevron-right nav-chevron"></i>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <a href="index.php?page=mutasi" class="nav-item <?= $currentPage === 'mutasi' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <i class="bi bi-arrow-left-right"></i>
                <span>Transactions</span>
            </div>
            <?php if($currentPage === 'mutasi'): ?>
                <i class="bi bi-chevron-right nav-chevron"></i>
            <?php endif; ?>
        </a>

        <a href="index.php?page=suppliers" class="nav-item <?= $currentPage === 'suppliers' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <i class="bi bi-truck"></i>
                <span>Suppliers</span>
            </div>
            <?php if($currentPage === 'suppliers'): ?>
                <i class="bi bi-chevron-right nav-chevron"></i>
            <?php endif; ?>
        </a>

        <a href="index.php?page=alerts" class="nav-item <?= $currentPage === 'alerts' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <i class="bi bi-bell"></i>
                <span>Alerts</span>
            </div>
            <?php if($currentPage === 'alerts'): ?>
                <i class="bi bi-chevron-right nav-chevron"></i>
            <?php else: ?>
                <span class="nav-badge">4</span>
            <?php endif; ?>
        </a>

        <?php if (in_array($_SESSION['role'] ?? '', ['super_admin', 'admin_staff'])): ?>
        <a href="index.php?page=laporan" class="nav-item <?= $currentPage === 'laporan' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <i class="bi bi-bar-chart"></i>
                <span>Reports</span>
            </div>
            <?php if($currentPage === 'laporan'): ?>
                <i class="bi bi-chevron-right nav-chevron"></i>
            <?php endif; ?>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Bottom Section -->
    <div class="sidebar-bottom">
        <?php if (($_SESSION['role'] ?? '') === 'super_admin'): ?>
        <a href="index.php?page=pengaturan" class="nav-item <?= $currentPage === 'pengaturan' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </div>
        </a>
        <?php endif; ?>
    </div>
</aside>

<style>
/* ============================================
   SIDEBAR LAYOUT - BASE44 STYLE
   ============================================ */
.sidebar-wrapper {
    width: 240px;
    height: 100vh;
    background-color: #0f172a; /* Dark navy background */
    position: fixed;
    left: 0;
    top: 0;
    z-index: 40;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.sidebar-logo-container {
    padding: 24px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.sidebar-logo-icon {
    width: 40px;
    height: 40px;
    background-color: #3b82f6; /* Blue */
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.sidebar-brand-name {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 1.1rem;
    color: #f8fafc;
    line-height: 1.2;
}

.sidebar-brand-sub {
    font-family: 'Inter', sans-serif;
    font-size: 0.75rem;
    color: #94a3b8;
}

.sidebar-nav {
    flex: 1;
    padding: 0 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.nav-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-radius: 12px;
    text-decoration: none;
    color: #94a3b8;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
}

.nav-item:hover {
    color: #f8fafc;
}

.nav-item.active {
    background-color: #1e293b;
    color: #f8fafc;
    box-shadow: inset 2px 0 0 0 #3b82f6; /* subtle left border highlight like some themes, or just solid bg */
}
/* Specifically match the active state in the image (blueish background) */
.nav-item.active {
    background-color: #1e3a8a; /* Deep blue active background */
    box-shadow: none;
}

.nav-item-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.nav-item-left i {
    font-size: 1.1rem;
}

.nav-item-left span {
    font-size: 0.875rem;
    font-weight: 500;
}

.nav-chevron {
    font-size: 0.8rem;
    color: #cbd5e1;
}

.nav-badge {
    background-color: #ef4444;
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.sidebar-bottom {
    padding: 16px;
    margin-bottom: 8px;
}
</style>
