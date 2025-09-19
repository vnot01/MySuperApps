@extends('components.admin-layout')

@section('title', 'All RVMs - RVM Management')
@section('description', 'Manage all Reverse Vending Machines')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
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
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
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
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-warning" href="#" onclick="maintenanceRVM({{ $rvm->id }})">
                                                    <i class="fas fa-wrench me-2"></i>Maintenance
                                                </a></li>
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
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
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

@endsection

@section('scripts')
<script>
// Global variables
let rvmData = @json($rvms);

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Ping all RVMs on page load
    pingAllRVMs();
    
    // Auto refresh every 30 seconds
    setInterval(function() {
        refreshAllRVMs();
    }, 30000);
});

// Ping all RVMs
function pingAllRVMs() {
    rvmData.forEach(rvm => {
        if (rvm.ip_address) {
            pingRVM(rvm.id);
        }
    });
}

// Ping specific RVM
function pingRVM(rvmId) {
    const rvm = rvmData.find(r => r.id === rvmId);
    if (!rvm || !rvm.ip_address) {
        updateConnectionStatus(rvmId, 'error', 'No IP Address');
        return;
    }

    const connectionElement = document.getElementById(`connection-${rvmId}`);
    connectionElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Pinging...';
    connectionElement.className = 'badge bg-warning me-2';

    // Simulate ping (replace with actual API call)
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
            updateConnectionStatus(rvmId, 'success', 'Connected');
        } else {
            updateConnectionStatus(rvmId, 'danger', 'Disconnected');
        }
    })
    .catch(error => {
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

// Test connection in modal
function testConnection() {
    const ipAddress = document.querySelector('input[name="ip_address"]').value;
    const port = document.querySelector('input[name="port"]').value;
    
    if (!ipAddress) {
        alert('Please enter IP address');
        return;
    }

    const testBtn = document.querySelector('button[onclick="testConnection()"]');
    const originalText = testBtn.innerHTML;
    testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Testing...';
    testBtn.disabled = true;

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
    .then(response => response.json())
    .then(data => {
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
        testBtn.innerHTML = originalText;
        testBtn.disabled = false;
        alert('❌ Connection test error: ' + error.message);
    });
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

// Refresh all RVMs
function refreshAllRVMs() {
    location.reload();
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
document.getElementById('addRvmForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
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
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
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
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        alert('❌ Network error: ' + error.message);
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
    alert('🔧 Remote Access feature will be implemented soon!\n\nRVM ID: ' + rvmId);
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
