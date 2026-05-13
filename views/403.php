<?php if (!defined('BASE_PATH')) die('Access Denied'); ?>
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center p-8">
    <div class="w-24 h-24 bg-rose/10 text-rose rounded-3xl flex items-center justify-center text-5xl mb-6">
        <i class="bi bi-shield-lock"></i>
    </div>
    <h1 class="text-4xl font-display font-bold text-navy mb-4">403 — Akses Ditolak</h1>
    <p class="text-slate-500 max-w-md mb-8">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Halaman ini diproteksi khusus untuk Administrator.</p>
    <a href="index.php?page=dashboard" class="px-8 py-3 bg-navy text-white font-bold rounded-2xl shadow-lg hover:shadow-navy/20 transition-all">
        Kembali ke Dashboard
    </a>
</div>
