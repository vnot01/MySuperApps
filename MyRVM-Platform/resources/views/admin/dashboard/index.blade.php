@extends('components.admin-layout')

@section('title', 'RVM Dashboard - MyRVM Platform')
@section('description', 'RVM Monitoring Dashboard')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/remote-access.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/remote-gui-client.css') }}">
@endsection

@section('breadcrumb')
    <!-- <li class="breadcrumb-item">
        <a href="{{ url('/admin/dashboard') }}">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
    </li> -->
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-tachometer-alt me-2"></i>RVM Dashboard
    </li>
@endsection

@section('content')
    <!-- Modern Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header modern-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title fw-bold mb-2">
                            <i class="fas fa-recycle me-2"></i>
                            MyRVM Platform
                        </h1>
                        <h2 class="page-subtitle fw-bold mb-2">RVM Dashboard</h2>
                        <p class="page-description text-muted mb-0">Monitor and manage your Reverse Vending Machines</p>
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
                                                N/A
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
                                                N/A
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
                                                N/A
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
                                                N/A
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
                            <div class="mt-2">
                                @if(isset($timezoneData) && $timezoneData['statistics']['total_devices'] > 0)
                                    <span class="badge bg-info">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $timezoneData['statistics']['active_devices'] }} devices synced
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-clock me-1"></i>
                                        Timezone sync ready
                                    </span>
                                @endif
                            </div>
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

                    <!-- Timezone Sync Status -->
                    <!-- <hr class="my-4">
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
                                @if(isset($timezoneData) && $timezoneData['statistics']['total_devices'] > 0)
                                    <h6 class="mb-0">{{ $timezoneData['statistics']['active_devices'] }}/{{ $timezoneData['statistics']['total_devices'] }} devices</h6>
                                    <small class="text-info">{{ $timezoneData['statistics']['syncs_today'] }} syncs today</small>
                                @else
                                    <h6 class="mb-0">0/0 devices</h6>
                                    <small class="text-muted">No syncs yet</small>
                                @endif
                            </div>
                        </div>
                    </div> -->

                    <!-- @if(isset($timezoneData) && $timezoneData['recent_syncs']->count() > 0)
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
                    @else
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">Recent timezone syncs:</small>
                            <div class="row">
                                <div class="col-12">
                                    <small class="text-muted">No recent syncs. Start by sending timezone data from Jetson Orin.</small>
                                </div>
                            </div>
                        </div>
                    @endif -->
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

    <!-- Remote Access Modal - Simplified -->
    <div class="modal fade" id="remoteAccessModal" tabindex="-1" aria-labelledby="remoteAccessModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="remoteAccessModalLabel">
                        <i class="fas fa-wrench me-2"></i>Enter Maintenance Mode
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="fas fa-tools fa-3x text-warning mb-3"></i>
                            <h6>RVM Maintenance Mode</h6>
                            <p class="text-muted">Enter maintenance mode to access advanced monitoring, remote commands, and OTA management features.</p>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> This will change RVM status to "maintenance" and disable normal operations.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="enterMaintenanceModeBtn">
                        <i class="fas fa-wrench me-2"></i>Enter Maintenance Mode
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Remote Access Status Modal -->
    <div class="modal fade" id="remoteAccessStatusModal" tabindex="-1" aria-labelledby="remoteAccessStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="remoteAccessStatusModalLabel">
                        <i class="fas fa-info-circle me-2"></i>Remote Access Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="remoteAccessStatusContent">
                        <!-- Status content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="stopRemoteAccessBtn" style="display: none;">
                        <i class="fas fa-stop"></i> Stop Remote Access
                    </button>
                </div>
            </div>
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
    <script src="{{ asset('js/admin/dashboard/remote-access.js') }}"></script>
    <script src="{{ asset('js/admin/dashboard/remote-gui-client.js') }}"></script>
@endsection
