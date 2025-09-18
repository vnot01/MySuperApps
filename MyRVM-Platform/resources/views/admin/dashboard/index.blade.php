@extends('components.admin-layout')

@section('title', 'RVM Dashboard - MyRVM Platform')
@section('description', 'RVM Monitoring Dashboard')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
    </li>
@endsection

@section('content')
    <!-- Modern Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header modern-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title fw-bold mb-2">RVM Dashboard 321</h1>
                        <p class="page-subtitle text-muted mb-0">Monitor and manage your Reverse Vending Machines </br>
                        dashboard/index.blade.php
                        </p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-outline-primary btn-modern" id="refresh-dashboard">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                        <button class="btn btn-primary btn-modern" id="export-data">
                            <i class="fas fa-download me-2"></i>Export Data
                        </button>
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
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-6">
                            <div class="d-flex align-items-center p-4">
                                <div class="stat-icon-wrapper me-3">
                                    <div class="stat-icon" style="background: var(--primary-gradient); color: white;">
                                        <i class="fas fa-recycle"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="card-title mb-1" id="total-rvm">0</h3>
                                    <p class="card-subtitle text-muted mb-0">Total RVMs</p>
                                    <div class="stat-trend">
                                        <span class="trend-indicator {{ $trends['total_rvm_trend']['direction'] == 'up' ? 'positive' : ($trends['total_rvm_trend']['direction'] == 'down' ? 'negative' : 'neutral') }}">
                                            @if($trends['total_rvm_trend']['percentage'] == 'N/A')
                                                <i class="fas fa-minus me-1"></i>N/A
                                            @else
                                                <i class="fas fa-arrow-{{ $trends['total_rvm_trend']['direction'] == 'up' ? 'up' : ($trends['total_rvm_trend']['direction'] == 'down' ? 'down' : 'right') }} me-1"></i>{{ $trends['total_rvm_trend']['percentage'] }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-4">
                                <div class="stat-icon-wrapper me-3">
                                    <div class="stat-icon" style="background: var(--success-gradient); color: white;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="card-title mb-1" id="active-sessions">0</h3>
                                    <p class="card-subtitle text-muted mb-0">Active Sessions</p>
                                    <div class="stat-trend">
                                        <span class="trend-indicator {{ $trends['active_sessions_trend']['direction'] == 'up' ? 'positive' : ($trends['active_sessions_trend']['direction'] == 'down' ? 'negative' : 'neutral') }}">
                                            @if($trends['active_sessions_trend']['percentage'] == 'N/A')
                                                <i class="fas fa-minus me-1"></i>N/A
                                            @else
                                                <i class="fas fa-arrow-{{ $trends['active_sessions_trend']['direction'] == 'up' ? 'up' : ($trends['active_sessions_trend']['direction'] == 'down' ? 'down' : 'right') }} me-1"></i>{{ $trends['active_sessions_trend']['percentage'] }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-4">
                                <div class="stat-icon-wrapper me-3">
                                    <div class="stat-icon" style="background: var(--info-gradient); color: white;">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="card-title mb-1" id="deposits-today">0</h3>
                                    <p class="card-subtitle text-muted mb-0">Deposits Today</p>
                                    <div class="stat-trend">
                                        <span class="trend-indicator {{ $trends['deposits_today_trend']['direction'] == 'up' ? 'positive' : ($trends['deposits_today_trend']['direction'] == 'down' ? 'negative' : 'neutral') }}">
                                            @if($trends['deposits_today_trend']['percentage'] == 'N/A')
                                                <i class="fas fa-minus me-1"></i>N/A
                                            @else
                                                <i class="fas fa-arrow-{{ $trends['deposits_today_trend']['direction'] == 'up' ? 'up' : ($trends['deposits_today_trend']['direction'] == 'down' ? 'down' : 'right') }} me-1"></i>{{ $trends['deposits_today_trend']['percentage'] }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-4">
                                <div class="stat-icon-wrapper me-3">
                                    <div class="stat-icon" style="background: var(--warning-gradient); color: white;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="card-title mb-1" id="total-issues">0</h3>
                                    <p class="card-subtitle text-muted mb-0">Total Issues</p>
                                    <div class="stat-trend">
                                        <span class="trend-indicator {{ $trends['total_issues_trend']['direction'] == 'up' ? 'positive' : ($trends['total_issues_trend']['direction'] == 'down' ? 'negative' : 'neutral') }}">
                                            @if($trends['total_issues_trend']['percentage'] == 'N/A')
                                                <i class="fas fa-minus me-1"></i>N/A
                                            @else
                                                <i class="fas fa-arrow-{{ $trends['total_issues_trend']['direction'] == 'up' ? 'up' : ($trends['total_issues_trend']['direction'] == 'down' ? 'down' : 'right') }} me-1"></i>{{ $trends['total_issues_trend']['percentage'] }}
                                            @endif
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
        <div class="col-12">
            <div class="card border-0 shadow-sm animate-scale-in" style="animation-delay: 0.7s;">
                <div class="card-header border-0 bg-transparent p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold">RVM Monitoring</h5>
                            <p class="card-subtitle text-muted mb-0">Real-time status of all Reverse Vending Machines</p>
                            @if(isset($timezoneData) && $timezoneData['statistics']['total_devices'] > 0)
                                <div class="mt-2">
                                    <span class="badge bg-info">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $timezoneData['statistics']['active_devices'] }} devices synced
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex align-items-center">
                            <small class="text-muted me-3">Last updated: <span id="last-updated">--:--:--</span></small>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="goToPage(1)">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="changePage(-1)" id="prev-page">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="changePage(1)" id="next-page">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row" id="rvm-cards-container">
                        <!-- RVM cards will be loaded dynamically -->
                    </div>

                    @if(isset($timezoneData) && $timezoneData['statistics']['total_devices'] > 0)
                        <!-- Timezone Sync Status -->
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="avatar avatar-sm">
                                            <span class="avatar-initial rounded bg-label-info">
                                                <i class="fas fa-clock"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Timezone Sync Status</h6>
                                        <small class="text-muted">Device timezone synchronization</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-end">
                                    <h6 class="mb-0">{{ $timezoneData['statistics']['active_devices'] }}/{{ $timezoneData['statistics']['total_devices'] }} devices</h6>
                                    <small class="text-info">{{ $timezoneData['statistics']['syncs_today'] }} syncs today</small>
                                </div>
                            </div>
                        </div>

                        @if($timezoneData['recent_syncs']->count() > 0)
                            <div class="mt-3">
                                <small class="text-muted d-block mb-2">Recent timezone syncs:</small>
                                <div class="row">
                                    @foreach($timezoneData['recent_syncs']->take(3) as $sync)
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">{{ $sync->device_id }}</small>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($sync->sync_timestamp)->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="loading-spinner-large"></div>
            <div class="loading-text">Loading Dashboard...</div>
        </div>
    </div>

    <!-- Data for JavaScript -->
    <script>
        window.dashboardData = {
            rvms: @json($rvms ?? []),
            statistics: @json($statistics ?? []),
            timezoneConfig: @json($timezoneConfig ?? []),
            timezoneData: @json($timezoneData ?? [])
        };
        
        window.dashboardConfig = {
            apiBaseUrl: '{{ url('/api/v2') }}',
            csrfToken: '{{ csrf_token() }}',
            timezone: '{{ env("APP_TIMEZONE", "Asia/Jakarta") }}',
            dateFormat: '{{ env("APP_DATE_FORMAT", "Y-m-d") }}',
            timeFormat: '{{ env("APP_TIME_FORMAT", "H:i:s") }}',
            datetimeFormat: '{{ env("APP_DATETIME_FORMAT", "Y-m-d H:i:s") }}',
            displayTimezone: '{{ env("APP_DISPLAY_TIMEZONE", "WIB") }}'
        };
    </script>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/dashboard/dashboard.js') }}"></script>
    <script src="{{ asset('js/admin/dashboard/charts.js') }}"></script>
    <script src="{{ asset('js/admin/dashboard/rvm-cards.js') }}"></script>
    <script src="{{ asset('js/admin/dashboard/api.js') }}"></script>
@endsection
