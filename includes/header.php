<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Meta SEO -->
    <meta name="description" content="Sistem Monitoring Stok Obat (SiMoSoBa) - Solusi Cerdas Manajemen Apotek">
    <meta name="author" content="SiMoSoBa Team">

    <!-- Fonts (Google Fonts) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS (Gunakan jalur absolut dari root domain) -->
    <link rel="stylesheet" href="<?php echo '/css/style.css'; ?>">

    <!-- CDN Frameworks (Wajib CDN agar visual pasti muncul di Vercel) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        /**
         * Konfigurasi Tailwind CSS (JIT Mode via CDN)
         */
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 
                        navy: '#1e293b', 
                        emerald: '#10b981', 
                        softgrey: '#f8fafc',
                        amber: '#f59e0b',
                        rose: '#e11d48'
                    },
                    fontFamily: { 
                        sans: ['Inter', 'sans-serif'], 
                        display: ['Poppins', 'sans-serif'] 
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Global CSS Fallback */
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
