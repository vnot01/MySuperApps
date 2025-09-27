<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed" dir="ltr" data-skin="default" data-assets-path="../../assets/" data-template="horizontal-menu-template" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>RVM Dashboard - MyRVM Platform</title>
    <meta name="description" content="RVM Monitoring Dashboard">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet">

    <!-- External Icon Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/vendor/fonts/iconify-icons.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../assets/vendor/libs/node-waves/node-waves.css">
    <link rel="stylesheet" href="../../assets/vendor/libs/pickr/pickr-themes.css">
    <link rel="stylesheet" href="../../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assets/css/demo.css">

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../../assets/vendor/libs/apex-charts/apex-charts.css">
    <link rel="stylesheet" href="../../assets/vendor/libs/swiper/swiper.css">
    <link rel="stylesheet" href="../../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet" href="../../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="../../assets/vendor/fonts/flag-icons.css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="../../assets/vendor/css/pages/cards-advance.css">
    <link rel="stylesheet" href="../../assets/vendor/libs/chartjs/chartjs.css">


    <!-- Helpers -->
    <script src="../../assets/vendor/js/helpers.js"></script>
    <script src="../../assets/vendor/libs/pickr/pickr.js"></script>
    <script src="../../assets/vendor/js/template-customizer.js"></script>
    <script src="../../assets/js/config.js"></script>
    
    <!-- Custom RVM Styles -->
    <style>
        /* Modern Glassmorphism & Smooth Animations */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-soft: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        /* Smooth Page Transitions */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Reduce container padding for better spacing */
        .container-p-y {
            padding-top: 0.5rem !important;
            padding-bottom: 1.5rem !important;
        }

        /* Reduce page header margin */
        .modern-header {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Consistent spacing for all rows */
        .row.mb-4 {
            margin-bottom: 1.5rem !important;
        }

        /* Reduce top margin for first row (page header) */
        .row.mb-4:first-child {
            margin-top: 0 !important;
        }

        /* Modern Navbar Styles */
        .modern-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
        }

        /* Menu Toggle Styles */
        .menu-toggle {
            position: relative;
        }

        /* .menu-toggle::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        } */

        /* .menu-toggle[aria-expanded="true"]::after {
            transform: translateY(-50%) rotate(180deg);
        } */

        .menu-sub {
            display: none;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-top: 5px;
            padding: 8px 0;
            min-width: 200px;
            animation: slideDown 0.3s ease;
        }

        .menu-sub.show {
            display: block;
        }

        /* Aturan untuk sub-menu level kedua (jika ada) */
        .menu-sub .menu-item {
            position: relative; /* Diperlukan agar sub-sub-menu bisa diposisikan */
            /* list-style: none; untuk menghilangkan titik putih */
        }

        .menu-sub .menu-link {
            padding: 10px 20px;
            color: #6c757d;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: block; /* Agar link memenuhi area li */
            white-space: nowrap; /* Agar teks tidak terpotong */
        }

        .menu-sub .menu-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateX(5px);
        }

        .menu-sub .menu-sub {
            top: 0;
            left: 100%; /* Posisikan di sebelah kanan parent */
            margin-top: 0;
            margin-left: 1px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modern-navbar .navbar-brand {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 1.5rem;
        }

        /* Sticky Navbar Enhancement */
        .layout-navbar {
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Navbar scroll effect */
        .modern-navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        /* Modern Content Wrapper */
        .modern-content {
            padding: 0rem 0;
        }

        /* Modern Page Header */
        .modern-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-title {
            font-size: 2.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: #64748b;
        }
        
        /* Modern Button Styles */
        .btn-modern {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-modern:hover::before {
            left: 100%;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-primary.btn-modern {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-outline-primary.btn-modern {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
        }

        .btn-outline-primary.btn-modern:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }
        
        /* Modern Statistics Cards */
        .modern-stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modern-stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.98);
        }

        /* Statistics Overview Card */
        .statistics-overview-card .card-body .row .col-6 {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            min-height: 120px;
            display: flex;
            align-items: center;
        }

        .statistics-overview-card .card-body .row .col-6:nth-child(2n) {
            border-right: none;
        }

        .statistics-overview-card .card-body .row .col-6:nth-child(n+3) {
            border-bottom: none;
        }

        .statistics-overview-card .card-body .row .col-6:hover {
            background: rgba(0, 0, 0, 0.02);
            transition: background 0.3s ease;
        }

        .statistics-overview-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .statistics-overview-card .card-title {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Ensure consistent subtitle styling */
        .statistics-overview-card .card-subtitle {
            font-size: 0.875rem !important;
            font-weight: 400 !important;
            color: #6c757d !important;
        }

        .card-subtitle {
            font-size: 0.875rem !important;
            font-weight: 400 !important;
            color: #6c757d !important;
        }

        .statistics-overview-card .stat-trend {
            font-size: 0.75rem;
        }

        .stat-icon-wrapper {
            position: relative;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .stat-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), transparent);
            border-radius: 16px;
        }

        .stat-trend {
            display: flex;
            align-items: center;
        }

        .trend-indicator {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .trend-indicator.positive {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .trend-indicator.negative {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: translateX(100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10000;
            max-width: 400px;
            font-size: 14px;
            line-height: 1.4;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .notification-success i {
            color: #10b981;
        }

        .notification-error i {
            color: #ef4444;
        }

        .notification-warning i {
            color: #f59e0b;
        }

        .notification-info i {
            color: #3b82f6;
        }
        
        /* Loading Animation & Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            backdrop-filter: blur(10px);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .loading-spinner-large, .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .loading-text {
            margin-top: 1rem;
            color: #374151;
            font-weight: 500;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* RVM Cards */
        .rvm-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 6px solid transparent;
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
        }
        
        .rvm-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }

        .rvm-card.active { border-left-color: #10b981; }
        .rvm-card.inactive { border-left-color: #6b7280; }
        .rvm-card.maintenance { border-left-color: #f59e0b; }
        .rvm-card.full { border-left-color: #ef4444; }
        .rvm-card.error { border-left-color: #dc2626; }
        .rvm-card.unknown { border-left-color: #9ca3af; }

        /* RVM Cards Container */
        #rvm-cards-container {
            min-height: 200px;
        }

        /* Chart Container */
            .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        #statusChart {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        #rvm-cards-container .col-md-6,
        #rvm-cards-container .col-lg-4 {
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Ensure RVM cards are visible */
        .rvm-card {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }

        /* Menu Toggle Styles */
        .menu-item .menu-sub {
            display: none; /* Sembunyikan secara default */
            position: absolute; /* PENTING untuk menu horizontal agar tidak mendorong item lain */
            background: white;
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-top: 5px; /* Jarak dari parent */
            padding: 8px 0;
            min-width: 220px; /* Lebar minimum dropdown */
            z-index: 1050; /* Pastikan di atas konten lain */
            list-style: none; /* Hapus bullet points */
            animation: slideDown 0.2s ease-out; /* Animasi halus */
        }

        .menu-item.open > .menu-sub {
            display: block;
        }

        .menu-item.open > .menu-link {
            color: #667eea !important;
        }
        /* ============================================= */
        /* Hapus Paksa Semua Panah Menu */
        /* ============================================= */
        .layout-menu .menu-link.menu-toggle::after {
            content: none !important;
        }
        /* ======================================================= */
        /* Hapus Lingkaran/Penanda Kustom */
        /* ======================================================= */
        .menu-inner .menu-sub .menu-link::before {
            content: none !important;
        }
        /* .menu-item.open > .menu-link::after {
            transform: rotate(180deg);
        } */
        /* Hapus panah bawaan yang mungkin tidak berfungsi dengan baik */
        /* .menu-link::after {
            content: none !important;
        } */
        /* Aturan untuk panah pada menu toggle jika Anda masih ingin menggunakannya */
        /* .menu-link.menu-toggle::after {
            content: '\f107'; /* Font Awesome down arrow * /
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            display: inline-block;
            margin-left: auto; /* Posisikan di kanan * /
            transition: transform 0.3s ease;
        } */
        /* .menu-item.open > .menu-link.menu-toggle::after {
            transform: rotate(-90deg); /* Panah menunjuk ke kanan saat menu terbuka * /
        } */

        /* Untuk sub-menu di dalam sub-menu */
        /* .menu-sub .menu-item.open > .menu-link.menu-toggle::after {
            transform: rotate(-90deg);
        } */

        /* Status Indicators */
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            position: relative;
        }
        
        .status-dot.active { 
            background-color: #10b981; 
            animation: pulse-green 2s infinite;
        }
        .status-dot.inactive { background-color: #6b7280; }
        .status-dot.maintenance { 
            background-color: #f59e0b; 
            animation: pulse-yellow 2s infinite;
        }
        .status-dot.full, .status-dot.error { 
            background-color: #ef4444; 
            animation: pulse-red 2s infinite;
        }
        .status-dot.unknown { background-color: #9ca3af; }

        /* Pulse Animations */
        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        }
        @keyframes pulse-yellow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
        }
        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
        }

        /* === TAMBAHKAN ATURAN INI DI AKHIR === */

        /* Paksa sembunyikan backdrop bawaan template yang mungkin macet */
        .content-backdrop {
                display: none !important;
            }
            
        /* Pastikan content-wrapper tidak tersembunyi oleh template */
        .content-wrapper {
            visibility: visible !important;
            opacity: 1 !important;
        }
    </style>
    
    <script>
        // Data from Controller
        const serverData = {
            rvms: @json($rvms),
            statistics: @json($statistics),
            timezoneConfig: @json($timezoneConfig)
        };
        
        // Configuration
        const config = {
            apiBaseUrl: '{{ url('/api/v2') }}',
            csrfToken: '{{ csrf_token() }}',
            refreshInterval: 30000, // 30 seconds
            timezone: '{{ env("APP_TIMEZONE", "Asia/Jakarta") }}',
            dateFormat: '{{ env("APP_DATE_FORMAT", "Y-m-d") }}',
            timeFormat: '{{ env("APP_TIME_FORMAT", "H:i:s") }}',
            datetimeFormat: '{{ env("APP_DATETIME_FORMAT", "Y-m-d H:i:s") }}',
            displayTimezone: '{{ env("APP_DISPLAY_TIMEZONE", "WIB") }}'
        };

        // Global variables
        let monitoringData = null;
        let statusChart = null;
        let currentRvmId = null;
        let refreshIntervalTimer = null;
        let rvmStatusChanges = {}; // Store RVM status changes
        let currentPage = 1;
        let itemsPerPage = 12;

        // --- Core UI Functions ---

        function showLoadingAnimation() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.display = 'flex';
                // Trigger reflow to ensure the transition is applied
                overlay.offsetHeight; 
                overlay.style.opacity = '1';
                overlay.style.visibility = 'visible';
            }
        }

        function hideLoadingAnimation() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.opacity = '0';
                overlay.style.visibility = 'hidden';
                // The display:none can be handled by the transitionend event, or a simple timeout.
                // Timeout is simpler and sufficient here.
                setTimeout(() => {
                    if (overlay.style.opacity === '0') { // Check if it's still meant to be hidden
                        overlay.style.display = 'none';
                    }
                }, 300); // Should match CSS transition duration
            }
        }
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            
            const icons = { 
                success: 'fas fa-check-circle', 
                error: 'fas fa-times-circle', 
                warning: 'fas fa-exclamation-triangle', 
                info: 'fas fa-info-circle' 
            };
            const icon = icons[type] || icons.info;
            
            notification.innerHTML = `<div class="d-flex align-items-center"><i class="${icon} me-2"></i> ${message}</div>`;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.classList.add('show'), 10);
            setTimeout(() => {
                notification.classList.remove('show');
                notification.addEventListener('transitionend', () => notification.remove());
            }, 3000);
        }

        // --- Dashboard Logic ---

        async function initializeDashboard() {
            try {
                showLoadingAnimation();
                
                // Load saved RVM status changes first
                loadRvmStatusChanges();
                
                await Promise.all([
                    initializeStatusChart(),
                    setupEventListeners(),
                ]);
                
                await loadMonitoringData();
                startAutoRefresh();

            } catch (error) {
                console.error('Error initializing dashboard:', error);
                showNotification('Failed to initialize dashboard', 'error');
            } finally {
                hideLoadingAnimation();
            }
        }

        async function loadMonitoringData() {
            try {
                showLoadingAnimation();
                
                console.log('Loading monitoring data from server...');
                
                // Use server data instead of mock data
                const serverMonitoringData = {
                    rvms: serverData.rvms,
                    statistics: serverData.statistics
                };
                
                console.log('Server data loaded:', serverMonitoringData);
                
                // Apply saved status changes to server data
                if (serverMonitoringData.rvms && Object.keys(rvmStatusChanges).length > 0) {
                    console.log('Applying saved status changes:', rvmStatusChanges);
                    serverMonitoringData.rvms.forEach(rvm => {
                        if (rvmStatusChanges[rvm.id]) {
                            rvm.calculated_status = rvmStatusChanges[rvm.id].status;
                            rvm.last_seen = rvmStatusChanges[rvm.id].last_seen;
                        }
                    });
                }
                
                monitoringData = serverMonitoringData;
                
                console.log('Updating dashboard components...');
                updateStatistics(serverMonitoringData.statistics);
                updateRvmCards(serverMonitoringData.rvms);
                updateStatusChart();
                updateLastUpdated();
                
                console.log('Dashboard data loaded successfully from server');
                
            } catch (error) {
                console.error('Error loading monitoring data:', error);
                console.error('Error stack:', error.stack);
                showNotification('Error loading dashboard data: ' + error.message, 'error');
            } finally {
                hideLoadingAnimation();
            }
        }

        // --- Timezone and DateTime Functions ---

        function formatDateTime(date, format = 'datetime') {
            try {
                const d = new Date(date);
                
                // Check if date is valid
                if (isNaN(d.getTime())) {
                    console.warn('Invalid date provided to formatDateTime:', date);
                    return 'Invalid Date';
                }
                
                // Convert to configured timezone
                const options = {
                    timeZone: config.timezone || 'Asia/Jakarta',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                };
                
                const formatted = d.toLocaleString('en-US', options);
                
                // Add timezone display
                return `${formatted} ${config.displayTimezone || 'WIB'}`;
                
            } catch (error) {
                console.error('Error formatting datetime:', error);
                return date ? date.toString() : 'Unknown';
            }
        }

        function formatTime(date) {
            try {
                const d = new Date(date);
                
                // Check if date is valid
                if (isNaN(d.getTime())) {
                    console.warn('Invalid date provided to formatTime:', date);
                    return 'Invalid Date';
                }
                
                const options = {
                    timeZone: config.timezone || 'Asia/Jakarta',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                };
                
                return d.toLocaleTimeString('en-US', options);
                
            } catch (error) {
                console.error('Error formatting time:', error, 'Date:', date);
                return date ? date.toString() : 'Unknown';
            }
        }

        function formatDate(date) {
            try {
                const d = new Date(date);
                
                // Check if date is valid
                if (isNaN(d.getTime())) {
                    console.warn('Invalid date provided to formatDate:', date);
                    return 'Invalid Date';
                }
                
                const options = {
                    timeZone: config.timezone || 'Asia/Jakarta',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                };
                
                return d.toLocaleDateString('en-US', options);
                
            } catch (error) {
                console.error('Error formatting date:', error);
                return date ? date.toString() : 'Unknown';
            }
        }

        function getCurrentDateTime() {
            const now = new Date();
            return formatDateTime(now);
        }

        function getCurrentTime() {
            const now = new Date();
            return formatTime(now);
        }

        function getCurrentDate() {
            const now = new Date();
            return formatDate(now);
        }

        // --- RVM Status Logic Functions ---

        function determineRvmStatus(capacity, specialStatus = null) {
            // If there's a special status (maintenance, inactive, error, unknown), use it
            if (specialStatus && ['maintenance', 'inactive', 'error', 'unknown'].includes(specialStatus)) {
                return specialStatus;
            }
            
            // Determine status based on capacity
            if (capacity >= 100) {
                return 'full';
            } else if (capacity >= 0) {
                return 'active';
            } else {
                return 'unknown';
            }
        }

        function getStatusInfo(status) {
            const statusMap = {
                'active': { 
                    color: 'success', 
                    icon: 'fas fa-check-circle', 
                    label: 'Active',
                    description: 'RVM is operational and ready'
                },
                'full': { 
                    color: 'danger', 
                    icon: 'fas fa-exclamation-triangle', 
                    label: 'Full',
                    description: 'RVM storage is full'
                },
                'maintenance': { 
                    color: 'warning', 
                    icon: 'fas fa-tools', 
                    label: 'Maintenance',
                    description: 'RVM is under maintenance'
                },
                'inactive': { 
                    color: 'secondary', 
                    icon: 'fas fa-pause-circle', 
                    label: 'Inactive',
                    description: 'RVM is offline or disabled'
                },
                'error': { 
                    color: 'danger', 
                    icon: 'fas fa-times-circle', 
                    label: 'Error',
                    description: 'RVM has encountered an error'
                },
                'unknown': { 
                    color: 'info', 
                    icon: 'fas fa-question-circle', 
                    label: 'Unknown',
                    description: 'Status cannot be determined'
                }
            };
            
            return statusMap[status] || statusMap['unknown'];
        }

        // --- Data Update Functions ---

        function saveRvmStatusChange(rvmId, newStatus) {
            // Save RVM status change to persist across refreshes
            rvmStatusChanges[rvmId] = {
                status: newStatus,
                last_seen: getCurrentTime(),
                timestamp: Date.now()
            };
            
            // Store in localStorage for persistence across page reloads
            localStorage.setItem('rvmStatusChanges', JSON.stringify(rvmStatusChanges));
            
            console.log(`Saved status change for RVM-${rvmId}: ${newStatus} at ${getCurrentTime()}`);
        }

        function loadRvmStatusChanges() {
            // Load saved status changes from localStorage
            const saved = localStorage.getItem('rvmStatusChanges');
            if (saved) {
                try {
                    rvmStatusChanges = JSON.parse(saved);
                    console.log('Loaded saved RVM status changes:', rvmStatusChanges);
                } catch (error) {
                    console.error('Error loading saved status changes:', error);
                    rvmStatusChanges = {};
                }
            }
        }

        function clearRvmStatusChange(rvmId) {
            // Clear saved status change for specific RVM
            if (rvmStatusChanges[rvmId]) {
                delete rvmStatusChanges[rvmId];
                localStorage.setItem('rvmStatusChanges', JSON.stringify(rvmStatusChanges));
                console.log(`Cleared status change for RVM-${rvmId}`);
            }
        }

        function updateStatistics(stats) {
            animateNumber('total-rvm', stats.total_rvm);
            animateNumber('active-sessions', stats.active_sessions);
            animateNumber('deposits-today', stats.deposits_today);
            animateNumber('total-issues', stats.total_issues);
        }
        
        function animateNumber(elementId, targetValue) {
            const element = document.getElementById(elementId);
            if (!element) return;
            
            const startValue = parseInt(element.textContent, 10) || 0;
            if (startValue === targetValue) return;

            const duration = 1000;
            let startTime = null;

            function animation(currentTime) {
                if (startTime === null) startTime = currentTime;
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const currentValue = Math.floor(progress * (targetValue - startValue) + startValue);
                
                element.textContent = currentValue;
                
                if (progress < 1) requestAnimationFrame(animation);
            }
            requestAnimationFrame(animation);
        }

        function updateRvmCards(rvms) {
            const container = document.getElementById('rvm-cards-container');
            if (!container) return;
            
            container.innerHTML = '';
            
            // Calculate pagination
            const totalItems = rvms.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
            const currentPageItems = rvms.slice(startIndex, endIndex);
            
            // Update pagination info
            updatePaginationInfo(startIndex + 1, endIndex, totalItems);
            updatePaginationControls(currentPage, totalPages);
            
            // Render current page items
            currentPageItems.forEach((rvm, index) => {
                const card = createRvmCard(rvm);
                card.style.animation = `fadeInUp 0.5s ${index * 0.05}s ease-out forwards`;
                container.appendChild(card);
            });
        }

        function createRvmCard(rvm) {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';
            col.style.opacity = 0; // for animation
            
            const statusInfo = {
                active: { text: 'text-success', icon: 'fas fa-check-circle' },
                inactive: { text: 'text-secondary', icon: 'fas fa-pause-circle' },
                maintenance: { text: 'text-warning', icon: 'fas fa-tools' },
                full: { text: 'text-danger', icon: 'fas fa-exclamation-triangle' },
                error: { text: 'text-danger', icon: 'fas fa-times-circle' },
                unknown: { text: 'text-muted', icon: 'fas fa-question-circle' }
            }[rvm.calculated_status] || { text: 'text-muted', icon: 'fas fa-question-circle' };
            
            col.innerHTML = `
                <div class="card rvm-card ${rvm.calculated_status} border-0 shadow-sm h-100" data-rvm-id="${rvm.id}">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-dot ${rvm.calculated_status} me-3"></div>
                                <div>
                                    <h6 class="card-title mb-1 fw-bold">${rvm.name}</h6>
                                    <small class="text-muted">${rvm.location}</small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-text-secondary btn-icon rounded-pill" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="openRemoteAccess(${rvm.id}, '${rvm.name}')"><i class="fas fa-desktop me-2"></i>Remote Access</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="openStatusModal(${rvm.id}, '${rvm.name}')"><i class="fas fa-edit me-2"></i>Update Status</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-capitalize ${statusInfo.text}"><i class="${statusInfo.icon} me-1"></i>${rvm.calculated_status}</span>
                                <span class="fw-bold">${rvm.capacity}% Full</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar ${rvm.capacity > 80 ? 'bg-danger' : rvm.capacity > 60 ? 'bg-warning' : 'bg-success'}" style="width: ${rvm.capacity}%"></div>
                            </div>
                            <small class="text-muted d-block mt-2"><i class="fas fa-clock me-1"></i>${rvm.last_seen}</small>
                        </div>
                    </div>
                </div>`;
            return col;
        }

        // --- Pagination Functions ---
        
        function updatePaginationInfo(start, end, total) {
            const infoElement = document.getElementById('pagination-info');
            if (infoElement) {
                infoElement.textContent = `Showing ${start}-${end} of ${total} RVMs`;
            }
        }
        
        function updatePaginationControls(currentPage, totalPages) {
            // Update page buttons
            for (let i = 1; i <= totalPages; i++) {
                const pageElement = document.getElementById(`page-${i}`);
                if (pageElement) {
                    if (i === currentPage) {
                        pageElement.classList.add('active');
                    } else {
                        pageElement.classList.remove('active');
                    }
                }
            }
            
            // Update prev/next buttons
            const prevElement = document.getElementById('prev-page');
            const nextElement = document.getElementById('next-page');
            
            if (prevElement) {
                if (currentPage === 1) {
                    prevElement.classList.add('disabled');
                } else {
                    prevElement.classList.remove('disabled');
                }
            }
            
            if (nextElement) {
                if (currentPage === totalPages) {
                    nextElement.classList.add('disabled');
                } else {
                    nextElement.classList.remove('disabled');
                }
            }
        }
        
        function goToPage(page) {
            if (page < 1) return;
            
            const totalItems = monitoringData?.rvms?.length || 0;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            
            if (page > totalPages) return;
            
            currentPage = page;
            
            // Re-render cards with new page
            if (monitoringData?.rvms) {
                updateRvmCards(monitoringData.rvms);
            }
        }
        
        function changePage(direction) {
            const totalItems = monitoringData?.rvms?.length || 0;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const newPage = currentPage + direction;
            
            if (newPage >= 1 && newPage <= totalPages) {
                goToPage(newPage);
            }
        }

        // --- Charting ---

        function initializeStatusChart() {
            console.log('Initializing status chart...');
            
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded!');
                return;
            }
            
            const canvas = document.getElementById('statusChart');
            if (!canvas) {
                console.error('Status chart canvas not found!');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Could not get 2D context for chart!');
                return;
            }
            
            console.log('Chart canvas found, creating chart...');
            
            try {
                statusChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Inactive', 'Maintenance', 'Full/Error'],
                        datasets: [{
                            data: [0, 0, 0, 0],
                            backgroundColor: ['#28a745', '#6c757d', '#ffc107', '#dc3545'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { 
                            legend: { 
                                position: 'bottom', 
                                labels: { padding: 20 } 
                            } 
                        }
                    }
                });
                
                console.log('Status chart initialized successfully:', statusChart);
            } catch (error) {
                console.error('Error initializing status chart:', error);
            }
        }

        function updateStatusChart() {
            if (!statusChart || !monitoringData) return;
            
            console.log('Updating status chart with data:', monitoringData.rvms);
            
            const counts = monitoringData.rvms.reduce((acc, rvm) => {
                const status = rvm.calculated_status || rvm.status;
                acc[status] = (acc[status] || 0) + 1;
                return acc;
            }, {});
            
            console.log('Status counts:', counts);
            
            statusChart.data.datasets[0].data = [
                counts.active || 0,
                counts.inactive || 0,
                counts.maintenance || 0,
                (counts.full || 0) + (counts.error || 0)
            ];
            
            console.log('Chart data updated:', statusChart.data.datasets[0].data);
            statusChart.update();
        }

        // --- Event Handling & Setup ---

        function setupEventListeners() {
            document.getElementById('refresh-dashboard')?.addEventListener('click', loadMonitoringData);
            document.getElementById('export-data')?.addEventListener('click', exportMonitoringData);
            
            // Remote Access Modal Event Listeners
            const connectBtn = document.getElementById('connect-rvm');
            const accessPinInput = document.getElementById('access-pin');
            
            if (connectBtn) {
                connectBtn.addEventListener('click', handleRemoteConnect);
            }
            
            if (accessPinInput) {
                accessPinInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        handleRemoteConnect();
                    }
                });
            }
            
            // Status Update Modal Event Listeners
            const updateStatusBtn = document.getElementById('update-status');
            if (updateStatusBtn) {
                updateStatusBtn.addEventListener('click', handleStatusUpdate);
            }
            
            // Quick Actions Buttons (moved to navbar dropdown)
            // Event listeners are handled in navbar dropdown
        }

        function openRemoteAccess(rvmId, rvmName) {
            currentRvmId = rvmId;
            document.getElementById('modal-rvm-name').textContent = rvmName;
            new bootstrap.Modal(document.getElementById('remoteAccessModal')).show();
        }

        function openStatusModal(rvmId, rvmName) {
            currentRvmId = rvmId;
            
            console.log('Opening status modal for RVM:', rvmId, rvmName);
            console.log('Monitoring data available:', !!monitoringData);
            console.log('RVMs in monitoring data:', monitoringData?.rvms?.length);
            
            // Get RVM data to show current status
            const rvm = monitoringData?.rvms?.find(r => r.id === rvmId);
            console.log('Found RVM data:', rvm);
            
            if (rvm) {
                document.getElementById('status-modal-rvm-name').textContent = rvmName;
                
                // Use calculated_status instead of status
                const currentStatus = rvm.calculated_status || rvm.status;
                
                // Set current status in dropdown
                const statusSelect = document.getElementById('new-status');
                if (statusSelect) {
                    statusSelect.value = currentStatus;
                }
                
                // Show current status info
                const currentStatusElement = document.getElementById('current-status-display');
                if (currentStatusElement) {
                    currentStatusElement.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="status-dot ${currentStatus} me-2"></div>
                            <span class="text-capitalize fw-medium">${currentStatus}</span>
                        </div>
                    `;
                }
            } else {
                console.error('RVM not found in monitoring data:', rvmId);
                document.getElementById('status-modal-rvm-name').textContent = rvmName;
                
                // Show default status when RVM not found
                const currentStatusElement = document.getElementById('current-status-display');
                if (currentStatusElement) {
                    currentStatusElement.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="status-dot unknown me-2"></div>
                            <span class="text-capitalize fw-medium">Unknown</span>
                        </div>
                    `;
                }
                
                // Set default status in dropdown
                const statusSelect = document.getElementById('new-status');
                if (statusSelect) {
                    statusSelect.value = 'active';
                }
            }
            
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function handleRemoteConnect() {
            const pinInput = document.getElementById('access-pin');
            const connectBtn = document.getElementById('connect-rvm');
            const pin = pinInput.value.trim();
            
            if (!pin) {
                showNotification('Please enter access PIN', 'warning');
                pinInput.focus();
                return;
            }
            
            if (pin.length < 4) {
                showNotification('PIN must be at least 4 characters', 'warning');
                pinInput.focus();
                return;
            }
            
            // Disable button and show loading
            connectBtn.disabled = true;
            connectBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connecting...';
            
            // Simulate connection process
            setTimeout(() => {
                // Save RVM status change to Maintenance when in remote access
                saveRvmStatusChange(currentRvmId, 'maintenance');
                
                // Update current monitoring data
                if (monitoringData && monitoringData.rvms) {
                    const rvmIndex = monitoringData.rvms.findIndex(r => r.id === currentRvmId);
                    if (rvmIndex !== -1) {
                        monitoringData.rvms[rvmIndex].status = 'maintenance';
                        monitoringData.rvms[rvmIndex].calculated_status = 'maintenance';
                        monitoringData.rvms[rvmIndex].last_seen = getCurrentTime();
                    }
                }
                
                // Immediately update the dashboard
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                // Simulate successful connection
                const rvmName = monitoringData?.rvms?.find(r => r.id === currentRvmId)?.name || `RVM-${currentRvmId}`;
                showNotification(`✅ Successfully connected to ${rvmName}. Status changed to Maintenance.`, 'success');
                
                // Additional notification for dashboard update
                setTimeout(() => {
                    showNotification(`🔄 Dashboard updated: ${rvmName} is now in maintenance mode`, 'info');
                }, 1000);
                
                // Close access modal
                const accessModal = bootstrap.Modal.getInstance(document.getElementById('remoteAccessModal'));
                if (accessModal) {
                    accessModal.hide();
                }
                
                // Reset form
                pinInput.value = '';
                connectBtn.disabled = false;
                connectBtn.innerHTML = 'Connect';
                
                // Refresh dashboard to show status change
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                // Open remote control interface
                openRemoteControlInterface(currentRvmId);
                
                console.log(`Remote access connected to RVM-${currentRvmId} with PIN: ${pin}. Status changed to Maintenance.`);
                
            }, 2000);
        }

        function handleStatusUpdate() {
            const statusSelect = document.getElementById('new-status');
            const updateBtn = document.getElementById('update-status');
            const newStatus = statusSelect.value;
            
            if (!newStatus) {
                showNotification('Please select a status', 'warning');
                statusSelect.focus();
                return;
            }
            
            // Get current RVM data
            const rvm = monitoringData?.rvms?.find(r => r.id === currentRvmId);
            const currentStatus = rvm?.status;
            
            // Check if status is the same
            if (currentStatus === newStatus) {
                showNotification('Status is already set to this value', 'info');
                return;
            }
            
            // Confirm critical status changes
            const criticalStatuses = ['error', 'maintenance', 'inactive'];
            if (criticalStatuses.includes(newStatus)) {
                const confirmMessage = `Are you sure you want to set RVM-${currentRvmId} to ${newStatus}? This may affect RVM operations.`;
                if (!confirm(confirmMessage)) {
                    return;
                }
            }
            
            // Disable button and show loading
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            
            // Show updating notification
            showNotification(`Updating RVM-${currentRvmId} status from ${currentStatus} to ${newStatus}...`, 'info');
            
            // Simulate update process
            setTimeout(() => {
                // Save RVM status change
                saveRvmStatusChange(currentRvmId, newStatus);
                
                // Update the RVM data in memory (simulate API response)
                if (monitoringData && monitoringData.rvms) {
                    const rvmIndex = monitoringData.rvms.findIndex(r => r.id === currentRvmId);
                    if (rvmIndex !== -1) {
                        // Update both status and calculated_status
                        monitoringData.rvms[rvmIndex].status = newStatus;
                        monitoringData.rvms[rvmIndex].calculated_status = newStatus;
                        
                        // Update last seen time
                        monitoringData.rvms[rvmIndex].last_seen = getCurrentTime();
                    }
                }
                
                // Immediately update the RVM cards to reflect the change
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                // Show success notification with more details
                const statusMessages = {
                    'active': 'RVM is now operational and ready for use',
                    'inactive': 'RVM has been set to offline mode',
                    'maintenance': 'RVM is now in maintenance mode',
                    'full': 'RVM storage is marked as full',
                    'error': 'RVM status set to error - requires attention',
                    'unknown': 'RVM status set to unknown'
                };
                
                const statusMessage = statusMessages[newStatus] || 'Status updated successfully';
                const rvmName = monitoringData?.rvms?.find(r => r.id === currentRvmId)?.name || `RVM-${currentRvmId}`;
                
                // Show detailed success notification
                showNotification(`✅ ${rvmName} status changed from ${currentStatus} to ${newStatus}. ${statusMessage}`, 'success');
                
                // Additional notification for status change confirmation
                setTimeout(() => {
                    showNotification(`🔄 Dashboard updated: ${rvmName} is now ${newStatus}`, 'info');
                }, 1000);
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Reset form
                statusSelect.value = 'active';
                updateBtn.disabled = false;
                updateBtn.innerHTML = 'Update';
                
                // Refresh dashboard data to show updated status
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                // Log the update
                console.log(`RVM-${currentRvmId} status updated from ${currentStatus} to ${newStatus}`);
                
            }, 2000);
        }

        function openRemoteControlInterface(rvmId) {
            // Get RVM data from monitoring data
            const rvm = monitoringData?.rvms?.find(r => r.id === rvmId);
            if (!rvm) {
                showNotification('RVM data not found', 'error');
                return;
            }
            
            // Update modal with RVM information
            document.getElementById('remote-control-rvm-name').textContent = rvm.name;
            document.getElementById('remote-control-location').textContent = rvm.location;
            
            // Show remote control modal
            const remoteControlModal = new bootstrap.Modal(document.getElementById('remoteControlModal'));
            remoteControlModal.show();
            
            // Start real-time updates
            startRemoteControlUpdates(rvmId);
        }

        function remoteControl(action) {
            const actionMessages = {
                'power_on': 'Powering on RVM...',
                'power_standby': 'Setting RVM to standby mode...',
                'power_off': 'Powering off RVM...',
                'maintenance_mode': 'Setting RVM to maintenance mode...',
                'reset_system': 'Resetting RVM system...',
                'calibrate': 'Calibrating RVM sensors...',
                'export_data': 'Exporting RVM data...',
                'view_logs': 'Opening RVM logs...',
                'generate_report': 'Generating RVM report...',
                'emergency_stop': 'Emergency stop activated!',
                'lock_machine': 'Locking RVM machine...',
                'unlock_machine': 'Unlocking RVM machine...'
            };
            
            const successMessages = {
                'power_on': 'RVM powered on successfully',
                'power_standby': 'RVM set to standby mode',
                'power_off': 'RVM powered off successfully',
                'maintenance_mode': 'RVM set to maintenance mode',
                'reset_system': 'RVM system reset completed',
                'calibrate': 'RVM calibration completed',
                'export_data': 'RVM data exported successfully',
                'view_logs': 'RVM logs opened',
                'generate_report': 'RVM report generated successfully',
                'emergency_stop': 'Emergency stop activated',
                'lock_machine': 'RVM machine locked',
                'unlock_machine': 'RVM machine unlocked'
            };
            
            const message = actionMessages[action] || 'Executing command...';
            const successMessage = successMessages[action] || 'Command executed successfully';
            
            showNotification(message, 'info');
            
            // Simulate command execution
            setTimeout(() => {
                showNotification(successMessage, 'success');
                
                // Update dashboard data if needed
                if (['power_on', 'power_standby', 'power_off', 'maintenance_mode', 'reset_system'].includes(action)) {
                    loadMonitoringData();
                }
                
                console.log(`Remote control action: ${action} executed on RVM-${currentRvmId}`);
            }, 1500);
        }

        function closeRemoteControl() {
            // Simply close the modal without changing RVM status
            // RVM remains in Maintenance status (remote access still active)
            const remoteControlModal = bootstrap.Modal.getInstance(document.getElementById('remoteControlModal'));
            if (remoteControlModal) {
                remoteControlModal.hide();
            }
            
            showNotification('Remote control interface closed. RVM remains in remote access mode.', 'info');
            console.log(`Remote control interface closed for RVM-${currentRvmId}. Status remains Maintenance.`);
        }

        function disconnectRemote() {
            showNotification('Disconnecting from remote control...', 'info');
            
            setTimeout(() => {
                // Save RVM status change back to Active when disconnecting
                saveRvmStatusChange(currentRvmId, 'active');
                
                // Update current monitoring data
                if (monitoringData && monitoringData.rvms) {
                    const rvmIndex = monitoringData.rvms.findIndex(r => r.id === currentRvmId);
                    if (rvmIndex !== -1) {
                        monitoringData.rvms[rvmIndex].status = 'active';
                        monitoringData.rvms[rvmIndex].calculated_status = 'active';
                        monitoringData.rvms[rvmIndex].last_seen = getCurrentTime();
                    }
                }
                
                // Immediately update the dashboard
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                const rvmName = monitoringData?.rvms?.find(r => r.id === currentRvmId)?.name || `RVM-${currentRvmId}`;
                showNotification(`✅ Disconnected from ${rvmName}. Status restored to Active.`, 'success');
                
                // Additional notification for dashboard update
                setTimeout(() => {
                    showNotification(`🔄 Dashboard updated: ${rvmName} is now active`, 'info');
                }, 1000);
                
                // Close remote control modal
                const remoteControlModal = bootstrap.Modal.getInstance(document.getElementById('remoteControlModal'));
                if (remoteControlModal) {
                    remoteControlModal.hide();
                }
                
                // Stop real-time updates
                stopRemoteControlUpdates();
                
                console.log(`Disconnected from RVM-${currentRvmId} remote control. Status restored to Active.`);
            }, 1000);
        }

        function startRemoteControlUpdates(rvmId) {
            // Start real-time updates for remote control interface
            // In real implementation, this would establish WebSocket connection
            console.log(`Started real-time updates for RVM-${rvmId}`);
        }

        function stopRemoteControlUpdates() {
            // Stop real-time updates
            // In real implementation, this would close WebSocket connection
            console.log('Stopped remote control updates');
        }

        function startAutoRefresh() {
            clearInterval(refreshIntervalTimer);
            refreshIntervalTimer = setInterval(loadMonitoringData, config.refreshInterval);
        }

        function stopAutoRefresh() {
            clearInterval(refreshIntervalTimer);
        }
        
        function updateLastUpdated() {
            const lastUpdatedElement = document.getElementById('last-updated');
            if (lastUpdatedElement) {
                lastUpdatedElement.textContent = getCurrentTime();
            }
        }
        
        function getMockData() {
            // Generate mock data with current timezone-aware timestamps
            const now = new Date();
            const baseTime = now.getTime();
            
            // Raw RVM data with capacity and special status
            const rawRvmData = [
                { id: 1, name: 'RVM-001', location: 'Mall Central', capacity: 85, specialStatus: null, lastSeenOffset: 30 },
                { id: 2, name: 'RVM-002', location: 'Shopping Plaza', capacity: 60, specialStatus: 'maintenance', lastSeenOffset: 45 },
                { id: 3, name: 'RVM-003', location: 'City Center', capacity: 30, specialStatus: 'inactive', lastSeenOffset: 120 },
                { id: 4, name: 'RVM-004', location: 'Airport Terminal', capacity: 92, specialStatus: null, lastSeenOffset: 15 },
                { id: 5, name: 'RVM-005', location: 'University Campus', capacity: 100, specialStatus: null, lastSeenOffset: 60 },
                { id: 6, name: 'RVM-006', location: 'Hospital Lobby', capacity: 45, specialStatus: 'error', lastSeenOffset: 180 },
                { id: 7, name: 'RVM-007', location: 'Office Complex', capacity: 78, specialStatus: null, lastSeenOffset: 5 },
                { id: 8, name: 'RVM-008', location: 'Train Station', capacity: 65, specialStatus: null, lastSeenOffset: 20 },
                { id: 9, name: 'RVM-009', location: 'Shopping Mall', capacity: 95, specialStatus: null, lastSeenOffset: 10 },
                { id: 10, name: 'RVM-010', location: 'Bus Station', capacity: 0, specialStatus: null, lastSeenOffset: 2 },
                { id: 11, name: 'RVM-011', location: 'Library', capacity: 15, specialStatus: null, lastSeenOffset: 25 },
                { id: 12, name: 'RVM-012', location: 'Park', capacity: 100, specialStatus: null, lastSeenOffset: 35 }
            ];
            
            // Process RVM data with correct status logic
            const rvms = rawRvmData.map(rvm => ({
                id: rvm.id,
                name: rvm.name,
                location: rvm.location,
                capacity: rvm.capacity,
                status: determineRvmStatus(rvm.capacity, rvm.specialStatus),
                last_seen: formatTime(new Date(baseTime - rvm.lastSeenOffset * 60 * 1000))
            }));
            
            // Calculate statistics based on processed data
            const statistics = {
                total_rvm: rvms.length,
                active_sessions: rvms.filter(r => r.status === 'active').length,
                deposits_today: rvms.reduce((sum, r) => sum + r.capacity, 0),
                total_issues: rvms.filter(r => ['error', 'full'].includes(r.status)).length
            };
            
            return { 
                statistics: statistics,
                rvms: rvms
            };
        }

        // --- Quick Actions Functions ---

        function setAllToMaintenance() {
            const totalRvms = monitoringData?.rvms?.length || 0;
            if (totalRvms === 0) {
                showNotification('No RVMs available to update', 'warning');
                return;
            }
            
            // Confirm bulk operation
            const confirmMessage = `Are you sure you want to set all ${totalRvms} RVMs to maintenance mode? This will affect all RVM operations.`;
            if (!confirm(confirmMessage)) {
                return;
            }
            
            showNotification(`🔄 Setting all ${totalRvms} RVMs to maintenance mode...`, 'info');
            
            // Simulate API call
            setTimeout(() => {
                let updatedCount = 0;
                
                // Update all RVMs in memory
                if (monitoringData && monitoringData.rvms) {
                    monitoringData.rvms.forEach(rvm => {
                        if (rvm.calculated_status !== 'maintenance') {
                            rvm.status = 'maintenance';
                            rvm.calculated_status = 'maintenance';
                            rvm.last_seen = getCurrentTime();
                            saveRvmStatusChange(rvm.id, 'maintenance');
                            updatedCount++;
                        }
                    });
                }
                
                // Immediately update the dashboard
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                // Show detailed success notification
                showNotification(`✅ Bulk update completed: ${updatedCount} RVMs set to maintenance mode`, 'success');
                
                // Additional notification for dashboard update
                setTimeout(() => {
                    showNotification(`🔄 Dashboard updated: All RVMs are now in maintenance mode`, 'info');
                }, 1000);
                
            }, 1500);
        }

        function setAllToActive() {
            const totalRvms = monitoringData?.rvms?.length || 0;
            if (totalRvms === 0) {
                showNotification('No RVMs available to update', 'warning');
                return;
            }
            
            // Confirm bulk operation
            const confirmMessage = `Are you sure you want to activate all ${totalRvms} RVMs? This will restore all RVM operations.`;
            if (!confirm(confirmMessage)) {
                return;
            }
            
            showNotification(`🔄 Activating all ${totalRvms} RVMs...`, 'info');
            
            // Simulate API call
            setTimeout(() => {
                let updatedCount = 0;
                
                // Update all RVMs in memory
                if (monitoringData && monitoringData.rvms) {
                    monitoringData.rvms.forEach(rvm => {
                        if (rvm.calculated_status !== 'active') {
                            rvm.status = 'active';
                            rvm.calculated_status = 'active';
                            rvm.last_seen = getCurrentTime();
                            saveRvmStatusChange(rvm.id, 'active');
                            updatedCount++;
                        }
                    });
                }
                
                // Immediately update the dashboard
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                // Show detailed success notification
                showNotification(`✅ Bulk update completed: ${updatedCount} RVMs activated`, 'success');
                
                // Additional notification for dashboard update
                setTimeout(() => {
                    showNotification(`🔄 Dashboard updated: All RVMs are now active`, 'info');
                }, 1000);
                
            }, 1500);
        }

        function exportMonitoringData() {
            showNotification('Preparing data export...', 'info');
            // Simulate export process
            setTimeout(() => {
                showNotification('Data exported successfully! Download will start shortly.', 'success');
                // In real implementation, this would trigger a file download
            }, 2000);
        }

        // --- Menu Toggle Functions ---

        // function initializeMenuToggle() {
        //     // Initialize menu toggle functionality
        //     const menuToggles = document.querySelectorAll('.menu-toggle');
        //     menuToggles.forEach(toggle => {
        //         toggle.addEventListener('click', function(e) {
        //             e.preventDefault();
        //             const menuItem = this.closest('.menu-item');
        //             const menuSub = menuItem.querySelector('.menu-sub');
                    
        //             if (menuSub) {
        //                 // Toggle active class
        //                 menuItem.classList.toggle('open');
                        
        //                 // Close other open menus
        //                 const otherMenuItems = document.querySelectorAll('.menu-item.open');
        //                 otherMenuItems.forEach(item => {
        //                     if (item !== menuItem) {
        //                         item.classList.remove('open');
        //                     }
        //                 });
        //             }
        //         });
        //     });
        // }

        // --- Dynamic Data Loading Functions ---

        // Load Processing Engines for dropdowns
        async function loadProcessingEngines() {
            try {
                console.log('Loading processing engines from /admin/processing-engines-test...');
                const response = await fetch('/admin/processing-engines-test');
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    const engines = data.data;
                    const cudaEngines = engines.filter(engine => engine.type === 'nvidia_cuda');
                    const jetsonEngines = engines.filter(engine => engine.type === 'jetson_edge');
                    
                    console.log('CUDA engines found:', cudaEngines.length);
                    console.log('Jetson engines found:', jetsonEngines.length);
                    
                    // Update Processing Engine dropdown in Upload & Process modal
                    updateProcessingEngineDropdown(cudaEngines, jetsonEngines);
                    
                    // Update Jetson dropdowns in Live Camera and Upload modals
                    updateJetsonDropdowns(jetsonEngines);
                    
                    // Update Engine Configuration modal with CUDA data
                    updateEngineConfigModal(cudaEngines);
                    
                    console.log('Processing engines loaded successfully:', engines.length);
                } else {
                    console.error('API returned success: false');
                }
            } catch (error) {
                console.error('Error loading processing engines:', error);
            }
        }

        // Update Processing Engine dropdown
        function updateProcessingEngineDropdown(cudaEngines, jetsonEngines) {
            const processingEngineSelect = document.getElementById('processingEngine');
            if (!processingEngineSelect) return;

            // Clear existing options
            processingEngineSelect.innerHTML = '';

            // Add CUDA engines
            cudaEngines.forEach(engine => {
                if (engine.is_active) {
                    const option = document.createElement('option');
                    option.value = `cuda-${engine.id}`;
                    option.textContent = `${engine.name} (${engine.server_address}:${engine.port})`;
                    if (engine.docker_gpu_passthrough) {
                        option.textContent += ' - Docker GPU Passthrough';
                    }
                    processingEngineSelect.appendChild(option);
                }
            });

            // Add Jetson Edge option
            if (jetsonEngines.length > 0) {
                const option = document.createElement('option');
                option.value = 'jetson-edge';
                option.textContent = 'Jetson Edge Computing';
                processingEngineSelect.appendChild(option);
            }
        }

        // Update Jetson dropdowns
        function updateJetsonDropdowns(jetsonEngines) {
            const jetsonSelects = [
                document.getElementById('jetsonSelect'), // Live Camera modal
                document.getElementById('jetsonDevice'),   // Upload & Process modal
                document.getElementById('defaultJetson')   // Engine Configuration modal
            ];

            jetsonSelects.forEach(select => {
                if (!select) return;

                // Clear existing options except first one
                const firstOption = select.querySelector('option[value=""]') || select.querySelector('option');
                select.innerHTML = '';
                
                if (firstOption) {
                    select.appendChild(firstOption);
                }

                // Add Jetson engines
                jetsonEngines.forEach((engine, index) => {
                    const option = document.createElement('option');
                    option.value = `jetson-${engine.id}`;
                    option.textContent = `${engine.name} (${engine.server_address}:${engine.port})`;
                    select.appendChild(option);
                });
            });
        }

        // Update Engine Configuration modal with CUDA data
        function updateEngineConfigModal(cudaEngines) {
            // Update CUDA server dropdown
            const cudaServerSelect = document.getElementById('cudaServerSelect');
            if (cudaServerSelect) {
                cudaServerSelect.innerHTML = '';
                
                cudaEngines.forEach(engine => {
                    const option = document.createElement('option');
                    option.value = engine.id;
                    option.textContent = `${engine.name} (${engine.server_address}:${engine.port})`;
                    if (engine.is_active) {
                        option.textContent += ' - Active';
                    } else {
                        option.textContent += ' - Inactive';
                    }
                    cudaServerSelect.appendChild(option);
                });

                // Add event listener for server selection
                cudaServerSelect.addEventListener('change', function() {
                    const selectedEngine = cudaEngines.find(engine => engine.id == this.value);
                    if (selectedEngine) {
                        document.getElementById('cudaServer').value = `${selectedEngine.server_address}:${selectedEngine.port}`;
                        document.getElementById('gpuMemory').value = selectedEngine.gpu_memory_limit?.replace('GB', '') || '8';
                        document.getElementById('enableDockerGPU').checked = selectedEngine.docker_gpu_passthrough;
                        document.getElementById('cudaModelPath').value = selectedEngine.model_path || '/models/yolo11n.pt';
                    }
                });
            }
        }

        // Load RVM data for Jetson generation
        async function loadRvmData() {
            try {
                const response = await fetch('/admin/rvm/monitoring-data');
                const data = await response.json();
                
                if (data.success) {
                    console.log('RVM data loaded for Jetson generation:', data.data.rvms.length);
                    return data.data.rvms;
                }
            } catch (error) {
                console.error('Error loading RVM data:', error);
            }
            return [];
        }

        // --- Page Lifecycle ---

        window.addEventListener('load', () => {
            // Add small delay to ensure Chart.js is fully loaded
            setTimeout(async () => {
                initializeDashboard();
                // initializeMenuToggle();
                initializeStickyNavbar();
                
                // Load dynamic data for dropdowns
                await loadProcessingEngines();
                await loadRvmData();
            }, 100);
        });
        window.addEventListener('beforeunload', stopAutoRefresh);

        // Sticky Navbar with Scroll Effect
        function initializeStickyNavbar() {
            const navbar = document.getElementById('layout-navbar');
            if (!navbar) return;

            let lastScrollTop = 0;
            
            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Add scrolled class for visual effect
                if (scrollTop > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                lastScrollTop = scrollTop;
            });
        }
    </script>
    
    <!-- Chart.js -->
    <script src="../../assets/vendor/libs/chartjs/chartjs.js"></script>
