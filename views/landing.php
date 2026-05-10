<?php
/**
 * views/landing.php - Landing Page Content
 */
?>
<!-- Navbar -->
<nav class="fixed top-0 left-0 w-full z-50 glass border-b border-white/20">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="bi bi-capsule-pill text-xl"></i>
            </div>
            <span class="font-display font-bold text-2xl text-navy">SiMo<span class="text-emerald">SoBa</span></span>
        </div>
        <div class="flex items-center gap-8">
            <a href="#fitur" class="text-sm font-bold text-slate-600 hover:text-emerald transition-colors">Fitur</a>
            <a href="index.php?page=login" class="px-6 py-2.5 bg-navy text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition-all">Masuk</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="pt-40 pb-20 px-6">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
        <div>
            <span class="inline-block px-4 py-1.5 bg-emerald/10 text-emerald text-xs font-bold rounded-full mb-6 uppercase tracking-widest">Apotek Management 2.0</span>
            <h1 class="text-5xl lg:text-7xl font-display font-bold text-navy leading-[1.1] mb-8">
                Monitoring Stok <br><span class="text-emerald">Lebih Cerdas.</span>
            </h1>
            <p class="text-lg text-slate-600 mb-10 leading-relaxed max-w-lg">
                Optimalkan ketersediaan obat Anda dengan sistem monitoring cerdas yang memprediksi kebutuhan stok secara real-time.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="index.php?page=login" class="px-8 py-4 bg-emerald text-white font-bold rounded-2xl shadow-xl shadow-emerald/20 hover:scale-105 transition-all flex items-center gap-3">
                    Mulai Sekarang <i class="bi bi-arrow-right"></i>
                </a>
                <a href="#fitur" class="px-8 py-4 bg-white text-navy font-bold rounded-2xl border border-slate-200 hover:bg-slate-50 transition-all">
                    Lihat Fitur
                </a>
            </div>
        </div>
        <div class="relative">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-emerald/10 blur-3xl rounded-full"></div>
            <div class="bg-white p-4 rounded-[40px] shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-700">
                <img src="/api/assets/medical_illustration_minimalist_1778422468140.png" class="w-full rounded-[32px]">
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar -->
<div class="bg-navy py-12">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="text-center">
            <h4 class="text-4xl font-bold text-white mb-2">99%</h4>
            <p class="text-slate-400 text-sm font-medium">Akurasi Stok</p>
        </div>
        <div class="text-center border-l border-white/10">
            <h4 class="text-4xl font-bold text-white mb-2">24/7</h4>
            <p class="text-slate-400 text-sm font-medium">Monitoring Real-time</p>
        </div>
        <div class="text-center border-l border-white/10">
            <h4 class="text-4xl font-bold text-white mb-2">1k+</h4>
            <p class="text-slate-400 text-sm font-medium">Obat Terkelola</p>
        </div>
        <div class="text-center border-l border-white/10">
            <h4 class="text-4xl font-bold text-white mb-2">Fast</h4>
            <p class="text-slate-400 text-sm font-medium">Prediksi AI</p>
        </div>
    </div>
</div>
