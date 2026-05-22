<?php
/**
 * includes/sidebar.php – Premium Sidebar Navigation
 */
$currentPage = $_GET['page'] ?? 'dashboard';
$userRole    = $_SESSION['role'] ?? 'user';
$userName    = $_SESSION['nama'] ?? 'User';
$userEmail   = $_SESSION['email'] ?? '';
?>
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon" aria-hidden="true">
            <i class="bi bi-capsule-pill"></i>
        </div>
        <div>
            <div class="sidebar-brand">SiMoSoBa</div>
            <div class="sidebar-brand-sub">Stock Monitor v2.0</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav" aria-label="Dashboard navigation">

        <div class="sidebar-section-label">Overview</div>

        <a href="<?= BASE_URL ?>/?page=dashboard"
           class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>"
           id="nav-dashboard" aria-current="<?= $currentPage === 'dashboard' ? 'page' : 'false' ?>">
            <div class="nav-link-left">
                <i class="bi bi-grid-1x2" aria-hidden="true"></i>
                <span>Dashboard</span>
            </div>
        </a>

        <div class="sidebar-section-label" style="margin-top:8px">Inventory</div>

        <?php if (in_array($userRole, ['super_admin', 'admin_staff'])): ?>
        <a href="<?= BASE_URL ?>/?page=stok"
           class="nav-link <?= $currentPage === 'stok' ? 'active' : '' ?>"
           id="nav-stok" aria-current="<?= $currentPage === 'stok' ? 'page' : 'false' ?>">
            <div class="nav-link-left">
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <span>Inventory</span>
            </div>
        </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/?page=mutasi"
           class="nav-link <?= $currentPage === 'mutasi' ? 'active' : '' ?>"
           id="nav-mutasi" aria-current="<?= $currentPage === 'mutasi' ? 'page' : 'false' ?>">
            <div class="nav-link-left">
                <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                <span>Transactions</span>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/?page=suppliers"
           class="nav-link <?= $currentPage === 'suppliers' ? 'active' : '' ?>"
           id="nav-suppliers" aria-current="<?= $currentPage === 'suppliers' ? 'page' : 'false' ?>">
            <div class="nav-link-left">
                <i class="bi bi-truck" aria-hidden="true"></i>
                <span>Suppliers</span>
            </div>
        </a>

        <div class="sidebar-section-label" style="margin-top:8px">Monitoring</div>

        <a href="<?= BASE_URL ?>/?page=alerts"
           class="nav-link <?= $currentPage === 'alerts' ? 'active' : '' ?>"
           id="nav-alerts" aria-current="<?= $currentPage === 'alerts' ? 'page' : 'false' ?>">
            <div class="nav-link-left">
                <i class="bi bi-bell" aria-hidden="true"></i>
                <span>Alerts</span>
            </div>
            <?php
            $alertCount = 0;
            try {
                $alertCount = (int)$pdo->query("SELECT COUNT(*) FROM obat WHERE stok <= stok_min OR (exp_date IS NOT NULL AND exp_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))")->fetchColumn();
            } catch(Throwable $e) {}
            if($alertCount > 0): ?>
            <span class="nav-badge" aria-label="<?= $alertCount ?> alerts"><?= $alertCount ?></span>
            <?php endif; ?>
        </a>

        <?php if (in_array($userRole, ['super_admin', 'admin_staff'])): ?>
        <a href="<?= BASE_URL ?>/?page=laporan"
           class="nav-link <?= $currentPage === 'laporan' ? 'active' : '' ?>"
           id="nav-laporan" aria-current="<?= $currentPage === 'laporan' ? 'page' : 'false' ?>">
            <div class="nav-link-left">
                <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i>
                <span>Reports</span>
            </div>
        </a>
        <?php endif; ?>

    </nav>

    <!-- Bottom: User + Logout -->
    <div class="sidebar-bottom">
        <?php if ($userRole === 'super_admin'): ?>
        <a href="<?= BASE_URL ?>/?page=users"
           class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>"
           style="margin-bottom:6px"
           id="nav-users">
            <div class="nav-link-left">
                <i class="bi bi-people" aria-hidden="true"></i>
                <span>Users</span>
            </div>
        </a>
        <?php endif; ?>

        <div class="sidebar-user" onclick="window.location='<?= BASE_URL ?>/?page=logout'" title="Logout" role="button" aria-label="Logout">
            <div class="user-avatar" aria-hidden="true">
                <?= strtoupper(substr($userName, 0, 1)) ?>
            </div>
            <div>
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-role"><?= htmlspecialchars(str_replace('_', ' ', $userRole)) ?></div>
            </div>
            <i class="bi bi-box-arrow-right" style="color:#64748b;margin-left:auto;font-size:.9rem" aria-hidden="true"></i>
        </div>
    </div>
</aside>