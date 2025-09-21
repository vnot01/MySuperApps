@extends('components.admin-layout')

@section('title', 'All RVMs - RVM Management')
@section('description', 'Manage all Reverse Vending Machines')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/remote-access.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/rvm-modern.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ url('/admin/dashboard') }}">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="javascript:void(0);">
            <i class="fas fa-recycle me-2"></i>RVM Management
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-list me-2"></i>All RVMs
    </li>
@endsection

@section('content')
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 fw-bold text-primary">
                        <i class="fas fa-recycle me-2"></i>RVM Management
                    </h1>
                    <p class="text-muted mb-0">Manage all Reverse Vending Machines</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshAllRVMs()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh All
                    </button>
                    <button class="btn btn-primary" onclick="showAddRVMModal()">
                        <i class="fas fa-plus me-2"></i>Add New RVM
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card statistics-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="fas fa-recycle"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Total RVMs</h6>
                            <h4 class="mb-0 text-primary" id="total-rvm">{{ $rvms->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card statistics-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Active</h6>
                            <h4 class="mb-0 text-success" id="active-rvm">{{ $rvms->where('status', 'active')->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card statistics-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="fas fa-clock"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Timezone Synced</h6>
                            <h4 class="mb-0 text-info" id="timezone-synced">{{ $rvms->whereNotNull('last_timezone_sync')->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card statistics-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Needs Attention</h6>
                            <h4 class="mb-0 text-warning" id="needs-attention">{{ $rvms->whereIn('status', ['error', 'maintenance'])->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RVM Monitoring Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold">RVM Monitoring</h2>
                    <p class="text-muted mb-0">Real-time status of all Reverse Vending Machines</p>
                    <div class="mt-2">
                        <span class="badge bg-info">
                            <i class="fas fa-clock me-1"></i>
                            Timezone sync ready
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <small class="text-muted">Server Time: <span id="server-time" class="fw-medium">--:--:--</span></small>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" class="pagination-btn" onclick="goToPage(1)" title="First Page">
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <button type="button" class="pagination-btn" onclick="changePage(-1)" id="prev-page" title="Previous Page">
                            <i class="fas fa-angle-left"></i>
                        </button>
                        <button type="button" class="pagination-btn" onclick="changePage(1)" id="next-page" title="Next Page">
                            <i class="fas fa-angle-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RVM Cards Grid -->
    <div class="row g-4 mb-4" id="rvm-cards-container">
        @foreach($rvms as $rvm)
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card rvm-card border-0 shadow-sm h-100" data-rvm-id="{{ $rvm->id }}" data-status="{{ $rvm->status }}">
                <div class="card-body d-flex flex-column">
                    <!-- Card Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        {{ substr($rvm->name, -2) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $rvm->name }}</h6>
                                <small class="text-muted">{{ $rvm->location ?? $rvm->location_description ?? 'Not Set' }}</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-text-secondary btn-icon rounded-pill" type="button" data-bs-toggle="dropdown" data-bs-offset="0,10">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="viewRVMDetails({{ $rvm->id }})"><i class="fas fa-eye"></i> View Details</a></li>
                                <li><a class="dropdown-item" href="#" onclick="editRVM({{ $rvm->id }})"><i class="fas fa-edit"></i> Edit RVM</a></li>
                                <li><a class="dropdown-item" href="#" onclick="pingRVM({{ $rvm->id }})"><i class="fas fa-wifi"></i> Ping RVM</a></li>
                                <li><a class="dropdown-item" href="#" onclick="syncTimezone({{ $rvm->id }})"><i class="fas fa-clock"></i> Sync Timezone</a></li>
                                <li><a class="dropdown-item text-primary" href="#" onclick="showRemoteAccessModal({{ $rvm->id }})">
                                    <i class="fas fa-wrench me-2"></i>Enter Maintenance Mode
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteRVM({{ $rvm->id }})"><i class="fas fa-trash"></i> Delete RVM</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Status Indicator -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        @php
                            $statusConfig = [
                                'active' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Active'],
                                'inactive' => ['class' => 'secondary', 'icon' => 'pause-circle', 'text' => 'Inactive'],
                                'maintenance' => ['class' => 'warning', 'icon' => 'wrench', 'text' => 'Maintenance'],
                                'error' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Error'],
                                'full' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Full']
                            ];
                            $config = $statusConfig[$rvm->status] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                        @endphp
                        <span class="text-capitalize text-{{ $config['class'] }}">
                            <i class="fas fa-{{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                        </span>
                        <div class="connection-indicator" id="connection-indicator-{{ $rvm->id }}">
                            <span class="connection-pulse unknown"></span>
                            <span class="badge bg-secondary">
                                Unknown
                            </span>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="system-metrics mb-3">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="metric-item">
                                    <div class="metric-value text-primary" id="cpu-{{ $rvm->id }}">--</div>
                                    <div class="metric-label">CPU</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="metric-item">
                                    <div class="metric-value text-info" id="memory-{{ $rvm->id }}">--</div>
                                    <div class="metric-label">Memory</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="metric-item">
                                    <div class="metric-value text-warning" id="temperature-{{ $rvm->id }}">--</div>
                                    <div class="metric-label">Temp</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Network Information -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">IP Address</small>
                            <small class="fw-medium">{{ $rvm->ip_address ?? 'Not Set' }}</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Port</small>
                            <small class="fw-medium">{{ $rvm->port ?? '8000' }}</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Uptime</small>
                            <small class="fw-medium" id="uptime-{{ $rvm->id }}">--</small>
                        </div>
                    </div>

                    <!-- Time Information -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Local Time</small>
                            <small class="fw-medium time-display" id="local-time-{{ $rvm->id }}">--:--:--</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Timezone</small>
                            <small class="fw-medium" id="timezone-{{ $rvm->id }}">
                                {{ $rvm->timezone ?? 'Asia/Jakarta' }}
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Last Update</small>
                            <small class="fw-medium" id="last-update-{{ $rvm->id }}">
                                {{ $rvm->last_ping ? \Carbon\Carbon::parse($rvm->last_ping)->format('H:i') : 'Never' }}
                            </small>
                        </div>
                    </div>

                    <!-- Remote Access Status -->
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Remote Access</small>
                            <div class="remote-access-status" data-rvm-id="{{ $rvm->id }}">
                                <span class="badge bg-secondary">
                                    <i class="fas fa-circle"></i> Inactive
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
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

    <!-- Data for JavaScript -->
    <script>
        window.rvmData = @json($rvms);
        window.currentPage = 1;
        window.itemsPerPage = 12;
        window.totalPages = Math.ceil(window.rvmData.length / window.itemsPerPage);
    </script>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/dashboard/remote-access.js') }}"></script>
    <script>
    // Global variables
    let rvmData = @json($rvms);
    let currentPage = 1;
    let itemsPerPage = 12;
    let totalPages = Math.ceil(rvmData.length / itemsPerPage);

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        updateServerTime();
        updateLocalTimes();
        startTimeUpdates();
        loadSystemMetrics();
        
        // Update pagination buttons
        updatePaginationButtons();
    });

    // Update server time
    function updateServerTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        // Update server time
        document.getElementById('server-time').textContent = timeString;
    }

    // Update local times for all RVMs based on their timezone
    function updateLocalTimes() {
        rvmData.forEach(rvm => {
            const timeElement = document.getElementById(`local-time-${rvm.id}`);
            if (timeElement) {
                const now = new Date();
                let timeString;
                
                // Get RVM timezone from data
                const rvmTimezone = rvm.timezone || 'Asia/Jakarta'; // Default to Indonesia
                
                try {
                    // Convert to RVM's local timezone
                    const rvmTime = new Date(now.toLocaleString("en-US", {timeZone: rvmTimezone}));
                    timeString = rvmTime.toLocaleTimeString('en-US', { 
                        hour12: false,
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        timeZone: rvmTimezone
                    });
                } catch (error) {
                    // Fallback to server time if timezone is invalid
                    timeString = now.toLocaleTimeString('en-US', { 
                        hour12: false,
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                }
                
                timeElement.textContent = timeString;
            }
        });
    }

    // Start time updates
    function startTimeUpdates() {
        setInterval(() => {
            updateServerTime();
            updateLocalTimes();
        }, 1000);
    }

    // Load system metrics
    function loadSystemMetrics() {
        rvmData.forEach(rvm => {
            // Simulate metrics loading
            setTimeout(() => {
                updateRVMetrics(rvm.id);
            }, Math.random() * 2000);
        });
    }

    // Update RVM metrics
    function updateRVMetrics(rvmId) {
        // Simulate real-time metrics
        const cpu = Math.floor(Math.random() * 100);
        const memory = Math.floor(Math.random() * 100);
        const temperature = Math.floor(Math.random() * 20) + 40; // 40-60°C
        const uptime = Math.floor(Math.random() * 86400); // 0-24 hours in seconds

        document.getElementById(`cpu-${rvmId}`).textContent = `${cpu}%`;
        document.getElementById(`memory-${rvmId}`).textContent = `${memory}%`;
        document.getElementById(`temperature-${rvmId}`).textContent = `${temperature}°C`;
        document.getElementById(`uptime-${rvmId}`).textContent = formatDuration(uptime);

        // Update connection status
        updateConnectionStatus(rvmId, Math.random() > 0.2); // 80% chance connected
    }

    // Update connection status
    function updateConnectionStatus(rvmId, isConnected) {
        const indicator = document.getElementById(`connection-indicator-${rvmId}`);
        if (indicator) {
            const pulse = indicator.querySelector('.connection-pulse');
            const badge = indicator.querySelector('.badge');
            
            if (isConnected) {
                pulse.className = 'connection-pulse connected';
                badge.className = 'badge bg-success';
                badge.innerHTML = '<i class="fas fa-circle me-1"></i>Connected';
            } else {
                pulse.className = 'connection-pulse disconnected';
                badge.className = 'badge bg-danger';
                badge.innerHTML = '<i class="fas fa-circle me-1"></i>Disconnected';
            }
        }
    }

    // Format duration
    function formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }

    // Pagination functions
    function goToPage(page) {
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            updatePaginationButtons();
            // Here you would implement actual pagination logic
        }
    }

    function changePage(direction) {
        const newPage = currentPage + direction;
        goToPage(newPage);
    }

    function updatePaginationButtons() {
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
    }

    // RVM Action Functions
    function showRemoteAccessModal(rvmId) {
        const rvm = rvmData.find(r => r.id === rvmId);
        if (!rvm) {
            alert('❌ RVM not found');
            return;
        }
        
        // Update modal title
        document.getElementById('remoteAccessModalLabel').innerHTML = `<i class="fas fa-wrench me-2"></i>Enter Maintenance Mode - ${rvm.name}`;
        
        // Set up button click handler
        document.getElementById('enterMaintenanceModeBtn').onclick = () => enterMaintenanceMode(rvmId);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('remoteAccessModal'));
        modal.show();
    }

    // Enter Maintenance Mode - Redirect to full page
    function enterMaintenanceMode(rvmId) {
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('remoteAccessModal'));
        modal.hide();
        
        // Redirect to maintenance mode page
        window.location.href = `/admin/rvm/${rvmId}/maintenance-mode`;
    }

    // Other RVM functions (placeholder implementations)
    function viewRVMDetails(rvmId) {
        alert(`View details for RVM ${rvmId}`);
    }

    function editRVM(rvmId) {
        alert(`Edit RVM ${rvmId}`);
    }

    function pingRVM(rvmId) {
        alert(`Ping RVM ${rvmId}`);
    }

    function syncTimezone(rvmId) {
        alert(`Sync timezone for RVM ${rvmId}`);
    }

    function deleteRVM(rvmId) {
        if (confirm(`Are you sure you want to delete RVM ${rvmId}?`)) {
            alert(`Delete RVM ${rvmId}`);
        }
    }

    function refreshAllRVMs() {
        location.reload();
    }

    function showAddRVMModal() {
        alert('Show add RVM modal');
    }

    // Auto refresh every 30 seconds
    setInterval(() => {
        loadSystemMetrics();
    }, 30000);
    </script>
@endsection