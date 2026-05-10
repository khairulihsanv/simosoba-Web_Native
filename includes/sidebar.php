<?php
/**
 * includes/sidebar.php - Navigation Sidebar
 */
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<aside class="w-64 bg-navy h-screen fixed left-0 top-0 text-white shadow-2xl z-40 overflow-y-auto custom-scrollbar">
    <!-- Logo Section -->
    <div class="p-8 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald/20">
                <i class="bi bi-capsule-pill text-xl"></i>
            </div>
            <span class="font-display font-bold text-xl tracking-tight">SiMo<span class="text-emerald">SoBa</span></span>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="px-4 space-y-2">
        <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Menu Utama</p>
        
        <a href="index.php?page=dashboard" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'dashboard' ? 'bg-emerald text-white shadow-lg shadow-emerald/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="text-sm font-bold">Dashboard</span>
        </a>

        <a href="index.php?page=stok" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'stok' ? 'bg-emerald text-white shadow-lg shadow-emerald/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' ?>">
            <i class="bi bi-capsule"></i>
            <span class="text-sm font-bold">Data Obat</span>
        </a>

        <a href="index.php?page=laporan" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'laporan' ? 'bg-emerald text-white shadow-lg shadow-emerald/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span class="text-sm font-bold">Laporan</span>
        </a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
        <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-8 mb-4">Administrator</p>
        <a href="index.php?page=users" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all group <?= $currentPage === 'users' ? 'bg-emerald text-white shadow-lg shadow-emerald/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' ?>">
            <i class="bi bi-people"></i>
            <span class="text-sm font-bold">Kelola User</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Bottom Section -->
    <div class="absolute bottom-8 left-0 w-full px-4">
        <a href="index.php?page=logout" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-all font-bold text-sm">
            <i class="bi bi-box-arrow-left"></i>
            <span>Keluar Sistem</span>
        </a>
    </div>
</aside>
