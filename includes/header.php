<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="SiMoSoBa - Sistem Monitoring Stok Obat Cerdas untuk Manajemen Farmasi yang Akurat dan Efisien.">
    <title>Dashboard SiMoSoBa â€” Monitoring Stok Cerdas</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/main.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- CDN Frameworks -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Poppins', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        /* Responsive Table fix */
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body class="bg-softgrey text-navy antialiased">