<?php
/**
 * includes/sidebar.php - Navigation Sidebar
 */
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<aside class="w-64 bg-white dark:bg-dark-800 h-screen fixed left-0 top-0 text-navy dark:text-dark-text shadow-sm z-40 overflow-y-auto custom-scrollbar border-r border-slate-100 dark:border-dark-700 transition-colors duration-300 flex flex-col">
    <!-- Logo Section -->
    <div class="p-6 mb-2">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-emerald rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald/30">
                <i class="bi bi-capsule-pill text-xl"></i>
            </div>
            <div>
                <div class="font-display font-bold text-lg text-navy dark:text-white">SiMoSoBa</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Manajemen Stok</div>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="px-4 space-y-2 flex-1">
        <p class="px-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4">Menu Utama</p>
        
        <a href="index.php?page=dashboard" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'dashboard' ? 'bg-emerald text-white shadow-lg shadow-emerald/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700 hover:text-navy dark:hover:text-white' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="text-sm font-bold">Dashboard</span>
        </a>

        <a href="index.php?page=mutasi" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'mutasi' ? 'bg-emerald text-white shadow-lg shadow-emerald/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700 hover:text-navy dark:hover:text-white' ?>">
            <i class="bi bi-arrow-left-right"></i>
            <span class="text-sm font-bold">Mutasi Stok</span>
        </a>

        <?php if (in_array($_SESSION['role'] ?? '', ['super_admin', 'admin_staff'])): ?>
        <a href="index.php?page=stok" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'stok' ? 'bg-emerald text-white shadow-lg shadow-emerald/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700 hover:text-navy dark:hover:text-white' ?>">
            <i class="bi bi-capsule"></i>
            <span class="text-sm font-bold">Data Obat</span>
        </a>
        <?php endif; ?>

        <?php if (in_array($_SESSION['role'] ?? '', ['super_admin', 'admin_staff'])): ?>
        <a href="index.php?page=laporan" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'laporan' ? 'bg-emerald text-white shadow-lg shadow-emerald/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700 hover:text-navy dark:hover:text-white' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span class="text-sm font-bold">Laporan</span>
        </a>
        <?php endif; ?>

        <?php if (($_SESSION['role'] ?? '') === 'super_admin'): ?>
        <p class="px-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-8 mb-4">Administrator</p>
        <a href="index.php?page=users" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'users' ? 'bg-emerald text-white shadow-lg shadow-emerald/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700 hover:text-navy dark:hover:text-white' ?>">
            <i class="bi bi-people"></i>
            <span class="text-sm font-bold">Kelola User</span>
        </a>
        <a href="index.php?page=pengaturan" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'pengaturan' ? 'bg-emerald text-white shadow-lg shadow-emerald/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700 hover:text-navy dark:hover:text-white' ?>">
            <i class="bi bi-gear"></i>
            <span class="text-sm font-bold">Pengaturan Sistem</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Bottom Section -->
    <div class="p-4 space-y-2 mb-2">
        <!-- Theme Toggle -->
        <button @click="toggleTheme()" class="w-full flex items-center justify-between px-4 py-3.5 rounded-2xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700 hover:text-navy dark:hover:text-white transition-all font-bold text-sm border border-slate-100 dark:border-dark-700">
            <span class="flex items-center gap-3">
                <i class="bi" :class="isDark ? 'bi-moon-stars-fill' : 'bi-sun-fill'"></i>
                <span x-text="isDark ? 'Dark Mode' : 'Light Mode'"></span>
            </span>
            <div class="w-10 h-6 bg-slate-200 dark:bg-emerald rounded-full flex p-1 items-center transition-all duration-300 relative">
                <div class="w-4 h-4 bg-white rounded-full shadow-md transition-all duration-300 absolute" :class="isDark ? 'right-1' : 'left-1'"></div>
            </div>
        </button>

        <a href="index.php?page=logout" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400 transition-all font-bold text-sm">
            <i class="bi bi-box-arrow-left"></i>
            <span>Keluar Sistem</span>
        </a>
    </div>
</aside>
