<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-navbar-fixed layout-menu-horizontal layout-compact" dir="ltr" data-skin="default" data-assets-path="/assets/" data-template="horizontal-menu-template" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin Dashboard</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet" />

    <link rel="stylesheet" href="/assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/pickr/pickr-themes.css" />
    <link rel="stylesheet" href="/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="/assets/css/custom.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/swiper/swiper.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="/assets/vendor/fonts/flag-icons.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="/assets/vendor/css/pages/cards-advance.css" />

    <!-- Helpers -->
    <script src="/assets/vendor/js/helpers.js"></script>
    <script src="/assets/vendor/js/template-customizer.js"></script>
    <script src="/assets/js/config.js"></script>

    <!-- Laravel Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
        <div class="layout-container">
            <!-- Navbar -->
            <nav class="layout-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
                <div class="container-xxl">
                    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
                        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
                            <span class="app-brand-logo demo">
                                <span class="text-primary">
                                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor" />
                                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor" />
                                    </svg>
                                </span>
                            </span>
                            <span class="app-brand-text demo menu-text fw-bold text-heading">{{ config('app.name', 'RVM Platform') }}</span>
                        </a>

                        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                            <i class="icon-base ti tabler-x icon-sm d-flex align-items-center justify-content-center"></i>
                        </a>
                    </div>

                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                            <i class="icon-base ti tabler-menu-2 icon-md"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                            <!-- Search -->
                            <li class="nav-item">
                                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#searchModal">
                                    <i class="ti tabler-search ti-md"></i>
                                </a>
                            </li>

                            <!-- Language -->
                            <li class="nav-item dropdown-language dropdown">
                                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <i class="ti tabler-world ti-md"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="en">
                                            <span class="align-middle">English</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="id">
                                            <span class="align-middle">Indonesia</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- Theme Toggle -->
                            <li class="nav-item">
                                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill" href="javascript:void(0);" data-theme="theme-default">
                                    <i class="ti tabler-sun ti-md"></i>
                                </a>
                            </li>

                            <!-- Shortcuts -->
                            <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown">
                                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <i class="ti tabler-apps ti-md"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0">
                                    <div class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Shortcuts</h6>
                                        </div>
                                    </div>
                                    <div class="dropdown-shortcuts-list scrollable-container">
                                        <div class="row row-bordered overflow-visible g-0">
                    <div class="dropdown-shortcuts-item col">
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ti tabler-brain ti-26px text-heading"></i>
                        </span>
                        <a href="http://localhost:8001/gemini/dashboard" class="stretched-link" target="_blank">AI Vision</a>
                        <small>Gemini Vision Playground</small>
                    </div>
                    <div class="dropdown-shortcuts-item col">
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ti tabler-robot ti-26px text-heading"></i>
                        </span>
                        <a href="/admin/edge-vision-test" class="stretched-link">Edge Vision</a>
                        <small>YOLO11 + SAM2</small>
                    </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Notifications -->
                            <li class="nav-item dropdown-notifications navbar-dropdown dropdown">
                                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <span class="position-relative">
                                        <i class="ti tabler-bell ti-md"></i>
                                        <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-0">
                                    <li class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Notification</h6>
                                            <div class="d-flex align-items-center h6 mb-0">
                                                <span class="badge bg-primary rounded-pill me-2">8</span>
                                                <a href="javascript:void(0)" class="btn btn-text-secondary rounded-pill btn-icon dropdown-notifications-all" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read">
                                                    <i class="ti tabler-mail-opened text-heading"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown-notifications-list scrollable-container">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <img src="/assets/img/avatars/1.png" alt class="rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="small mb-1">Congratulation Lettie 🎉</h6>
                                                        <small class="mb-1 d-block text-body">Won the monthly best seller gold badge</small>
                                                        <small class="text-muted">1h ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)" class="dropdown-notifications-read">
                                                            <span class="badge badge-dot"></span>
                                                        </a>
                                                        <a href="javascript:void(0)" class="dropdown-notifications-archive">
                                                            <span class="ti tabler-x"></span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="border-top">
                                        <div class="d-grid p-4">
                                            <a class="btn btn-primary btn-sm d-flex" href="javascript:void(0);">
                                                <small class="align-middle">View all notifications</small>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </li>

                            <!-- User Profile -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="/assets/img/avatars/1.png" alt class="rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="/assets/img/avatars/1.png" alt class="rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                                    <small class="text-muted">{{ Auth::user()->email }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="ti ti-user me-3 icon-md"></i><span class="align-middle">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="ti ti-settings me-3 icon-md"></i><span class="align-middle">Settings</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="ti ti-credit-card me-3 icon-md"></i><span class="align-middle">Billing</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="ti ti-power me-3 icon-md"></i><span class="align-middle">Log Out</span>
                                            </a>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- / Navbar -->

            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu flex-grow-0">
                <div class="container-xxl d-flex h-100">
                    <ul class="menu-inner">
                        <!-- Dashboards -->
                        <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="javascript:void(0)" class="menu-link menu-toggle">
                                <i class="menu-icon ti tabler-smart-home"></i>
                                <div data-i18n="Dashboards">Dashboards</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <a href="{{ route('admin.dashboard') }}" class="menu-link">
                                        <i class="menu-icon ti tabler-chart-pie-2"></i>
                                        <div data-i18n="Analytics">Analytics</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="menu-link">
                                        <i class="menu-icon ti tabler-vector-bezier-circle"></i>
                                        <div data-i18n="RVM">RVM Management</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="menu-link">
                                        <i class="menu-icon ti tabler-shopping-cart"></i>
                                        <div data-i18n="Economy">Economy</div>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Management -->
                        <li class="menu-item">
                            <a href="javascript:void(0)" class="menu-link menu-toggle">
                                <i class="menu-icon ti tabler-users"></i>
                                <div data-i18n="Management">Management</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item">
                                    <a href="#" class="menu-link">
                                        <i class="menu-icon ti tabler-user"></i>
                                        <div data-i18n="Users">Users</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="menu-link">
                                        <i class="menu-icon ti tabler-building"></i>
                                        <div data-i18n="Tenants">Tenants</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="menu-link">
                                        <i class="menu-icon ti tabler-shield"></i>
                                        <div data-i18n="Roles">Roles & Permissions</div>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- RVM Operations -->
                        <li class="menu-item">
                            <a href="javascript:void(0)" class="menu-link menu-toggle">
                                <i class="menu-icon ti tabler-devices"></i>
                                <div data-i18n="RVM">RVM Operations</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item">
                                    <a href="#" class="menu-link">
                                        <i class="menu-icon ti tabler-list"></i>
                                        <div data-i18n="Machines">Machines</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="menu-link">
                                        <i class="menu-icon ti tabler-activity"></i>
                                        <div data-i18n="Sessions">Sessions</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="menu-link">
                                        <i class="menu-icon ti tabler-coins"></i>
                                        <div data-i18n="Deposits">Deposits</div>
                                    </a>
                                </li>
                            </ul>
                        </li>

            <!-- Computer Vision -->
            <li class="menu-item">
                <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon ti tabler-brain"></i>
                    <div data-i18n="Computer Vision">Computer Vision</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="http://localhost:8001/gemini/dashboard" class="menu-link" target="_blank">
                            <i class="menu-icon ti tabler-eye"></i>
                            <div data-i18n="AI Vision">AI Vision</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="/admin/edge-vision-test" class="menu-link">
                            <i class="menu-icon ti tabler-robot"></i>
                            <div data-i18n="Edge Vision">Edge Vision</div>
                        </a>
                    </li>
                </ul>
            </li>
                    </ul>
                </div>
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Page Heading -->
                        @if (isset($header))
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-6">
                                    <div class="card-body">
                                        {{ $header }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Page Content -->
                        <div id="vue-root">
                            {{ $slot }}
                        </div>
                    </div>
                    <!-- / Content -->
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
        <!-- / Layout container -->
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/assets/vendor/libs/popper/popper.js"></script>
    <script src="/assets/vendor/js/bootstrap.js"></script>
    <script src="/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/assets/vendor/js/menu.js"></script>

    <!-- Vendors JS -->
    <script src="/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="/assets/vendor/libs/swiper/swiper.js"></script>
    <script src="/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

    <!-- Main JS -->
    <script src="/assets/js/main.js"></script>
</body>

</html>