<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Meta SEO -->
    <meta name="description" content="Sistem Monitoring Stok Obat (SiMoSoBa) - Solusi Cerdas Manajemen Apotek">
    <meta name="author" content="SiMoSoBa Team">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- CDN Frameworks (Fallback untuk Vercel) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS (Absolute Path) -->
    <?php if (file_exists(BASE_PATH . '/css/style.css')): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <?php endif; ?>

    <script>
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
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
