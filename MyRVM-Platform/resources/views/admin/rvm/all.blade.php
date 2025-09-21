@extends('components.admin-layout')

@section('title', 'All RVMs - RVM Management')
@section('description', 'Manage all Reverse Vending Machines')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/remote-access.css') }}">
    <style>
        /* Make RVM List card expandable to bottom */
        .rvm-list-container {
            min-height: calc(100vh - 300px);
            display: flex;
            flex-direction: column;
        }
        
        .rvm-list-card {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .rvm-list-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .rvm-list-card .table-responsive {
            flex: 1;
        }
        
        .rvm-list-card .table {
            height: 100%;
        }
        
        /* Port details styling */
        .port-details {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .port-details .d-block {
            margin-bottom: 2px;
        }
        
        .port-details .text-success {
            color: #28a745 !important;
        }
        
        .port-details .text-danger {
            color: #dc3545 !important;
        }
        
        .connection-status {
            font-size: 0.875rem;
        }
        
        .connection-status .text-success {
            color: #28a745 !important;
        }
        
        .connection-status .text-danger {
            color: #dc3545 !important;
        }
        
        .connection-status .text-warning {
            color: #ffc107 !important;
        }
    </style>
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
<!-- <div class="container-fluid"> -->
    <!-- Header -->
    <!-- <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 fw-bold text-primary">
                        <i class="fas fa-recycle me-2"></i>
                        MyRVM Platform
                    </h1>
                    <h2 class="mb-1 fw-bold">
                        All RVMs
                    </h2>
                    <p class="text-muted mb-0">Manage all Reverse Vending Machines and their timezone settings</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshAllRVMs()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh All
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRvmModal">
                        <i class="fas fa-plus me-1"></i>
                        Add New RVM
                    </button>
                </div>
            </div>
        </div>
    </div> -->
    <!-- Modern Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header modern-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="mb-1 fw-bold text-primary">
                            <i class="fas fa-recycle me-2"></i>
                            RVM Management
                        </h1>
                        <h2 class="mb-1 fw-bold">
                            All RVMs
                        </h2>
                        <p class="text-muted mb-0">Manage all Reverse Vending Machines and their timezone settings</p>
                        </div>
                        <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshAllRVMs()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh All
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRvmModal">
                            <i class="fas fa-plus me-1"></i>
                            Add New RVM
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
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
                            <h4 class="mb-0 text-primary" id="total-rvms">{{ $rvms->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
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
                            <h6 class="mb-0">Active RVMs</h6>
                            <h4 class="mb-0 text-success" id="active-rvms">{{ $activeCount ?? $rvms->where('status', 'active')->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
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
                            <h4 class="mb-0 text-info" id="timezone-synced">{{ $timezoneSyncedCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
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
                            <h4 class="mb-0 text-warning" id="needs-attention">{{ $needsAttentionCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RVM List -->
    <div class="row flex-grow-1 rvm-list-container">
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100 rvm-list-card">
                <div class="card-header border-0 bg-transparent p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">RVM List</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="statusFilter" onchange="filterRVMs()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="error">Error</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search RVMs..." onkeyup="searchRVMs()">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>RVM ID</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>IP Address</th>
                                    <th>Timezone</th>
                                    <th>Last Sync</th>
                                    <th>Remote Access</th>
                                    <th>Connection</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="rvmTableBody">
                                @foreach($rvms as $rvm)
                                <tr data-rvm-id="{{ $rvm->id }}" data-status="{{ $rvm->status }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <div class="avatar avatar-xs">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        {{ substr($rvm->name, -2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $rvm->name }}</h6>
                                                <small class="text-muted">ID: {{ $rvm->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $rvm->location ?? $rvm->location_description ?? 'Not Set' }}</h6>
                                            <small class="text-muted">{{ $rvm->address ?? 'Not Set' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'active' => ['class' => 'success', 'icon' => 'check-circle'],
                                                'inactive' => ['class' => 'secondary', 'icon' => 'pause-circle'],
                                                'maintenance' => ['class' => 'warning', 'icon' => 'wrench'],
                                                'error' => ['class' => 'danger', 'icon' => 'exclamation-triangle']
                                            ];
                                            $config = $statusConfig[$rvm->status] ?? ['class' => 'secondary', 'icon' => 'question-circle'];
                                        @endphp
                                        <span class="badge bg-{{ $config['class'] }}">
                                            <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                            {{ ucfirst($rvm->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $rvm->ip_address ?? 'Not Set' }}</h6>
                                            <small class="text-muted">Port: {{ $rvm->port ?? '8000' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0" id="timezone-{{ $rvm->id }}">
                                                {{ $rvm->timezone ?? 'Not Set' }}
                                            </h6>
                                            <small class="text-muted" id="timezone-offset-{{ $rvm->id }}">
                                                {{ $rvm->timezone_offset ?? 'Unknown' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0" id="last-sync-{{ $rvm->id }}">
                                                {{ $rvm->last_timezone_sync ? \Carbon\Carbon::parse($rvm->last_timezone_sync)->diffForHumans() : 'Never' }}
                                            </h6>
                                            <small class="text-muted" id="sync-time-{{ $rvm->id }}">
                                                {{ $rvm->last_timezone_sync ? \Carbon\Carbon::parse($rvm->last_timezone_sync)->format('M d, Y H:i') : 'No sync' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="remote-access-status" data-rvm-id="{{ $rvm->id }}">
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-circle"></i> Inactive
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-secondary me-2" id="connection-{{ $rvm->id }}">
                                                <i class="fas fa-question-circle me-1"></i>
                                                Unknown
                                            </span>
                                            <button class="btn btn-sm btn-outline-primary" onclick="pingRVM({{ $rvm->id }})">
                                                <i class="fas fa-wifi"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="viewRVMDetails({{ $rvm->id }})">
                                                    <i class="fas fa-eye me-2"></i>View Details
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="editRVM({{ $rvm->id }})">
                                                    <i class="fas fa-edit me-2"></i>Edit RVM
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="syncTimezone({{ $rvm->id }})">
                                                    <i class="fas fa-clock me-2"></i>Sync Timezone
                                                </a></li>
                                                <li><a class="dropdown-item text-primary" href="#" onclick="showRemoteAccessModal({{ $rvm->id }})">
                                                    <i class="fas fa-wrench me-2"></i>Enter Maintenance Mode
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteRVM({{ $rvm->id }})">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- </div> -->

<!-- Add RVM Modal -->
<div class="modal fade" id="addRvmModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Add New RVM
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRvmForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">RVM Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">IP Address</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="ip_address" placeholder="192.168.1.100" required>
                                    <button class="btn btn-outline-primary" type="button" onclick="testConnection()">
                                        <i class="fas fa-wifi me-1"></i>
                                        Test
                                    </button>
                                </div>
                                <div class="form-text">Enter the IP address of the Jetson Orin device</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Port</label>
                                <input type="number" class="form-control" name="port" value="8000">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Timezone</label>
                                <select class="form-select" name="timezone">
                                    <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                                    <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                    <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Capacity (%)</label>
                                <input type="number" class="form-control" name="capacity" value="0" min="0" max="100">
                                <div class="form-text">Current capacity percentage</div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Connection Test:</strong> Click "Test" button to verify connection to the Jetson Orin device before saving.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Add RVM
                    </button>
                </div>
            </form>
        </div>
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


@endsection

@section('page-js')
<script src="{{ asset('js/admin/dashboard/remote-access.js') }}"></script>
<script>
// Global variables
let rvmData = @json($rvms);

// ===== GLOBAL FUNCTIONS =====
// Refresh all RVMs
function refreshAllRVMs() {
    // Use AJAX to refresh only the RVM data instead of full page reload
    fetch('/admin/rvm', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update RVM data
            rvmData = data.rvms;
            
            // Update statistics
            updateStatistics(data.statistics);
            
            // Update RVM table
            updateRVMTable(data.rvms);
            
            // Ping all RVMs to update connection status
            pingAllRVMs();
        }
    })
    .catch(error => {
        console.error('Refresh error:', error);
        // Fallback to full page reload if AJAX fails
        location.reload();
    });
}

// Update statistics display
function updateStatistics(statistics) {
    document.querySelector('.stat-card.total .stat-number').textContent = statistics.total;
    document.querySelector('.stat-card.active .stat-number').textContent = statistics.active;
    document.querySelector('.stat-card.timezone-synced .stat-number').textContent = statistics.timezone_synced;
    document.querySelector('.stat-card.needs-attention .stat-number').textContent = statistics.needs_attention;
}

// Update RVM table display
function updateRVMTable(rvms) {
    const tbody = document.querySelector('#rvmTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    rvms.forEach(rvm => {
        const row = createRVMTableRow(rvm);
        tbody.appendChild(row);
    });
}

// Create RVM table row
function createRVMTableRow(rvm) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <div class="rvm-id">
                <strong>${rvm.name}</strong>
                <small class="text-muted">ID: ${rvm.id}</small>
            </div>
        </td>
        <td>
            <div class="location-info">
                <strong>${rvm.location || 'Not Set'}</strong>
                ${rvm.address ? `<br><small class="text-muted">${rvm.address}</small>` : ''}
            </div>
        </td>
        <td>
            <span class="badge bg-${getStatusClass(rvm.status)}">${rvm.status}</span>
        </td>
        <td>
            <div class="connection-info">
                <strong>${rvm.ip_address || 'Not Set'}</strong>
                ${rvm.port ? `<br><small class="text-muted">Port: ${rvm.port}</small>` : ''}
            </div>
        </td>
        <td>
            <div class="timezone-info">
                <strong>${rvm.timezone || 'Not Set'}</strong>
                ${rvm.timezone_offset ? `<br><small class="text-muted">${rvm.timezone_offset}</small>` : ''}
            </div>
        </td>
        <td>
            <div class="sync-info">
                <strong>${rvm.last_timezone_sync ? formatDate(rvm.last_timezone_sync) : 'Never'}</strong>
                <br><small class="text-muted">${rvm.last_timezone_sync ? 'Synced' : 'No sync'}</small>
            </div>
        </td>
        <td>
            <span class="badge bg-secondary">Inactive</span>
        </td>
        <td>
            <div id="connection-${rvm.id}" class="connection-status">
                <span class="badge bg-warning">
                    <i class="fas fa-wifi"></i> Testing...
                </span>
            </div>
        </td>
        <td>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="viewRVMDetails(${rvm.id})"><i class="fas fa-eye"></i> View Details</a></li>
                    <li><a class="dropdown-item" href="#" onclick="editRVM(${rvm.id})"><i class="fas fa-edit"></i> Edit RVM</a></li>
                    <li><a class="dropdown-item" href="#" onclick="pingRVM(${rvm.id})"><i class="fas fa-wifi"></i> Ping RVM</a></li>
                    <li><a class="dropdown-item" href="#" onclick="syncTimezone(${rvm.id})"><i class="fas fa-clock"></i> Sync Timezone</a></li>
                    <li><a class="dropdown-item" href="#" onclick="remoteAccess(${rvm.id})"><i class="fas fa-wrench"></i> Enter Maintenance Mode</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteRVM(${rvm.id})"><i class="fas fa-trash"></i> Delete RVM</a></li>
                </ul>
            </div>
        </td>
    `;
    return row;
}

// Helper functions
function getStatusClass(status) {
    const statusClasses = {
        'active': 'success',
        'inactive': 'secondary',
        'maintenance': 'warning',
        'full': 'danger',
        'error': 'danger'
    };
    return statusClasses[status] || 'secondary';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Test connection in modal
function testConnection() {
    console.log('testConnection function called'); // Debug log
    
    const ipAddress = document.querySelector('input[name="ip_address"]').value;
    const port = document.querySelector('input[name="port"]').value;
    
    console.log('IP Address:', ipAddress, 'Port:', port); // Debug log
    
    if (!ipAddress) {
        alert('Please enter IP address');
        return;
    }

    const testBtn = document.querySelector('button[onclick="testConnection()"]');
    const originalText = testBtn.innerHTML;
    testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Testing...';
    testBtn.disabled = true;

    console.log('Making API call to test connection...'); // Debug log

    // Real connection test via API
    fetch('/admin/rvm/test-connection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            ip_address: ipAddress,
            port: port || 8000
        })
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug log
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // Debug log
        testBtn.innerHTML = originalText;
        testBtn.disabled = false;
        
        if (data.success) {
            if (data.is_dummy) {
                alert('✅ Dummy data detected (0.0.0.0) - No actual connection test performed');
            } else {
                alert(`✅ Connection successful!\nResponse time: ${data.response_time}ms\nMessage: ${data.message}`);
            }
        } else {
            alert(`❌ Connection failed!\nError: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error); // Debug log
        testBtn.innerHTML = originalText;
        testBtn.disabled = false;
        alert('❌ Connection test error: ' + error.message);
    });
}

// Ping all RVMs
function pingAllRVMs() {
    rvmData.forEach(rvm => {
        if (rvm.ip_address) {
            pingRVM(rvm.id);
        }
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Ping all RVMs on page load
    pingAllRVMs();
    
    // Auto refresh every 30 seconds (only if no modal is open)
    setInterval(function() {
        // Check if any modal is open
        const openModals = document.querySelectorAll('.modal.show');
        const modalOpen = window.modalOpen || false;
        
        if (openModals.length === 0 && !modalOpen) {
            refreshAllRVMs();
        }
    }, 30000);
    
    // Add event listeners for modal open/close
    document.addEventListener('show.bs.modal', function(event) {
        console.log('Modal opened:', event.target.id);
        // Stop auto-refresh when modal is open
        window.modalOpen = true;
    });
    
    document.addEventListener('hide.bs.modal', function(event) {
        console.log('Modal closed:', event.target.id);
        // Resume auto-refresh when modal is closed
        window.modalOpen = false;
    });
});

// Ping specific RVM
function pingRVM(rvmId) {
    const rvm = rvmData.find(r => r.id === rvmId);
    if (!rvm || !rvm.ip_address) {
        updateConnectionStatus(rvmId, 'error', 'No IP Address');
        return;
    }

    const connectionElement = document.getElementById(`connection-${rvmId}`);
    connectionElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Testing...';
    connectionElement.className = 'badge bg-warning me-2';

    // Ping RVM with multi-port testing
    fetch(`/admin/rvm/ping/${rvmId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pingResult = data.data.ping_result;
            
            if (pingResult && pingResult.success) {
                // Show successful connection
                updateConnectionStatus(rvmId, 'success', 'Connected');
                
                // Show detailed port information
                if (pingResult.ports) {
                    let portInfo = '<div class="port-details mt-2">';
                    for (const [port, result] of Object.entries(pingResult.ports)) {
                        const icon = result.success ? 'check-circle text-success' : 'times-circle text-danger';
                        portInfo += `<small class="d-block"><i class="fas fa-${icon}"></i> ${result.service}: ${result.response_time}ms</small>`;
                    }
                    portInfo += '</div>';
                    connectionElement.innerHTML += portInfo;
                }
            } else {
                updateConnectionStatus(rvmId, 'danger', 'Disconnected');
            }
        } else {
            console.error('Ping API error:', data.message || 'Unknown error');
            updateConnectionStatus(rvmId, 'danger', 'Error');
        }
    })
    .catch(error => {
        console.error('Ping error:', error);
        updateConnectionStatus(rvmId, 'danger', 'Error');
    });
}

// Update connection status
function updateConnectionStatus(rvmId, status, text) {
    const connectionElement = document.getElementById(`connection-${rvmId}`);
    const statusConfig = {
        'success': { class: 'success', icon: 'check-circle' },
        'danger': { class: 'danger', icon: 'times-circle' },
        'warning': { class: 'warning', icon: 'exclamation-triangle' },
        'error': { class: 'secondary', icon: 'question-circle' }
    };
    
    const config = statusConfig[status] || statusConfig['error'];
    connectionElement.innerHTML = `<i class="fas fa-${config.icon} me-1"></i>${text}`;
    connectionElement.className = `badge bg-${config.class} me-2`;
}


// Sync timezone for specific RVM
function syncTimezone(rvmId) {
    const rvm = rvmData.find(r => r.id === rvmId);
    if (!rvm) return;

    if (confirm(`Sync timezone for ${rvm.name}?`)) {
        fetch(`/admin/rvm/sync-timezone/${rvmId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Timezone sync initiated successfully');
                refreshAllRVMs();
            } else {
                alert('Timezone sync failed: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

// Filter RVMs by status
function filterRVMs() {
    const status = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#rvmTableBody tr');
    
    rows.forEach(row => {
        if (!status || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Search RVMs
function searchRVMs() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#rvmTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Add/Edit RVM form submission
console.log('Setting up form event listener...'); // Debug log
document.getElementById('addRvmForm').addEventListener('submit', function(e) {
    console.log('Form submit event triggered!'); // Debug log
    e.preventDefault();
    
    // Validate required fields first
    const requiredFields = ['name', 'location', 'ip_address', 'timezone', 'status'];
    const missingFields = [];
    
    requiredFields.forEach(field => {
        const input = this.querySelector(`[name="${field}"]`);
        if (!input || !input.value.trim()) {
            missingFields.push(field);
        }
    });
    
    // Validate capacity if provided
    const capacityInput = this.querySelector('[name="capacity"]');
    if (capacityInput && capacityInput.value) {
        const capacity = parseInt(capacityInput.value);
        if (isNaN(capacity) || capacity < 0 || capacity > 100) {
            alert('❌ Capacity must be a number between 0 and 100');
            return;
        }
    }
    
    if (missingFields.length > 0) {
        alert(`❌ Please fill in all required fields:\n• ${missingFields.join('\n• ')}`);
        return;
    }
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const editId = this.getAttribute('data-edit-id');
    
    // Show loading state
    const submitBtn = document.querySelector('#addRvmForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    const isEdit = !!editId;
    
    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>${isEdit ? 'Updating...' : 'Adding...'}`;
    submitBtn.disabled = true;
    
    const url = isEdit ? `/admin/rvm/${editId}` : '/admin/rvm';
    const method = isEdit ? 'PUT' : 'POST';
    
    console.log('Submitting RVM data:', data); // Debug log
    console.log('URL:', url, 'Method:', method); // Debug log
    
    // Check CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    console.log('CSRF Token:', csrfToken ? csrfToken.getAttribute('content') : 'NOT FOUND'); // Debug log
    
    // Test debug route first
    console.log('Testing debug route...'); // Debug log
    fetch('/admin/rvm/debug', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ test: 'data' })
    })
    .then(response => response.json())
    .then(debugData => {
        console.log('Debug route response:', debugData); // Debug log
    })
    .catch(debugError => {
        console.error('Debug route error:', debugError); // Debug log
    });
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug log
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // Debug log
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        if (data.success) {
            // Show success message
            const action = isEdit ? 'updated' : 'added';
            alert(`✅ RVM ${action} successfully!\n\nRVM ID: ${data.data.id}\nName: ${data.data.name}\nIP: ${data.data.ip_address}`);
            
            // Close modal and reload page
            const modal = bootstrap.Modal.getInstance(document.getElementById('addRvmModal'));
            modal.hide();
            
            // Reset form and modal title
            this.reset();
            this.removeAttribute('data-edit-id');
            document.querySelector('#addRvmModal .modal-title').innerHTML = '<i class="fas fa-plus me-2"></i>Add New RVM';
            document.querySelector('#addRvmForm button[type="submit"]').innerHTML = '<i class="fas fa-plus me-1"></i>Add RVM';
            
            location.reload();
        } else {
            // Show error message with details
            const action = isEdit ? 'updating' : 'adding';
            let errorMsg = `❌ Error ${action} RVM:\n${data.message}`;
            if (data.errors) {
                errorMsg += '\n\nValidation errors:';
                for (const [field, errors] of Object.entries(data.errors)) {
                    errorMsg += '\n• ' + field + ': ' + errors.join(', ');
                }
            }
            alert(errorMsg);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error); // Debug log
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        const errorMessage = error.message || error.toString() || 'Unknown network error';
        alert('❌ Network error: ' + errorMessage);
    });
});

// RVM Action Functions
function viewRVMDetails(rvmId) {
    // Fetch RVM details and show in modal
    fetch(`/admin/rvm/${rvmId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const rvm = data.data;
                const modalHtml = `
                    <div class="modal fade" id="viewRvmModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-eye me-2"></i>
                                        RVM Details - ${rvm.name}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Basic Information</h6>
                                            <p><strong>ID:</strong> ${rvm.id}</p>
                                            <p><strong>Name:</strong> ${rvm.name}</p>
                                            <p><strong>Location:</strong> ${rvm.location || 'Not Set'}</p>
                                            <p><strong>Address:</strong> ${rvm.address || 'Not Set'}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Network & Status</h6>
                                            <p><strong>IP Address:</strong> ${rvm.ip_address || 'Not Set'}</p>
                                            <p><strong>Port:</strong> ${rvm.port || 8000}</p>
                                            <p><strong>Status:</strong> <span class="badge bg-success">${rvm.status}</span></p>
                                            <p><strong>Timezone:</strong> ${rvm.timezone || 'Not Set'}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                const modal = new bootstrap.Modal(document.getElementById('viewRvmModal'));
                modal.show();
                
                // Clean up modal after hiding
                document.getElementById('viewRvmModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            } else {
                alert('Error loading RVM details: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function editRVM(rvmId) {
    // Fetch RVM data and populate edit form
    fetch(`/admin/rvm/${rvmId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const rvm = data.data;
                // Populate the add form with existing data
                document.querySelector('input[name="name"]').value = rvm.name || '';
                document.querySelector('input[name="location"]').value = rvm.location || '';
                document.querySelector('textarea[name="address"]').value = rvm.address || '';
                document.querySelector('input[name="ip_address"]').value = rvm.ip_address || '';
                document.querySelector('input[name="port"]').value = rvm.port || 8000;
                document.querySelector('select[name="timezone"]').value = rvm.timezone || 'Asia/Jakarta';
                document.querySelector('select[name="status"]').value = rvm.status || 'active';
                document.querySelector('input[name="capacity"]').value = rvm.capacity || 0;
                
                // Change modal title and submit button
                document.querySelector('#addRvmModal .modal-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit RVM';
                document.querySelector('#addRvmForm button[type="submit"]').innerHTML = '<i class="fas fa-save me-1"></i>Update RVM';
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('addRvmModal'));
                modal.show();
                
                // Change form action to update
                document.getElementById('addRvmForm').setAttribute('data-edit-id', rvmId);
            } else {
                alert('Error loading RVM data: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function maintenanceRVM(rvmId) {
    window.location.href = `/admin/rvm/maintenance`;
}

function deleteRVM(rvmId) {
    if (confirm('Are you sure you want to delete this RVM? This action cannot be undone.')) {
        fetch(`/admin/rvm/${rvmId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ RVM deleted successfully!');
                location.reload();
            } else {
                alert('❌ Error deleting RVM: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
        });
    }
}

function syncTimezone(rvmId) {
    if (confirm('Sync timezone for this RVM?')) {
        fetch(`/admin/rvm/sync-timezone/${rvmId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Timezone synced successfully!');
                location.reload();
            } else {
                alert('❌ Error syncing timezone: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
        });
    }
}

function remoteAccess(rvmId) {
    // This function is called from the dropdown menu
    // It should trigger the same modal as showRemoteAccessModal
    showRemoteAccessModal(rvmId);
}

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

function startRemoteAccessFromModal(rvmId) {
    const adminId = getCurrentAdminId();
    
    if (!adminId) {
        alert('❌ Admin ID not found. Please login again.');
        return;
    }
    
    const accessType = document.getElementById('accessType').value;
    const sessionDuration = document.getElementById('sessionDuration').value;
    const reason = document.getElementById('accessReason').value;
    
    // Show loading state
    const button = document.getElementById('remoteAccessActionBtn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting...';
    button.disabled = true;
    
    fetch(`/admin/rvm/${rvmId}/remote-access/start`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            admin_id: adminId,
            ip_address: getClientIP(),
            port: accessType === 'camera' ? 5000 : 5001,
            access_type: accessType,
            session_duration: parseInt(sessionDuration),
            reason: reason || 'Remote access session started from RVM management'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Remote access started successfully!\n\nSession ID: ${data.data.session_id}\nRVM Status: ${data.data.status}`);
            
            // Close modal and refresh page
            bootstrap.Modal.getInstance(document.getElementById('remoteAccessModal')).hide();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            const errorMessage = data.message || 'Unknown error occurred';
            alert(`❌ Failed to start remote access:\n${errorMessage}`);
        }
    })
    .catch(error => {
        console.error('Remote access start error:', error);
        const errorMessage = error.message || error.toString() || 'Unknown network error';
        alert('❌ Network error: ' + errorMessage);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function getStatusClass(status) {
    const statusClasses = {
        'active': 'success',
        'inactive': 'secondary',
        'maintenance': 'warning',
        'full': 'danger',
        'error': 'danger'
    };
    return statusClasses[status] || 'secondary';
}

function getCurrentAdminId() {
    const adminIdMeta = document.querySelector('meta[name="admin-id"]');
    return adminIdMeta ? adminIdMeta.getAttribute('content') : null;
}

function getClientIP() {
    return '192.168.1.100'; // Placeholder - would need to be implemented based on your setup
}

// Check port from modal
function checkPortFromModal(rvmId) {
    const accessType = document.getElementById('accessType').value;
    const port = accessType === 'camera' ? 5000 : 5001;
    const checkBtn = document.getElementById('checkPortBtn');
    const portStatus = document.getElementById('portStatus');
    
    // Show loading state
    const originalText = checkBtn.innerHTML;
    checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    checkBtn.disabled = true;
    
    fetch(`/admin/rvm/${rvmId}/remote-access/check-port`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            port: port,
            access_type: accessType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const result = data.data;
            const statusIcon = result.status === 'open' ? 'check-circle' : 
                              result.status === 'reject' ? 'exclamation-triangle' : 'times-circle';
            
            portStatus.innerHTML = `
                <div class="alert alert-${result.status_class} mb-0">
                    <i class="fas fa-${statusIcon}"></i>
                    <strong>Port ${result.port} (${result.service_name}): ${result.status_text}</strong>
                    <br>
                    <small>Response Time: ${result.response_time}ms | ${result.message}</small>
                </div>
            `;
            portStatus.style.display = 'block';
        } else {
            portStatus.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-times-circle"></i>
                    <strong>Port Check Failed</strong>
                    <br>
                    <small>${data.message}</small>
                </div>
            `;
            portStatus.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Port check error:', error);
        portStatus.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fas fa-times-circle"></i>
                <strong>Network Error</strong>
                <br>
                <small>Failed to check port: ${error.message}</small>
            </div>
        `;
        portStatus.style.display = 'block';
    })
    .finally(() => {
        checkBtn.innerHTML = originalText;
        checkBtn.disabled = false;
    });
}

function updateStatus(rvmId) {
    // Get RVM name for display
    const rvmRow = document.querySelector(`tr[data-rvm-id="${rvmId}"]`);
    const rvmName = rvmRow ? rvmRow.querySelector('.card-title, h6').textContent : `RVM-${rvmId}`;
    
    // Create modal HTML with dropdown
    const modalHtml = `
        <div class="modal fade" id="updateStatusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>
                            Update Status - ${rvmName}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select New Status:</label>
                            <select class="form-select" id="statusSelect">
                                <option value="">Choose status...</option>
                                <option value="active">✅ Active</option>
                                <option value="inactive">⏸️ Inactive</option>
                                <option value="maintenance">🔧 Maintenance</option>
                                <option value="error">❌ Error</option>
                                <option value="full">📦 Full</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>RVM ID:</strong> ${rvmId}<br>
                            <strong>Current Status:</strong> <span id="currentStatus">Loading...</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="updateStatusBtn" disabled>
                            <i class="fas fa-save me-1"></i>Update Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('updateStatusModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
    
    // Get current status and populate dropdown
    fetch(`/admin/rvm/${rvmId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const currentStatus = data.data.status;
                document.getElementById('currentStatus').textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
                
                // Set current status as selected
                const statusSelect = document.getElementById('statusSelect');
                statusSelect.value = currentStatus;
            }
        })
        .catch(error => {
            console.error('Error fetching current status:', error);
            document.getElementById('currentStatus').textContent = 'Unknown';
        });
    
    // Handle dropdown change
    document.getElementById('statusSelect').addEventListener('change', function() {
        const updateBtn = document.getElementById('updateStatusBtn');
        updateBtn.disabled = !this.value;
    });
    
    // Handle update button click
    document.getElementById('updateStatusBtn').addEventListener('click', function() {
        const newStatus = document.getElementById('statusSelect').value;
        
        if (!newStatus) {
            alert('Please select a status');
            return;
        }
        
        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
        
        // Update status via API
        fetch(`/admin/rvm/${rvmId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert(`✅ Status updated successfully!\n\n${rvmName} is now ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}`);
                
                // Close modal
                modal.hide();
                
                // Refresh the page
                location.reload();
            } else {
                alert(`❌ Error updating status: ${data.message}`);
                // Re-enable button
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save me-1"></i>Update Status';
            }
        })
        .catch(error => {
            alert(`❌ Error: ${error.message}`);
            // Re-enable button
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save me-1"></i>Update Status';
        });
    });
    
    // Clean up modal after hiding
    document.getElementById('updateStatusModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

</script>
@endsection