</head>

<body>
    <!-- Skip Link for Accessibility -->
    <!-- <a href="#main-content" class="skip-link">Skip to main content</a> -->
    
    <!-- Layout wrapper -->
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading Dashboard...</div>
        </div>
    </div>
    
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
        <div class="layout-container">
            <!-- Modern Navbar with Glassmorphism -->
            <nav class="layout-navbar navbar navbar-expand-xl align-items-center modern-navbar" id="layout-navbar">
                <div class="container-xxl">
                    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
                        <a href="{{ url('/admin/rvm-dashboard') }}" class="app-brand-link">
                            <span class="app-brand-logo demo">
                                <span class="text-primary">
                                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor" />
                                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                                    </svg>
                                </span>
                            </span>
                            <span class="app-brand-text demo menu-text fw-bold ms-2">MyRVM Platform</span>
                        </a>
                    </div>

                    <!-- Menu -->
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-auto me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="icon-base ti tabler-menu-2 icon-lg"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item navbar-search-wrapper mb-0">
                                <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
                                    <i class="fas fa-search icon-lg"></i>
                                </a>
                            </div>
                        </div>
                        <!-- /Search -->

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Quick links -->
                            <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="fas fa-bolt icon-lg"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end py-0">
                                    <div class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h5 class="text-body mb-0 me-auto">Quick Actions</h5>
                                        </div>
                                    </div>
                                    <div class="dropdown-shortcuts-list scrollable-container">
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon bg-label-warning rounded-circle mb-2">
                                                    <i class="fas fa-tools fs-4"></i>
                                                </span>
                                                <a href="javascript:void(0);" class="stretched-link" onclick="setAllToMaintenance()">Maintenance Mode</a>
                                                <small class="text-muted mb-0">Set all RVMs to maintenance</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon bg-label-success rounded-circle mb-2">
                                                    <i class="fas fa-power-off fs-4"></i>
                                                </span>
                                                <a href="javascript:void(0);" class="stretched-link" onclick="setAllToActive()">Set All Active</a>
                                                <small class="text-muted mb-0">Activate all RVMs</small>
                                            </div>
                                        </div>
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon bg-label-info rounded-circle mb-2">
                                                    <i class="fas fa-file-export fs-4"></i>
                                                </span>
                                                <a href="javascript:void(0);" class="stretched-link" onclick="exportMonitoringData()">Export Data</a>
                                                <small class="text-muted mb-0">Export monitoring data</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                                    <i class="fas fa-sync-alt fs-4"></i>
                                                </span>
                                                <a href="javascript:void(0);" class="stretched-link" onclick="loadMonitoringData()">Refresh</a>
                                                <small class="text-muted mb-0">Refresh dashboard data</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <!-- Quick links -->

                            <!-- Notification -->
                            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="fas fa-bell icon-lg"></i>
                                    <span class="badge bg-danger rounded-pill badge-notifications">5</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end py-0">
                                    <li class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h5 class="text-body mb-0 me-auto">Notifications</h5>
                                            <a href="javascript:void(0)" class="dropdown-notifications-all text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read">
                                                <i class="fas fa-check-double fs-4"></i>
                                            </a>
                                        </div>
                                    </li>
                                    <li class="dropdown-notifications-list scrollable-container">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <span class="avatar-initial rounded-circle bg-label-danger">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">RVM Error Alert</h6>
                                                        <p class="mb-0">RVM-001 has encountered an error</p>
                                                        <small class="text-muted">5 min ago</small>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <!--/ Notification -->

                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="../../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle">
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="../../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle">
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-medium d-block" id="user-name">Admin User</span>
                                                    <small class="text-muted" id="user-role">Administrator</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);">
                                            <i class="fas fa-user-circle me-2"></i>
                                            <span class="align-middle">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);">
                                            <i class="fas fa-cog me-2"></i>
                                            <span class="align-middle">Settings</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" id="logout-btn">
                                            <i class="fas fa-sign-out-alt me-2"></i>
                                            <span class="align-middle">Log Out</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!--/ User -->
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- / Navbar -->

            <!-- Horizontal Menu -->
            <div class="layout-menu menu-horizontal menu bg-menu-theme">
                <div class="container-xxl">
                    <ul class="menu-inner">
                        <!-- Dashboard -->
                        <li class="menu-item active">
                            <a href="{{ url('/admin/rvm-dashboard') }}" class="menu-link">
                                <i class="menu-icon fas fa-tachometer-alt"></i>
                                <span data-i18n="Dashboard">Dashboard</span>
                            </a>
                        </li>

                        <!-- RVM Management -->
                        <li class="menu-item">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon fas fa-recycle"></i>
                                <span data-i18n="RVM Management">RVM Management</span>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item">
                                    <a href="{{ url('/admin/rvm-management/all') }}" class="menu-link">
                                        <span data-i18n="All RVMs">All RVMs</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="{{ url('/admin/rvm-management/add') }}" class="menu-link">
                                        <span data-i18n="Add New RVM">Add New RVM</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="{{ url('/admin/rvm-management/maintenance') }}" class="menu-link">
                                        <span data-i18n="Maintenance">Maintenance</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Monitoring -->
                        <li class="menu-item">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon fas fa-eye"></i>
                                <span data-i18n="Monitoring">Monitoring</span>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item">
                                    <a href="{{ url('/admin/monitoring/real-time') }}" class="menu-link">
                                        <span data-i18n="Real-time Status">Real-time Status</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="{{ url('/admin/monitoring/analytics') }}" class="menu-link">
                                        <span data-i18n="Analytics">Analytics</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="{{ url('/admin/monitoring/reports') }}" class="menu-link">
                                        <span data-i18n="Reports">Reports</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Playground Computer Vision -->
                        <li class="menu-item">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon fas fa-brain"></i>
                                <span data-i18n="Playground Computer Vision">Playground Computer Vision</span>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item">
                                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                                        <i class="menu-icon fas fa-robot"></i>
                                        <span data-i18n="AI Vision">AI Vision</span>
                                    </a>
                                    <ul class="menu-sub">
                                        <li class="menu-item">
                                            <a href="http://localhost:8001/gemini/dashboard" class="menu-link" target="_blank">
                                                <i class="menu-icon fas fa-eye"></i>
                                                <span data-i18n="MyRVM Vision">MyRVM Vision</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="menu-item">
                                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                                        <i class="menu-icon fas fa-microchip"></i>
                                        <span data-i18n="Edge Vision">Edge Vision</span>
                                    </a>
                                    <ul class="menu-sub">
                                        <li class="menu-item">
                                            <a href="javascript:void(0);" class="menu-link" onclick="openEdgeVisionModal()">
                                                <i class="menu-icon fas fa-video"></i>
                                                <span data-i18n="Live Camera (Jetson)">Live Camera (Jetson)</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="javascript:void(0);" class="menu-link" onclick="openImageUploadModal()">
                                                <i class="menu-icon fas fa-upload"></i>
                                                <span data-i18n="Upload & Process">Upload & Process</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="javascript:void(0);" class="menu-link" onclick="openEngineConfigModal()">
                                                <i class="menu-icon fas fa-cogs"></i>
                                                <span data-i18n="Engine Configuration">Engine Configuration</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <!-- Settings -->
                        <li class="menu-item">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon fas fa-cogs"></i>
                                <span data-i18n="Settings">Settings</span>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item">
                                    <a href="{{ url('/admin/settings/system') }}" class="menu-link">
                                        <span data-i18n="System Settings">System Settings</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="{{ url('/admin/settings/users') }}" class="menu-link">
                                        <span data-i18n="User Management">User Management</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- / Horizontal Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Content wrapper -->
                <div class="content-wrapper modern-content">
                    <!-- Content -->
                    <div id="main-content" class="container-xxl flex-grow-1 container-p-y page-transition">
                        <!-- Modern Page Header -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="page-header modern-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h1 class="page-title fw-bold mb-2">RVM Dashboard</h1>
                                            <p class="page-subtitle text-muted mb-0">Monitor and manage your Reverse Vending Machines</p>
                                            </div>
                                        <div class="page-actions">
                                            <!-- <button class="btn btn-primary btn-modern me-2" id="refresh-dashboard">
                                                <i class="fas fa-sync-alt me-2"></i>Refresh
                                            </button>
                                            <button class="btn btn-outline-primary btn-modern" id="export-data">
                                                <i class="fas fa-file-export me-2"></i>Export
                                            </button> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Charts and Statistics Row -->
                        <div class="row g-6 mb-4">
                            <!-- RVM Status Chart -->
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm animate-slide-in-right" style="animation-delay: 0.5s;">
                                    <div class="card-header border-0 bg-transparent p-4">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-1 fw-bold">RVM Status Distribution</h5>
                                            <p class="card-subtitle text-muted">Real-time status overview</p>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="chart-container">
                                            <canvas id="statusChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics Overview Card -->
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm animate-slide-in-right statistics-overview-card" style="animation-delay: 0.6s;">
                                    <div class="card-header border-0 bg-transparent p-4">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-1 fw-bold">Statistics Overview</h5>
                                            <p class="card-subtitle text-muted">Key performance indicators</p>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row g-4">
                                            <!-- Total RVM -->
                                            <div class="col-6">
                                        <div class="d-flex align-items-center">
                                                    <div class="stat-icon-wrapper me-3">
                                                        <div class="stat-icon bg-gradient-primary">
                                                            <i class="fas fa-recycle text-white"></i>
                                                        </div>
                                            </div>
                                            <div class="card-info">
                                                <h4 class="card-title mb-0 fw-bold" id="total-rvm">-</h4>
                                                <small class="text-muted fw-medium">Total RVM</small>
                                                        <div class="stat-trend mt-1">
                                                            <span class="trend-indicator positive">
                                                                <i class="fas fa-arrow-up me-1"></i>
                                                                <span id="total-rvm-trend">+12%</span>
                                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                            <!-- Active Sessions -->
                                            <div class="col-6">
                                        <div class="d-flex align-items-center">
                                                    <div class="stat-icon-wrapper me-3">
                                                        <div class="stat-icon bg-gradient-success">
                                                            <i class="fas fa-chart-line text-white"></i>
                                                        </div>
                                            </div>
                                            <div class="card-info">
                                                <h4 class="card-title mb-0 fw-bold" id="active-sessions">-</h4>
                                                <small class="text-muted fw-medium">Active Sessions</small>
                                                        <div class="stat-trend mt-1">
                                                            <span class="trend-indicator positive">
                                                                <i class="fas fa-arrow-up me-1"></i>
                                                                <span id="active-sessions-trend">+8%</span>
                                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                            <!-- Deposits Today -->
                                            <div class="col-6">
                                        <div class="d-flex align-items-center">
                                                    <div class="stat-icon-wrapper me-3">
                                                        <div class="stat-icon bg-gradient-info">
                                                            <i class="fas fa-coins text-white"></i>
                                                        </div>
                                            </div>
                                            <div class="card-info">
                                                <h4 class="card-title mb-0 fw-bold" id="deposits-today">-</h4>
                                                <small class="text-muted fw-medium">Deposits Today</small>
                                                        <div class="stat-trend mt-1">
                                                            <span class="trend-indicator positive">
                                                                <i class="fas fa-arrow-up me-1"></i>
                                                                <span id="deposits-today-trend">+15%</span>
                                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                            <!-- Issues -->
                                            <div class="col-6">
                                        <div class="d-flex align-items-center">
                                                    <div class="stat-icon-wrapper me-3">
                                                        <div class="stat-icon bg-gradient-warning">
                                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                                        </div>
                                            </div>
                                            <div class="card-info">
                                                <h4 class="card-title mb-0 fw-bold" id="total-issues">-</h4>
                                                <small class="text-muted fw-medium">Issues</small>
                                                        <div class="stat-trend mt-1">
                                                            <span class="trend-indicator negative">
                                                                <i class="fas fa-arrow-down me-1"></i>
                                                                <span id="total-issues-trend">-5%</span>
                                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- RVM Monitoring Row -->
                        <div class="row g-6 mb-0">
                            <!-- RVM Monitoring -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm animate-scale-in" style="animation-delay: 0.7s;">
                                    <div class="card-header border-0 bg-transparent p-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title mb-1 fw-bold">RVM Monitoring</h5>
                                                <p class="card-subtitle mb-0 text-muted">Real-time status monitoring and remote control</p>
                                            </div>
                                            <button onclick="loadMonitoringData()" class="btn btn-primary btn-lg">
                                                <i class="fas fa-sync-alt me-2"></i>Refresh Data
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div id="rvm-cards-container" class="row g-4">
                                            <!-- RVM cards will be populated here -->
                                        </div>
                                        
                                        <!-- Pagination Controls -->
                                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                            <div class="text-muted">
                                                <span id="pagination-info">Showing 1-12 of 20 RVMs</span>
                                            </div>
                                            <nav aria-label="RVM pagination">
                                                <ul class="pagination pagination-sm mb-0">
                                                    <li class="page-item" id="prev-page">
                                                        <a class="page-link" href="javascript:void(0);" onclick="changePage(-1)">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                    <li class="page-item active" id="page-1">
                                                        <a class="page-link" href="javascript:void(0);" onclick="goToPage(1)">1</a>
                                                    </li>
                                                    <li class="page-item" id="page-2">
                                                        <a class="page-link" href="javascript:void(0);" onclick="goToPage(2)">2</a>
                                                    </li>
                                                    <li class="page-item" id="next-page">
                                                        <a class="page-link" href="javascript:void(0);" onclick="changePage(1)">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-transparent">
                        <div class="container-xxl">
                            <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                                <div class="text-body mb-2 mb-md-0 fw-medium">
                                    © <script>document.write(new Date().getFullYear())</script>, MyRVM Platform
                                </div>
                                <div class="d-none d-lg-inline-block">
                                    <span class="text-muted">Last updated: </span>
                                    <span id="last-updated" class="text-body fw-medium">-</span>
                                </div>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
        <!-- / Layout container -->
    </div>
    <!-- / Layout wrapper -->

    <!-- Remote Access Modal -->
    <div class="modal fade" id="remoteAccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-transparent">
                    <h5 class="modal-title fw-bold">Remote Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <p class="text-muted">Enter access PIN to connect to RVM:</p>
                        <p class="fw-bold text-primary" id="modal-rvm-name">-</p>
                    </div>
                    <div class="mb-3">
                        <label for="access-pin" class="form-label fw-medium">Access PIN</label>
                        <input type="password" id="access-pin" class="form-control" placeholder="Enter PIN">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-transparent">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="connect-rvm" class="btn btn-primary">Connect</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-transparent">
                    <h5 class="modal-title fw-bold">Update RVM Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <p class="text-muted">Update status for:</p>
                        <p class="fw-bold text-primary" id="status-modal-rvm-name">-</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-medium">Current Status</label>
                        <div id="current-status-display">
                            <div class="d-flex align-items-center">
                                <div class="status-dot unknown me-2"></div>
                                <span class="text-muted">Loading...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new-status" class="form-label fw-medium">New Status</label>
                        <select id="new-status" class="form-select">
                            <option value="active">🟢 Active - RVM is operational and ready</option>
                            <option value="inactive">⚫ Inactive - RVM is offline or disabled</option>
                            <option value="maintenance">🟡 Maintenance - RVM is under maintenance</option>
                            <option value="full">🔴 Full - RVM storage is full</option>
                            <option value="error">❌ Error - RVM has encountered an error</option>
                            <option value="unknown">❓ Unknown - Status cannot be determined</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Changing the status will immediately update the RVM's operational state. 
                        Some status changes may require physical intervention.
                    </div>
                </div>
                <div class="modal-footer border-0 bg-transparent">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="update-status" class="btn btn-primary">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Remote Control Modal -->
    <div class="modal fade" id="remoteControlModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-transparent">
                            <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="status-dot active"></div>
                        </div>
                                <div>
                            <h5 class="modal-title fw-bold mb-0" id="remote-control-rvm-name">RVM-001</h5>
                            <small class="text-muted">Remote Control Interface</small>
                                </div>
                            </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- RVM Status & Info -->
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-transparent border-0">
                                    <h6 class="card-title mb-0 fw-bold">RVM Information</h6>
                        </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Current Status</label>
                                        <div class="d-flex align-items-center">
                                            <div class="status-dot active me-2"></div>
                                            <span class="text-success fw-medium">Active</span>
                                    </div>
                                </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Capacity</label>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: 85%"></div>
                            </div>
                                        <small class="text-muted">85% Full</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Last Activity</label>
                                        <p class="mb-0 text-muted">10:30 AM</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Location</label>
                                        <p class="mb-0 text-muted" id="remote-control-location">Mall Central</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Remote Controls -->
                        <div class="col-md-8">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-transparent border-0">
                                    <h6 class="card-title mb-0 fw-bold">Remote Controls</h6>
                            </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <!-- Power Controls -->
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">Power Management</h6>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button class="btn btn-success" onclick="remoteControl('power_on')">
                                                    <i class="fas fa-power-off me-2"></i>Power On
                                                </button>
                                                <button class="btn btn-warning" onclick="remoteControl('power_standby')">
                                                    <i class="fas fa-pause me-2"></i>Standby
                                                </button>
                                                <button class="btn btn-danger" onclick="remoteControl('power_off')">
                                                    <i class="fas fa-power-off me-2"></i>Power Off
                                                </button>
                        </div>
                    </div>

                                        <!-- Maintenance Controls -->
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">Maintenance</h6>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button class="btn btn-info" onclick="remoteControl('maintenance_mode')">
                                                    <i class="fas fa-tools me-2"></i>Maintenance Mode
                                                </button>
                                                <button class="btn btn-secondary" onclick="remoteControl('reset_system')">
                                                    <i class="fas fa-redo me-2"></i>Reset System
                                                </button>
                                                <button class="btn btn-warning" onclick="remoteControl('calibrate')">
                                                    <i class="fas fa-cog me-2"></i>Calibrate
                                                </button>
                </div>
                                        </div>

                                        <!-- Data & Reports -->
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">Data & Reports</h6>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button class="btn btn-primary" onclick="remoteControl('export_data')">
                                                    <i class="fas fa-download me-2"></i>Export Data
                                                </button>
                                                <button class="btn btn-info" onclick="remoteControl('view_logs')">
                                                    <i class="fas fa-file-alt me-2"></i>View Logs
                                                </button>
                                                <button class="btn btn-success" onclick="remoteControl('generate_report')">
                                                    <i class="fas fa-chart-bar me-2"></i>Generate Report
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Emergency Controls -->
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3 text-danger">Emergency Controls</h6>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button class="btn btn-danger" onclick="remoteControl('emergency_stop')">
                                                    <i class="fas fa-stop me-2"></i>Emergency Stop
                                                </button>
                                                <button class="btn btn-warning" onclick="remoteControl('lock_machine')">
                                                    <i class="fas fa-lock me-2"></i>Lock Machine
                                                </button>
                                                <button class="btn btn-info" onclick="remoteControl('unlock_machine')">
                                                    <i class="fas fa-unlock me-2"></i>Unlock Machine
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Status -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-0">
                                    <h6 class="card-title mb-0 fw-bold">Real-time Status</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <div class="fs-4 text-success mb-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <small class="text-muted">System Status</small>
                                                <div class="fw-bold">Online</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <div class="fs-4 text-info mb-1">
                                                    <i class="fas fa-thermometer-half"></i>
                                                </div>
                                                <small class="text-muted">Temperature</small>
                                                <div class="fw-bold">24°C</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <div class="fs-4 text-warning mb-1">
                                                    <i class="fas fa-battery-three-quarters"></i>
                                                </div>
                                                <small class="text-muted">Power Level</small>
                                                <div class="fw-bold">87%</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <div class="fs-4 text-primary mb-1">
                                                    <i class="fas fa-wifi"></i>
                                                </div>
                                                <small class="text-muted">Connection</small>
                                                <div class="fw-bold">Strong</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-transparent">
                    <button type="button" class="btn btn-secondary" onclick="closeRemoteControl()">
                        <i class="fas fa-times me-2"></i>Close Remote Control
                    </button>
                    <button type="button" class="btn btn-danger" onclick="disconnectRemote()">
                        <i class="fas fa-sign-out-alt me-2"></i>Disconnect
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../assets/vendor/libs/hammer/hammer.js"></script>
    <script src="../../assets/vendor/libs/i18n/i18n.js"></script>
    <script src="../../assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="../../assets/vendor/js/menu.js"></script>

    <!-- Vendors JS -->
    <script src="../../assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="../../assets/vendor/libs/swiper/swiper.js"></script>

    <!-- Main JS -->
    <script src="../../assets/js/main.js"></script>

    <!-- Page JS (Skrip dashboard bawaan dari template telah dihapus karena kita menggunakan logika kustom) -->
    <!-- <script src="../../assets/js/dashboards-analytics.js"></script> -->

    <!-- Edge Vision Modals -->
    <!-- Live Camera Modal -->
    <div class="modal fade" id="edgeVisionModal" tabindex="-1" aria-labelledby="edgeVisionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edgeVisionModalLabel">
                        <i class="fas fa-video me-2"></i>Live Camera - Jetson Orin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Live Camera Feed</h6>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-success" id="startCamera">
                                            <i class="fas fa-play"></i> Start
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" id="stopCamera">
                                            <i class="fas fa-stop"></i> Stop
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="camera-container" style="position: relative; background: #000; border-radius: 8px; overflow: hidden;">
                                        <video id="liveCamera" width="100%" height="400" style="display: none;">
                                            Your browser does not support the video tag.
                                        </video>
                                        <div id="cameraPlaceholder" class="d-flex align-items-center justify-content-center" style="height: 400px; color: #666;">
                                            <div class="text-center">
                                                <i class="fas fa-video fa-3x mb-3"></i>
                                                <p>Camera feed will appear here</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Jetson Selection</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Select Jetson Device</label>
                                        <select class="form-select" id="jetsonSelect">
                                            <option value="">Choose Jetson...</option>
                                            <option value="jetson-1">Jetson Orin 1 (192.168.1.100)</option>
                                            <option value="jetson-2">Jetson Orin 2 (192.168.1.101)</option>
                                            <option value="jetson-3">Jetson Orin 3 (192.168.1.102)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Processing Mode</label>
                                        <select class="form-select" id="processingMode">
                                            <option value="yolo">YOLO11 Only</option>
                                            <option value="sam2">SAM2 Only</option>
                                            <option value="both">YOLO11 + SAM2</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="enableRecording">
                                            <label class="form-check-label" for="enableRecording">
                                                Enable Recording
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Detection Results</h6>
                                </div>
                                <div class="card-body">
                                    <div id="detectionResults">
                                        <p class="text-muted text-center">No detections yet</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveResults">
                        <i class="fas fa-save"></i> Save Results
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Upload Modal -->
    <div class="modal fade" id="imageUploadModal" tabindex="-1" aria-labelledby="imageUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageUploadModalLabel">
                        <i class="fas fa-upload me-2"></i>Upload & Process Image
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Upload Image</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="imageUpload" class="form-label">Select Image</label>
                                        <input type="file" class="form-control" id="imageUpload" accept="image/*">
                                    </div>
                                    <div class="image-preview" id="imagePreview" style="display: none;">
                                        <img id="previewImg" class="img-fluid rounded" style="max-height: 300px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Processing Options</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Processing Engine</label>
                                        <select class="form-select" id="processingEngine">
                                            <option value="cuda-vm102">NVIDIA CUDA VM102 (Docker GPU Passthrough)</option>
                                            <option value="jetson-edge">Jetson Edge Computing</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">AI Models</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="enableYOLO" checked>
                                            <label class="form-check-label" for="enableYOLO">
                                                YOLO11 Object Detection
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="enableSAM2" checked>
                                            <label class="form-check-label" for="enableSAM2">
                                                SAM2 Segmentation
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3" id="jetsonConfig" style="display: none;">
                                        <label class="form-label">Jetson Device</label>
                                        <select class="form-select" id="jetsonDevice">
                                            <option value="jetson-1">Jetson Orin 1</option>
                                            <option value="jetson-2">Jetson Orin 2</option>
                                            <option value="jetson-3">Jetson Orin 3</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="processImage">
                        <i class="fas fa-cogs"></i> Process Image
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Engine Configuration Modal -->
    <div class="modal fade" id="engineConfigModal" tabindex="-1" aria-labelledby="engineConfigModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="engineConfigModalLabel">
                        <i class="fas fa-cogs me-2"></i>Engine Configuration
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">NVIDIA CUDA VM102</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">CUDA Server</label>
                                        <select class="form-select" id="cudaServerSelect">
                                            <option value="">Choose CUDA Server...</option>
                                            <!-- Options will be populated dynamically -->
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Server Address</label>
                                        <input type="text" class="form-control" id="cudaServer" value="192.168.1.50:8000" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">GPU Memory Limit</label>
                                        <select class="form-select" id="gpuMemory">
                                            <option value="4">4GB</option>
                                            <option value="8" selected>8GB</option>
                                            <option value="16">16GB</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="enableDockerGPU" checked>
                                            <label class="form-check-label" for="enableDockerGPU">
                                                Docker GPU Passthrough
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Model Path</label>
                                        <input type="text" class="form-control" id="cudaModelPath" value="/models/yolo11n.pt">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Jetson Edge Computing</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Default Jetson</label>
                                        <select class="form-select" id="defaultJetson">
                                            <option value="">Choose Jetson...</option>
                                            <!-- Options will be populated dynamically -->
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Processing Timeout (seconds)</label>
                                        <input type="number" class="form-control" id="processingTimeout" value="30">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="enableAutoFailover">
                                            <label class="form-check-label" for="enableAutoFailover">
                                                Auto Failover
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Model Storage Path</label>
                                        <input type="text" class="form-control" id="jetsonModelPath" value="/home/jetson/models/">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="saveEngineConfig">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edge Vision JavaScript -->
    <script>
        // Menu Toggle Functions
        function initMenuToggle() {
            const menuToggles = document.querySelectorAll('.layout-menu .menu-toggle');

            menuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Mencegah event "bubble up" ke menu parent

                    const currentMenuItem = this.closest('.menu-item');
                    const currentSubMenu = currentMenuItem.querySelector(':scope > .menu-sub');

                    if (!currentSubMenu) return;

                    // Cek apakah menu yang diklik sudah terbuka
                    const isAlreadyOpen = currentMenuItem.classList.contains('open');

                    // Tutup semua menu yang terbuka di level yang sama
                    const parentMenu = currentMenuItem.parentElement;
                    const siblingMenuItems = parentMenu.querySelectorAll(':scope > .menu-item');
                    
                    siblingMenuItems.forEach(sibling => {
                        if (sibling.classList.contains('open')) {
                            sibling.classList.remove('open');
                        }
                    });

                    // Jika menu yang diklik tadi belum terbuka, buka sekarang
                    if (!isAlreadyOpen) {
                        currentMenuItem.classList.add('open');
                    }
                });
            });

            // Tambahkan event listener untuk menutup semua menu jika klik di luar area menu
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.layout-menu')) {
                    document.querySelectorAll('.layout-menu .menu-item.open').forEach(openItem => {
                        openItem.classList.remove('open');
                    });
                }
            });
        }

        // Edge Vision Functions
        function openEdgeVisionModal() {
            const modal = new bootstrap.Modal(document.getElementById('edgeVisionModal'));
            modal.show();
        }

        function openImageUploadModal() {
            const modal = new bootstrap.Modal(document.getElementById('imageUploadModal'));
            modal.show();
        }

        function openEngineConfigModal() {
            const modal = new bootstrap.Modal(document.getElementById('engineConfigModal'));
            modal.show();
        }

        // Live Camera Functions
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize menu toggle functionality
            initMenuToggle();
            
            const startCameraBtn = document.getElementById('startCamera');
            const stopCameraBtn = document.getElementById('stopCamera');
            const liveCamera = document.getElementById('liveCamera');
            const cameraPlaceholder = document.getElementById('cameraPlaceholder');
            const jetsonSelect = document.getElementById('jetsonSelect');

            startCameraBtn.addEventListener('click', function() {
                const selectedJetson = jetsonSelect.value;
                if (!selectedJetson) {
                    alert('Please select a Jetson device first');
                    return;
                }

                // Simulate camera start
                cameraPlaceholder.style.display = 'none';
                liveCamera.style.display = 'block';
                liveCamera.src = `http://${selectedJetson.split('-')[1] === '1' ? '192.168.1.100' : selectedJetson.split('-')[1] === '2' ? '192.168.1.101' : '192.168.1.102'}:8080/video_feed`;
                
                startCameraBtn.disabled = true;
                stopCameraBtn.disabled = false;
                
                // Simulate detection results
                simulateDetectionResults();
            });

            stopCameraBtn.addEventListener('click', function() {
                liveCamera.style.display = 'none';
                cameraPlaceholder.style.display = 'flex';
                liveCamera.src = '';
                
                startCameraBtn.disabled = false;
                stopCameraBtn.disabled = true;
            });

            // Image Upload Functions
            const imageUpload = document.getElementById('imageUpload');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            const processingEngine = document.getElementById('processingEngine');
            const jetsonConfig = document.getElementById('jetsonConfig');

            imageUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            processingEngine.addEventListener('change', function() {
                if (this.value === 'jetson-edge') {
                    jetsonConfig.style.display = 'block';
                } else {
                    jetsonConfig.style.display = 'none';
                }
            });

            document.getElementById('processImage').addEventListener('click', function() {
                const file = imageUpload.files[0];
                if (!file) {
                    alert('Please select an image first');
                    return;
                }

                const engine = processingEngine.value;
                const enableYOLO = document.getElementById('enableYOLO').checked;
                const enableSAM2 = document.getElementById('enableSAM2').checked;

                if (!enableYOLO && !enableSAM2) {
                    alert('Please select at least one AI model');
                    return;
                }

                // Simulate processing
                processImageWithAI(file, engine, enableYOLO, enableSAM2);
            });

            // Engine Configuration Functions
            document.getElementById('saveEngineConfig').addEventListener('click', function() {
                const cudaConfig = {
                    server: document.getElementById('cudaServer').value,
                    gpuMemory: document.getElementById('gpuMemory').value,
                    dockerGPU: document.getElementById('enableDockerGPU').checked,
                    modelPath: document.getElementById('cudaModelPath').value
                };

                const jetsonConfig = {
                    defaultJetson: document.getElementById('defaultJetson').value,
                    timeout: document.getElementById('processingTimeout').value,
                    autoFailover: document.getElementById('enableAutoFailover').checked,
                    modelPath: document.getElementById('jetsonModelPath').value
                };

                // Save configuration (simulate)
                localStorage.setItem('cudaConfig', JSON.stringify(cudaConfig));
                localStorage.setItem('jetsonConfig', JSON.stringify(jetsonConfig));

                alert('Configuration saved successfully!');
                bootstrap.Modal.getInstance(document.getElementById('engineConfigModal')).hide();
            });
        });

        function simulateDetectionResults() {
            const resultsContainer = document.getElementById('detectionResults');
            const results = [
                { class: 'bottle', confidence: 0.95, bbox: [100, 150, 200, 300] },
                { class: 'can', confidence: 0.87, bbox: [300, 200, 150, 250] },
                { class: 'paper', confidence: 0.78, bbox: [500, 100, 180, 200] }
            ];

            let html = '<div class="detection-list">';
            results.forEach((result, index) => {
                html += `
                    <div class="detection-item mb-2 p-2 border rounded">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">${result.class}</span>
                            <span class="badge bg-success">${(result.confidence * 100).toFixed(1)}%</span>
                        </div>
                        <small class="text-muted">BBox: [${result.bbox.join(', ')}]</small>
                    </div>
                `;
            });
            html += '</div>';

            resultsContainer.innerHTML = html;
        }

        function processImageWithAI(file, engine, enableYOLO, enableSAM2) {
            // Simulate processing
            const btn = document.getElementById('processImage');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                // Show results
                alert(`Image processed successfully using ${engine}!\nYOLO11: ${enableYOLO ? 'Enabled' : 'Disabled'}\nSAM2: ${enableSAM2 ? 'Enabled' : 'Disabled'}`);
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('imageUploadModal')).hide();
            }, 3000);
        }
    </script>

</body>
</html>
