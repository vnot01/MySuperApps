<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed" dir="ltr" data-skin="default" data-assets-path="../../assets/" data-template="horizontal-menu-template" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>@yield('title', 'MyRVM Platform')</title>
    <meta name="description" content="@yield('description', 'RVM Monitoring Dashboard')">
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
    
    <!-- Base Layout Styles -->
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

        /* Menu Toggle Styles */
        .menu-item .menu-sub {
            display: none;
            position: absolute;
            background: white;
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-top: 5px;
            padding: 8px 0;
            min-width: 220px;
            z-index: 1050;
            list-style: none;
            animation: slideDown 0.2s ease-out;
        }

        .menu-item.open > .menu-sub {
            display: block;
        }

        .menu-item.open > .menu-link {
            color: #667eea !important;
        }

        /* Remove forced menu arrows */
        .layout-menu .menu-link.menu-toggle::after {
            content: none !important;
        }

        .menu-inner .menu-sub .menu-link::before {
            content: none !important;
        }

        /* Menu sub styling */
        .menu-sub .menu-link {
            padding: 10px 20px;
            color: #6c757d;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: block;
            white-space: nowrap;
        }

        .menu-sub .menu-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateX(5px);
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

        /* Force hide backdrop */
        .content-backdrop {
            display: none !important;
        }
        
        /* Ensure content-wrapper is visible */
        .content-wrapper {
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            max-width: 400px;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification-success {
            background: rgba(16, 185, 129, 0.9);
            color: white;
        }

        .notification-error {
            background: rgba(239, 68, 68, 0.9);
            color: white;
        }

        .notification-warning {
            background: rgba(245, 158, 11, 0.9);
            color: white;
        }

        .notification-info {
            background: rgba(59, 130, 246, 0.9);
            color: white;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            margin-top: 1rem;
            color: #667eea;
            font-weight: 600;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* SPA Views */
        .spa-view {
            min-height: 400px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease-out;
        }

        .spa-view.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Breadcrumb Styles */
        .breadcrumb {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .breadcrumb-item a:hover {
            color: #5a67d8;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        /* Page Transitions */
        .page-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Connection Status Indicators */
        .connection-indicator {
            text-align: center;
            padding: 1rem;
        }

        .connection-indicator i {
            transition: all 0.3s ease;
        }

        /* Status Timeline */
        .status-timeline {
            max-height: 300px;
            overflow-y: auto;
        }

        .status-history-item {
            border-left: 3px solid #e9ecef;
            padding-left: 1rem;
            position: relative;
        }

        .status-history-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0.5rem;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #6c757d;
        }

        .status-history-item:first-child::before {
            background: #667eea;
        }

        /* Detection Results */
        .detection-result {
            border-left: 4px solid #28a745;
            transition: all 0.2s ease;
        }

        .detection-result:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Camera Container */
        .camera-container {
            position: relative;
            overflow: hidden;
        }

        .camera-overlay {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }

        .processing-overlay {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
        }

        /* Impact Assessment */
        .impact-metrics {
            font-size: 0.9rem;
        }

        /* SPA Content Container */
        #spa-content-container {
            transition: all 0.3s ease-in-out;
        }

        #spa-content-container.show {
            opacity: 1;
            visibility: visible;
        }

        /* Content Switching */
        .content-switching {
            transition: all 0.3s ease-in-out;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .spa-view {
                padding: 1rem;
            }
            
            .breadcrumb {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
    
    @yield('custom-css')
</head>

<body>
    <!-- Layout wrapper -->
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading...</div>
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
                    <!-- Breadcrumb Navigation -->
                    <div class="container-xxl">
                        <div class="row">
                            <div class="col-12">
                                <div id="breadcrumb-container" class="py-3">
                                    <!-- Breadcrumbs will be dynamically generated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div id="main-content" class="container-xxl flex-grow-1 container-p-y page-transition">
                        @yield('content')
                    </div>
                    
                    <!-- SPA Content Container (hidden by default) -->
                    <div id="spa-content-container" class="container-xxl flex-grow-1 container-p-y page-transition" style="display: none;">
                        <!-- SPA components will be loaded here -->
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

    <!-- Modals Section -->
    @yield('modals')

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

    <!-- Base Layout JavaScript -->
    <script>
        // Base configuration
        const baseConfig = {
            apiBaseUrl: '{{ url('/api/v2') }}',
            csrfToken: '{{ csrf_token() }}',
            timezone: '{{ env("APP_TIMEZONE", "Asia/Jakarta") }}',
            dateFormat: '{{ env("APP_DATE_FORMAT", "Y-m-d") }}',
            timeFormat: '{{ env("APP_TIME_FORMAT", "H:i:s") }}',
            datetimeFormat: '{{ env("APP_DATETIME_FORMAT", "Y-m-d H:i:s") }}',
            displayTimezone: '{{ env("APP_DISPLAY_TIMEZONE", "WIB") }}'
        };

        // Base UI Functions
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

        // Menu Toggle Functions
        function initMenuToggle() {
            const menuToggles = document.querySelectorAll('.layout-menu .menu-toggle');

            menuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const currentMenuItem = this.closest('.menu-item');
                    const currentSubMenu = currentMenuItem.querySelector(':scope > .menu-sub');

                    if (!currentSubMenu) return;

                    const isAlreadyOpen = currentMenuItem.classList.contains('open');

                    const parentMenu = currentMenuItem.parentElement;
                    const siblingMenuItems = parentMenu.querySelectorAll(':scope > .menu-item');
                    
                    siblingMenuItems.forEach(sibling => {
                        if (sibling.classList.contains('open')) {
                            sibling.classList.remove('open');
                        }
                    });

                    if (!isAlreadyOpen) {
                        currentMenuItem.classList.add('open');
                    }
                });
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('.layout-menu')) {
                    document.querySelectorAll('.layout-menu .menu-item.open').forEach(openItem => {
                        openItem.classList.remove('open');
                    });
                }
            });
        }

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

        // Initialize base functionality
        document.addEventListener('DOMContentLoaded', function() {
            initMenuToggle();
            initializeStickyNavbar();
        });
    </script>

    @yield('custom-js')
</body>
</html>