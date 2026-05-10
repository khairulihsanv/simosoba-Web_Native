<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Custom CSS (Absolute Path dari Root) -->
    <link rel="stylesheet" href="/css/style.css">

    <!-- CDN Frameworks (Wajib CDN untuk Vercel) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Debug: Memastikan script berjalan di Vercel
        console.log('SiMoSoBa Assets: CSS & Frameworks Check OK');

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
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
