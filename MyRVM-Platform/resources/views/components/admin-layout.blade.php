<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed" dir="ltr" data-skin="default" data-assets-path="/assets/" data-template="horizontal-menu-template" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>@yield('title', 'MyRVM Platform')</title>
    <meta name="description" content="@yield('description', 'RVM Monitoring Dashboard')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet">

    <!-- External Icon Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/vendor/fonts/iconify-icons.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/libs/node-waves/node-waves.css">
    <link rel="stylesheet" href="/assets/vendor/libs/pickr/pickr-themes.css">
    <link rel="stylesheet" href="/assets/vendor/css/core.css">
    <link rel="stylesheet" href="/assets/css/demo.css">

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="/assets/vendor/libs/apex-charts/apex-charts.css">
    <link rel="stylesheet" href="/assets/vendor/libs/swiper/swiper.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/fonts/flag-icons.css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="/assets/vendor/css/pages/cards-advance.css">
    <link rel="stylesheet" href="/assets/vendor/libs/chartjs/chartjs.css">

    <!-- Layout CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin/layout/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/layout/menu-simple.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/layout/footer.css') }}">

    <!-- Page Specific CSS -->
    @yield('page-css')

    <!-- Helpers -->
    <script src="/assets/vendor/js/helpers.js"></script>
    <script src="/assets/vendor/libs/pickr/pickr.js"></script>
    <script src="/assets/vendor/js/template-customizer.js"></script>
    <script src="/assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <!-- <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu"> -->
    <!-- <div class="layout-wrapper layout-navbar-full layout-without-menu"> -->
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-menu-fixed">
        <div class="layout-container">
            <!-- Navbar -->
            @include('components.navbar')
            
            <!-- Sidebar Menu -->
            @include('components.sidebar')
            
            <!-- Horizontal Menu -->
            @include('components.menu')
            
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Content wrapper -->
                <div class="content-wrapper modern-content">
                    <!-- Content -->
                    <div id="main-content" class="container-xxl flex-grow-1 container-p-y page-transition">
                        <!-- Breadcrumb -->
                        @include('components.breadcrumb')
                        
                        <!-- Page Content -->
                        @yield('content')
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

    <!-- Main JS -->
    <script src="/assets/js/main.js"></script>

    <!-- Chart.js -->
    <script src="/assets/vendor/libs/chartjs/chartjs.js"></script>

    <!-- Layout JS -->
    <script src="{{ asset('js/admin/components/menu-toggle-simple.js') }}"></script>
    <script src="{{ asset('js/admin/components/notifications.js') }}"></script>
    <script src="{{ asset('js/admin/components/loading.js') }}"></script>
    <script src="{{ asset('js/admin/components/form-manager.js') }}"></script>

    <!-- Page Specific JS -->
    @yield('page-js')
</body>
</html>