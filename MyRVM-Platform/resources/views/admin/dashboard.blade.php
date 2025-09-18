<x-admin-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-heading mb-0">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <!-- Statistics Cards -->
    <div class="row g-6 mb-6">
        <!-- Total RVM Machines -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total RVM Machines 1234</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">245</h4>
                                <p class="text-success mb-0">(+12%)</p>
                            </div>
                            <small class="mb-0">Active machines across all locations</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base ti tabler-device-desktop icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Active Sessions</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">1,847</h4>
                                <p class="text-success mb-0">(+8%)</p>
                            </div>
                            <small class="mb-0">Current active user sessions</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base ti tabler-activity icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total Revenue</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">$89,247</h4>
                                <p class="text-success mb-0">(+15%)</p>
                            </div>
                            <small class="mb-0">This month's earnings</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="icon-base ti tabler-coins icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">12,458</h4>
                                <p class="text-success mb-0">(+23%)</p>
                            </div>
                            <small class="mb-0">Registered platform users</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base ti tabler-users icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-6 mb-6">
        <!-- Revenue Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Revenue Analytics</h5>
                        <small class="text-muted">Monthly revenue breakdown</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="revenueChart" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-sm"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="revenueChart">
                            <a class="dropdown-item" href="javascript:void(0);">Last 7 Days</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last 30 Days</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last 90 Days</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="revenueChart" style="height: 300px;">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <i class="icon-base ti tabler-chart-line icon-xl text-muted mb-3"></i>
                                <p class="text-muted">Revenue chart will be displayed here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Machine Status -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Machine Status</h5>
                    <small class="text-muted">Real-time machine monitoring</small>
                    @if(isset($timezoneData) && $timezoneData['statistics']['total_devices'] > 0)
                        <div class="mt-2">
                            <span class="badge bg-info">
                                <i class="icon-base ti tabler-clock me-1"></i>
                                {{ $timezoneData['statistics']['active_devices'] }} devices synced
                            </span>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base ti tabler-check"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">Online</h6>
                                <small class="text-muted">Active machines</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">234</h6>
                            <small class="text-success">95.5%</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="icon-base ti tabler-alert-triangle"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">Maintenance</h6>
                                <small class="text-muted">Under maintenance</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">8</h6>
                            <small class="text-warning">3.3%</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="icon-base ti tabler-x"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">Offline</h6>
                                <small class="text-muted">Not responding</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">3</h6>
                            <small class="text-danger">1.2%</small>
                        </div>
                    </div>

                    @if(isset($timezoneData) && $timezoneData['statistics']['total_devices'] > 0)
                        <!-- Timezone Sync Status -->
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="icon-base ti tabler-clock"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Timezone Sync</h6>
                                    <small class="text-muted">Device timezone status</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-0">{{ $timezoneData['statistics']['active_devices'] }}/{{ $timezoneData['statistics']['total_devices'] }}</h6>
                                <small class="text-info">{{ $timezoneData['statistics']['syncs_today'] }} syncs today</small>
                            </div>
                        </div>

                        @if($timezoneData['recent_syncs']->count() > 0)
                            <div class="mt-3">
                                <small class="text-muted d-block mb-2">Recent syncs:</small>
                                @foreach($timezoneData['recent_syncs']->take(3) as $sync)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">{{ $sync->device_id }}</small>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($sync->sync_timestamp)->diffForHumans() }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities & Quick Actions -->
    <div class="row g-6">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Recent Activities</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-advance">
                        <div class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-success">
                                <i class="icon-base ti tabler-check"></i>
                            </span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Machine RVM-001 came online</h6>
                                    <small class="text-muted">2 minutes ago</small>
                                </div>
                                <p class="mb-0">Machine at Downtown Mall is now operational</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-warning">
                                <i class="icon-base ti tabler-coins"></i>
                            </span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Large deposit detected</h6>
                                    <small class="text-muted">15 minutes ago</small>
                                </div>
                                <p class="mb-0">$2,450 deposited at RVM-045 (Shopping Center)</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-info">
                                <i class="icon-base ti tabler-user-plus"></i>
                            </span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">New user registered</h6>
                                    <small class="text-muted">1 hour ago</small>
                                </div>
                                <p class="mb-0">John Doe joined the platform</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-danger">
                                <i class="icon-base ti tabler-alert-circle"></i>
                            </span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Machine maintenance required</h6>
                                    <small class="text-muted">3 hours ago</small>
                                </div>
                                <p class="mb-0">RVM-023 requires immediate attention</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <button class="btn btn-primary d-flex align-items-center">
                            <i class="icon-base ti tabler-plus me-2"></i>
                            Add New Machine
                        </button>
                        
                        <button class="btn btn-outline-secondary d-flex align-items-center">
                            <i class="icon-base ti tabler-users me-2"></i>
                            Manage Users
                        </button>
                        
                        <button class="btn btn-outline-secondary d-flex align-items-center">
                            <i class="icon-base ti tabler-file-text me-2"></i>
                            Generate Report
                        </button>
                        
                        <button class="btn btn-outline-secondary d-flex align-items-center">
                            <i class="icon-base ti tabler-settings me-2"></i>
                            System Settings
                        </button>

                        @if(isset($timezoneData) && $timezoneData['statistics']['total_devices'] > 0)
                            <a href="{{ route('admin.timezone.index') }}" class="btn btn-outline-info d-flex align-items-center">
                                <i class="icon-base ti tabler-clock me-2"></i>
                                Timezone Management
                                <span class="badge bg-info ms-auto">{{ $timezoneData['statistics']['active_devices'] }}</span>
                            </a>
                        @endif
                    </div>

                    <hr class="my-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">System Health</h6>
                        <span class="badge bg-label-success">Excellent</span>
                    </div>
                    
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">95% system performance</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue Component for Interactive Elements -->
    <div class="mt-6">
        <counter-button></counter-button>
    </div>
</x-admin-layout>