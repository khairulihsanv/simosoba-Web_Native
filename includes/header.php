<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="SiMoSoBa – Sistem Monitoring Stok Obat Cerdas untuk Manajemen Farmasi yang Akurat dan Efisien.">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – SiMoSoBa' : 'SiMoSoBa Dashboard' ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,400&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/main.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <!-- html2pdf for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <!-- html5-qrcode for barcode scanning -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <!-- Theme init (inline, before render to avoid flash) -->
    <script>
        (function() {
            const saved = localStorage.getItem('simo-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = saved || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body class="page-enter">

<!-- Toast Container -->
<div id="toast-container"></div>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="hidden" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:49;backdrop-filter:blur(2px);" onclick="closeSidebar()"></div>

<div class="app-shell">

    <!-- ═══ SIDEBAR ═══════════════════════════════════════════ -->
    <?php include BASE_PATH . '/includes/sidebar.php'; ?>

    <!-- ═══ MAIN AREA ════════════════════════════════════════ -->
    <div class="main-area">

        <!-- Topbar -->
        <header class="topbar" role="banner">
            <div class="flex items-center gap-3">
                <!-- Mobile menu button -->
                <button class="icon-btn" id="menu-toggle" onclick="toggleSidebar()" title="Toggle Menu" style="display:none" aria-label="Toggle navigation menu">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title" id="page-title"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?></h1>
            </div>

            <div class="topbar-actions">
                <!-- Search -->
                <div class="topbar-search" role="search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Search medications..." id="global-search" aria-label="Search medications">
                </div>

                <!-- Dark Mode Toggle -->
                <button class="icon-btn" id="theme-toggle" onclick="toggleTheme()" title="Toggle Dark Mode" aria-label="Toggle dark mode">
                    <i class="bi bi-moon-stars" id="theme-icon"></i>
                </button>

                <!-- Notification Bell -->
                <button class="icon-btn" onclick="window.location='<?= BASE_URL ?>/?page=alerts'" title="Alerts" aria-label="View alerts" style="position:relative">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge" id="notification-count" aria-label="Alert count">0</span>
                </button>

                <!-- User avatar -->
                <div class="user-avatar" title="<?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?>" aria-label="User menu" style="cursor:pointer" onclick="window.location='<?= BASE_URL ?>/?page=logout'">
                    <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                </div>
            </div>
        </header>

        <!-- Page Main Content -->
        <main class="main-content" role="main">