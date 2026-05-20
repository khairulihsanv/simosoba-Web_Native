<?php
/**
 * views/landing.php - Landing Page Content
 */
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200">
    <nav class="fixed top-0 left-0 w-full z-50 bg-white/90 backdrop-blur-xl border-b border-slate-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-3xl bg-emerald text-white flex items-center justify-center shadow-md shadow-emerald/20">
                    <i class="bi bi-capsule-pill text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.35em] text-emerald">SiMoSoBa</p>
                    <p class="text-xs text-slate-500">Manajemen Stok Obat</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="#fitur" class="text-sm font-medium text-slate-600 hover:text-navy transition-colors">Fitur</a>
                <a href="index.php?page=login" class="inline-flex items-center gap-2 rounded-full bg-navy px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-navy/10 hover:bg-slate-900 transition-all">Masuk <i class="bi bi-box-arrow-in-right"></i></a>
            </div>
        </div>
    </nav>

    <main class="pt-28 pb-20 px-6">
        <div class="max-w-7xl mx-auto grid gap-16 lg:grid-cols-2 lg:items-center">
            <div class="space-y-8">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.35em] text-emerald shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald"></span> Sistem Monitoring Cerdas
                </div>
                <div class="space-y-6">
                    <h1 class="text-5xl font-display font-bold tracking-tight text-navy sm:text-6xl">Kelola stok obat dengan lebih <span class="text-emerald">aman</span> dan <span class="text-navy">terencana</span>.</h1>
                    <p class="max-w-xl text-lg text-slate-600 leading-relaxed">SiMoSoBa membantu apotek dan klinik memantau persediaan obat, prediksi kedaluwarsa, dan mengurangi kekurangan stok melalui tampilan yang simpel dan mudah digunakan.</p>
                </div>
                <div class="flex flex-wrap gap-4">
                    <a href="index.php?page=login" class="inline-flex items-center gap-2 rounded-full bg-emerald px-7 py-4 text-sm font-semibold text-white shadow-xl shadow-emerald/25 hover:scale-105 transition-transform">Masuk Sekarang <i class="bi bi-arrow-right"></i></a>
                    <a href="#fitur" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-7 py-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Lihat Fitur</a>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-3xl bg-white p-5 border border-slate-200 shadow-sm text-center">
                        <p class="text-sm text-slate-500">Akurasi</p>
                        <p class="mt-3 text-2xl font-bold text-navy">99%</p>
                    </div>
                    <div class="rounded-3xl bg-white p-5 border border-slate-200 shadow-sm text-center">
                        <p class="text-sm text-slate-500">Realtime</p>
                        <p class="mt-3 text-2xl font-bold text-navy">24/7</p>
                    </div>
                    <div class="rounded-3xl bg-white p-5 border border-slate-200 shadow-sm text-center">
                        <p class="text-sm text-slate-500">Obat</p>
                        <p class="mt-3 text-2xl font-bold text-navy">1K+</p>
                    </div>
                    <div class="rounded-3xl bg-white p-5 border border-slate-200 shadow-sm text-center">
                        <p class="text-sm text-slate-500">Prediksi</p>
                        <p class="mt-3 text-2xl font-bold text-navy">Fast</p>
                    </div>
                </div>
            </div>
            <div class="relative">
                <div class="absolute -left-10 top-10 h-40 w-40 rounded-full bg-emerald/10 blur-3xl"></div>
                <div class="rounded-[40px] overflow-hidden border border-slate-200 shadow-2xl bg-white">
                    <img src="https://images.unsplash.com/photo-1581091215367-4d7d3ae963e9?auto=format&fit=crop&w=1000&q=80" alt="Ilustrasi manajemen stok obat" class="w-full h-full object-cover min-h-[520px]">
                </div>
            </div>
        </div>

        <section id="fitur" class="mt-24 rounded-[40px] bg-white p-8 shadow-xl border border-slate-200">
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="space-y-4 rounded-[32px] border border-slate-200 p-8 hover:shadow-2xl transition-all">
                    <div class="inline-flex h-14 w-14 items-center justify-center rounded-3xl bg-emerald/10 text-emerald text-2xl"><i class="bi bi-graph-up"></i></div>
                    <h3 class="text-xl font-bold text-navy">Intuitif & Ringkas</h3>
                    <p class="text-slate-600">Antarmuka yang mudah dipahami untuk semua level staf farmasi.</p>
                </div>
                <div class="space-y-4 rounded-[32px] border border-slate-200 p-8 hover:shadow-2xl transition-all">
                    <div class="inline-flex h-14 w-14 items-center justify-center rounded-3xl bg-emerald/10 text-emerald text-2xl"><i class="bi bi-clipboard-data"></i></div>
                    <h3 class="text-xl font-bold text-navy">Laporan Cepat</h3>
                    <p class="text-slate-600">Lihat ringkasan stok, kadaluarsa, dan mutasi dalam sekali pandang.</p>
                </div>
                <div class="space-y-4 rounded-[32px] border border-slate-200 p-8 hover:shadow-2xl transition-all">
                    <div class="inline-flex h-14 w-14 items-center justify-center rounded-3xl bg-emerald/10 text-emerald text-2xl"><i class="bi bi-shield-lock"></i></div>
                    <h3 class="text-xl font-bold text-navy">Tersedia & Aman</h3>
                    <p class="text-slate-600">Data tersimpan dalam sistem dengan akses login terkontrol.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200/80 bg-slate-50 py-10 text-center text-slate-500 text-sm">
        &copy; 2026 SiMoSoBa System. Hak Cipta Dilindungi.
    </footer>
</div>
