<!DOCTYPE html>
<html lang="id" x-data="themeSwitcher()" x-bind:class="{ 'dark': isDark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="SiMoSoBa - Sistem Monitoring Stok Obat Cerdas untuk Manajemen Farmasi yang Akurat dan Efisien.">
    <title>Dashboard SiMoSoBa â€” Monitoring Stok Cerdas</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/base44-polish.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- CDN Frameworks -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        navy: '#1e293b',
                        emerald: '#10b981',
                        softgrey: '#f8fafc',
                        amber: '#f59e0b',
                        rose: '#e11d48',
                        // Dark Mode Colors
                        dark: {
                            900: '#0f172a', // Background utama
                            800: '#1e293b', // Sidebar / Cards
                            700: '#334155', // Borders
                            text: '#f8fafc' // Teks putih
                        }
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
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
        
        /* Responsive Table fix */
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        
        /* Glassmorphism utility */
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .dark .glass { background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.05); }

        /* Page transition */
        .page-enter { opacity: 0; transform: translateY(10px); }
        .page-enter-active { opacity: 1; transform: translateY(0); transition: opacity 0.4s ease-out, transform 0.4s ease-out; }
    </style>
    
    <script>
        // Alpine Logic for Theme Switcher
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeSwitcher', () => ({
                isDark: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            }))
        });

        // Simple script to add page-enter-active class on load for SPA feel
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('page-enter');
            setTimeout(() => {
                document.body.classList.add('page-enter-active');
                document.body.classList.remove('page-enter');
            }, 10);
        });
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200 dark:from-dark-900 dark:via-dark-900 dark:to-slate-900 text-navy dark:text-dark-text antialiased transition-colors duration-300">
