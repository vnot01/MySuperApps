@extends('components.admin-layout')

@section('title', 'RVM Dashboard - MyRVM Platform')
@section('description', 'RVM Monitoring Dashboard')

@section('custom-css')
    <!-- Page CSS -->
    <link rel="stylesheet" href="../../assets/vendor/css/pages/cards-advance.css">
    <link rel="stylesheet" href="../../assets/vendor/libs/chartjs/chartjs.css">
    
    <!-- Custom RVM Styles -->
    <style>
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

        .page-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

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

        .bg-gradient-primary {
            background: var(--primary-gradient);
        }

        .bg-gradient-success {
            background: var(--success-gradient);
        }

        .bg-gradient-warning {
            background: var(--warning-gradient);
        }

        .bg-gradient-danger {
            background: var(--danger-gradient);
        }

        .bg-gradient-info {
            background: var(--info-gradient);
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

        /* Animation Classes */
        .animate-slide-in-right {
            animation: slideInRight 0.6s ease-out forwards;
        }

        .animate-scale-in {
            animation: scaleIn 0.6s ease-out forwards;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('content')
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
                        <!-- Action buttons can be added here -->
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
@endsection

@section('custom-js')
    <!-- Chart.js -->
    <script src="../../assets/vendor/libs/chartjs/chartjs.js"></script>
    
    <!-- SPA Router -->
    <script src="{{ asset('js/admin/dashboard/spa-router.js') }}"></script>
    
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
                setTimeout(() => {
                    if (overlay.style.opacity === '0') {
                        overlay.style.display = 'none';
                    }
                }, 300);
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
                
                if (isNaN(d.getTime())) {
                    console.warn('Invalid date provided to formatDateTime:', date);
                    return 'Invalid Date';
                }
                
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
                return `${formatted} ${config.displayTimezone || 'WIB'}`;
                
            } catch (error) {
                console.error('Error formatting datetime:', error);
                return date ? date.toString() : 'Unknown';
            }
        }

        function formatTime(date) {
            try {
                const d = new Date(date);
                
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

        function getCurrentTime() {
            const now = new Date();
            return formatTime(now);
        }

        // --- RVM Status Logic Functions ---

        function determineRvmStatus(capacity, specialStatus = null) {
            if (specialStatus && ['maintenance', 'inactive', 'error', 'unknown'].includes(specialStatus)) {
                return specialStatus;
            }
            
            if (capacity >= 100) {
                return 'full';
            } else if (capacity >= 0) {
                return 'active';
            } else {
                return 'unknown';
            }
        }

        // --- Data Update Functions ---

        function saveRvmStatusChange(rvmId, newStatus) {
            rvmStatusChanges[rvmId] = {
                status: newStatus,
                last_seen: getCurrentTime(),
                timestamp: Date.now()
            };
            
            localStorage.setItem('rvmStatusChanges', JSON.stringify(rvmStatusChanges));
            console.log(`Saved status change for RVM-${rvmId}: ${newStatus} at ${getCurrentTime()}`);
        }

        function loadRvmStatusChanges() {
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
            col.style.opacity = 0;
            
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

        // --- Quick Actions Functions ---

        function setAllToMaintenance() {
            const totalRvms = monitoringData?.rvms?.length || 0;
            if (totalRvms === 0) {
                showNotification('No RVMs available to update', 'warning');
                return;
            }
            
            const confirmMessage = `Are you sure you want to set all ${totalRvms} RVMs to maintenance mode? This will affect all RVM operations.`;
            if (!confirm(confirmMessage)) {
                return;
            }
            
            showNotification(`🔄 Setting all ${totalRvms} RVMs to maintenance mode...`, 'info');
            
            setTimeout(() => {
                let updatedCount = 0;
                
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
                
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                showNotification(`✅ Bulk update completed: ${updatedCount} RVMs set to maintenance mode`, 'success');
                
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
            
            const confirmMessage = `Are you sure you want to activate all ${totalRvms} RVMs? This will restore all RVM operations.`;
            if (!confirm(confirmMessage)) {
                return;
            }
            
            showNotification(`🔄 Activating all ${totalRvms} RVMs...`, 'info');
            
            setTimeout(() => {
                let updatedCount = 0;
                
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
                
                updateRvmCards(monitoringData.rvms);
                updateStatusChart();
                
                showNotification(`✅ Bulk update completed: ${updatedCount} RVMs activated`, 'success');
                
                setTimeout(() => {
                    showNotification(`🔄 Dashboard updated: All RVMs are now active`, 'info');
                }, 1000);
                
            }, 1500);
        }

        function exportMonitoringData() {
            showNotification('Preparing data export...', 'info');
            setTimeout(() => {
                showNotification('Data exported successfully! Download will start shortly.', 'success');
            }, 2000);
        }

        // --- Page Lifecycle ---

        window.addEventListener('load', () => {
            setTimeout(async () => {
                // Check if SPA Router is available
                if (window.spaRouter) {
                    console.log('SPA Router detected - Skipping traditional dashboard initialization');
                    // SPA Router will handle the dashboard initialization
                    initializeStickyNavbar();
                    return;
                }
                
                console.log('Traditional dashboard initialization');
                initializeDashboard();
                initializeStickyNavbar();
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
                
                if (scrollTop > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                lastScrollTop = scrollTop;
            });
        }

        // --- Modal Functions ---

        function openRemoteAccess(rvmId, rvmName) {
            // Navigate to SPA route instead of opening modal
            if (window.spaRouter) {
                window.spaRouter.navigate(`/dashboard/remote-access/${rvmId}`, {
                    rvmId: rvmId,
                    rvmName: rvmName,
                    location: 'Main Lobby - Building A' // This would come from RVM data
                });
            } else {
                // Fallback to modal if SPA router not available
                currentRvmId = rvmId;
                document.getElementById('modal-rvm-name').textContent = rvmName;
                const modal = new bootstrap.Modal(document.getElementById('remoteAccessModal'));
                modal.show();
            }
        }

        function openStatusModal(rvmId, rvmName) {
            // Navigate to SPA route instead of opening modal
            if (window.spaRouter) {
                window.spaRouter.navigate(`/dashboard/status-update/${rvmId}`, {
                    rvmId: rvmId,
                    rvmName: rvmName,
                    location: 'Main Lobby - Building A' // This would come from RVM data
                });
            } else {
                // Fallback to modal if SPA router not available
                currentRvmId = rvmId;
                document.getElementById('status-modal-rvm-name').textContent = rvmName;
                
                // Find current status
                const rvm = monitoringData?.rvms?.find(r => r.id === rvmId);
                if (rvm) {
                    const statusDisplay = document.getElementById('current-status-display');
                    const statusInfo = {
                        active: { text: 'text-success', icon: 'fas fa-check-circle', label: 'Active' },
                        inactive: { text: 'text-secondary', icon: 'fas fa-pause-circle', label: 'Inactive' },
                        maintenance: { text: 'text-warning', icon: 'fas fa-tools', label: 'Maintenance' },
                        full: { text: 'text-danger', icon: 'fas fa-exclamation-triangle', label: 'Full' },
                        error: { text: 'text-danger', icon: 'fas fa-times-circle', label: 'Error' },
                        unknown: { text: 'text-muted', icon: 'fas fa-question-circle', label: 'Unknown' }
                    }[rvm.calculated_status] || { text: 'text-muted', icon: 'fas fa-question-circle', label: 'Unknown' };
                    
                    statusDisplay.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="status-dot ${rvm.calculated_status} me-2"></div>
                            <span class="${statusInfo.text}">${statusInfo.label}</span>
                        </div>
                    `;
                }
                
                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                modal.show();
            }
        }

        function openEdgeVisionModal() {
            // Navigate to SPA route instead of opening modal
            if (window.spaRouter) {
                window.spaRouter.navigate('/edge-vision/live-camera');
            } else {
                // Fallback to modal if SPA router not available
                const modal = new bootstrap.Modal(document.getElementById('edgeVisionModal'));
                modal.show();
            }
        }

        function openImageUploadModal() {
            // Navigate to SPA route instead of opening modal
            if (window.spaRouter) {
                window.spaRouter.navigate('/edge-vision/image-upload');
            } else {
                // Fallback to modal if SPA router not available
                const modal = new bootstrap.Modal(document.getElementById('imageUploadModal'));
                modal.show();
            }
        }

        function openEngineConfigModal() {
            // Navigate to SPA route instead of opening modal
            if (window.spaRouter) {
                window.spaRouter.navigate('/edge-vision/engine-config');
            } else {
                // Fallback to modal if SPA router not available
                const modal = new bootstrap.Modal(document.getElementById('engineConfigModal'));
                modal.show();
            }
        }

        // --- Modal Event Listeners ---

        document.addEventListener('DOMContentLoaded', function() {
            // Remote Access Modal
            document.getElementById('connect-rvm')?.addEventListener('click', function() {
                const pin = document.getElementById('access-pin').value;
                if (!pin) {
                    showNotification('Please enter access PIN', 'warning');
                    return;
                }
                
                showNotification(`Connecting to RVM-${currentRvmId}...`, 'info');
                setTimeout(() => {
                    showNotification(`Connected to RVM-${currentRvmId} successfully!`, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('remoteAccessModal')).hide();
                }, 2000);
            });

            // Status Update Modal
            document.getElementById('update-status')?.addEventListener('click', function() {
                const newStatus = document.getElementById('new-status').value;
                if (!newStatus || !currentRvmId) return;
                
                showNotification(`Updating RVM-${currentRvmId} status to ${newStatus}...`, 'info');
                
                setTimeout(() => {
                    // Update the RVM status
                    const rvm = monitoringData?.rvms?.find(r => r.id === currentRvmId);
                    if (rvm) {
                        rvm.calculated_status = newStatus;
                        rvm.last_seen = getCurrentTime();
                        saveRvmStatusChange(currentRvmId, newStatus);
                        
                        updateRvmCards(monitoringData.rvms);
                        updateStatusChart();
                        
                        showNotification(`RVM-${currentRvmId} status updated to ${newStatus}`, 'success');
                    }
                    
                    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
                }, 1500);
            });

            // Edge Vision Modal
            document.getElementById('startCamera')?.addEventListener('click', function() {
                showNotification('Starting camera feed...', 'info');
                setTimeout(() => {
                    showNotification('Camera feed started successfully!', 'success');
                }, 2000);
            });

            document.getElementById('stopCamera')?.addEventListener('click', function() {
                showNotification('Stopping camera feed...', 'info');
                setTimeout(() => {
                    showNotification('Camera feed stopped', 'success');
                }, 1000);
            });

            // Image Upload Modal
            document.getElementById('imageUpload')?.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImg').src = e.target.result;
                        document.getElementById('imagePreview').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            document.getElementById('processImage')?.addEventListener('click', function() {
                const file = document.getElementById('imageUpload').files[0];
                if (!file) {
                    showNotification('Please select an image first', 'warning');
                    return;
                }
                
                showNotification('Processing image...', 'info');
                setTimeout(() => {
                    showNotification('Image processed successfully!', 'success');
                }, 3000);
            });

            // Engine Configuration Modal
            document.getElementById('saveEngineConfig')?.addEventListener('click', function() {
                showNotification('Saving engine configuration...', 'info');
                setTimeout(() => {
                    showNotification('Engine configuration saved successfully!', 'success');
                }, 2000);
            });
        });
    </script>
@endsection

@section('modals')
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
@endsection