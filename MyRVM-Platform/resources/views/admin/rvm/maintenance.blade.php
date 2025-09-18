@extends('components.admin-layout')

@section('title', 'RVM Maintenance - RVM Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold">
                        <i class="fas fa-wrench me-2 text-warning"></i>
                        RVM Maintenance
                    </h2>
                    <p class="text-muted mb-0">Maintenance tools and timezone synchronization for all RVMs</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-warning" onclick="refreshMaintenanceData()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>
                    <button class="btn btn-warning" onclick="bulkTimezoneSync()">
                        <i class="fas fa-clock me-1"></i>
                        Bulk Timezone Sync
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="fas fa-wrench"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Maintenance Mode</h6>
                            <h4 class="mb-0 text-warning" id="maintenance-count">{{ $rvms->where('status', 'maintenance')->count() }}</h4>
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
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Error Status</h6>
                            <h4 class="mb-0 text-danger" id="error-count">{{ $rvms->where('status', 'error')->count() }}</h4>
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
                            <h6 class="mb-0">Timezone Issues</h6>
                            <h4 class="mb-0 text-info" id="timezone-issues">{{ $timezoneIssuesCount }}</h4>
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
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="fas fa-wifi"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Connection Issues</h6>
                            <h4 class="mb-0 text-secondary" id="connection-issues">{{ $connectionIssuesCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Tools -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent p-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-tools me-2"></i>
                        Maintenance Tools
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="pingAllRVMs()">
                            <i class="fas fa-wifi me-2"></i>
                            Ping All RVMs
                        </button>
                        <button class="btn btn-outline-info" onclick="checkAllTimezones()">
                            <i class="fas fa-clock me-2"></i>
                            Check All Timezones
                        </button>
                        <button class="btn btn-outline-warning" onclick="restartAllServices()">
                            <i class="fas fa-redo me-2"></i>
                            Restart All Services
                        </button>
                        <button class="btn btn-outline-success" onclick="updateAllFirmware()">
                            <i class="fas fa-download me-2"></i>
                            Update All Firmware
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent p-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-clock me-2"></i>
                        Timezone Management
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-info" onclick="syncAllTimezones()">
                            <i class="fas fa-sync-alt me-2"></i>
                            Sync All Timezones
                        </button>
                        <button class="btn btn-outline-warning" onclick="fixTimezoneDrift()">
                            <i class="fas fa-adjust me-2"></i>
                            Fix Timezone Drift
                        </button>
                        <button class="btn btn-outline-primary" onclick="setGlobalTimezone()">
                            <i class="fas fa-globe me-2"></i>
                            Set Global Timezone
                        </button>
                        <button class="btn btn-outline-secondary" onclick="viewTimezoneLogs()">
                            <i class="fas fa-list me-2"></i>
                            View Timezone Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RVM Maintenance List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">RVM Maintenance Status</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="maintenanceFilter" onchange="filterMaintenanceRVMs()">
                                <option value="">All RVMs</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="error">Error</option>
                                <option value="timezone_issue">Timezone Issues</option>
                                <option value="connection_issue">Connection Issues</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>RVM</th>
                                    <th>Status</th>
                                    <th>Connection</th>
                                    <th>Timezone</th>
                                    <th>Last Sync</th>
                                    <th>Issues</th>
                                    <th>Maintenance Actions</th>
                                </tr>
                            </thead>
                            <tbody id="maintenanceTableBody">
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
                                                <small class="text-muted">{{ $rvm->location }}</small>
                                            </div>
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
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-secondary me-2" id="maintenance-connection-{{ $rvm->id }}">
                                                <i class="fas fa-question-circle me-1"></i>
                                                Unknown
                                            </span>
                                            <button class="btn btn-sm btn-outline-primary" onclick="pingRVM({{ $rvm->id }})">
                                                <i class="fas fa-wifi"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0" id="maintenance-timezone-{{ $rvm->id }}">
                                                {{ $rvm->timezone ?? 'Not Set' }}
                                            </h6>
                                            <small class="text-muted" id="maintenance-timezone-offset-{{ $rvm->id }}">
                                                {{ $rvm->timezone_offset ?? 'Unknown' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0" id="maintenance-last-sync-{{ $rvm->id }}">
                                                {{ $rvm->last_timezone_sync ? \Carbon\Carbon::parse($rvm->last_timezone_sync)->diffForHumans() : 'Never' }}
                                            </h6>
                                            <small class="text-muted" id="maintenance-sync-time-{{ $rvm->id }}">
                                                {{ $rvm->last_timezone_sync ? \Carbon\Carbon::parse($rvm->last_timezone_sync)->format('M d, Y H:i') : 'No sync' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="issues-{{ $rvm->id }}">
                                            @if($rvm->status === 'error')
                                                <span class="badge bg-danger me-1">System Error</span>
                                            @endif
                                            @if($rvm->status === 'maintenance')
                                                <span class="badge bg-warning me-1">Maintenance</span>
                                            @endif
                                            @if(!$rvm->timezone)
                                                <span class="badge bg-info me-1">No Timezone</span>
                                            @endif
                                            @if(!$rvm->ip_address)
                                                <span class="badge bg-secondary me-1">No IP</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-primary" onclick="syncTimezone({{ $rvm->id }})" title="Sync Timezone">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="restartRVM({{ $rvm->id }})" title="Restart RVM">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="updateFirmware({{ $rvm->id }})" title="Update Firmware">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="setMaintenanceMode({{ $rvm->id }}, false)" title="Exit Maintenance">
                                                <i class="fas fa-check"></i>
                                            </button>
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
</div>

<!-- Timezone Sync Modal -->
<div class="modal fade" id="timezoneSyncModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock me-2"></i>
                    Timezone Synchronization
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="timezoneSyncProgress">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Initializing timezone synchronization...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Global Timezone Modal -->
<div class="modal fade" id="globalTimezoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-globe me-2"></i>
                    Set Global Timezone
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="globalTimezoneForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Timezone</label>
                        <select class="form-select" name="timezone" required>
                            <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                            <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                            <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                            <option value="UTC">UTC</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will update the timezone for ALL RVMs. Make sure this is correct for your deployment.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Apply to All RVMs
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
        refreshMaintenanceData();
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
        updateMaintenanceConnectionStatus(rvmId, 'error', 'No IP Address');
        return;
    }

    const connectionElement = document.getElementById(`maintenance-connection-${rvmId}`);
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
            updateMaintenanceConnectionStatus(rvmId, 'success', 'Connected');
        } else {
            updateMaintenanceConnectionStatus(rvmId, 'danger', 'Disconnected');
        }
    })
    .catch(error => {
        updateMaintenanceConnectionStatus(rvmId, 'danger', 'Error');
    });
}

// Update connection status
function updateMaintenanceConnectionStatus(rvmId, status, text) {
    const connectionElement = document.getElementById(`maintenance-connection-${rvmId}`);
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
                refreshMaintenanceData();
            } else {
                alert('Timezone sync failed: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

// Bulk timezone sync
function bulkTimezoneSync() {
    if (confirm('Sync timezone for all RVMs? This may take a few minutes.')) {
        const modal = new bootstrap.Modal(document.getElementById('timezoneSyncModal'));
        modal.show();
        
        // Simulate bulk sync
        setTimeout(() => {
            alert('Bulk timezone sync completed');
            modal.hide();
            refreshMaintenanceData();
        }, 3000);
    }
}

// Sync all timezones
function syncAllTimezones() {
    bulkTimezoneSync();
}

// Check all timezones
function checkAllTimezones() {
    alert('Checking all timezones...');
    // Implementation for checking all timezones
}

// Fix timezone drift
function fixTimezoneDrift() {
    if (confirm('Fix timezone drift for all RVMs?')) {
        alert('Timezone drift fix initiated');
        // Implementation for fixing timezone drift
    }
}

// Set global timezone
function setGlobalTimezone() {
    const modal = new bootstrap.Modal(document.getElementById('globalTimezoneModal'));
    modal.show();
}

// View timezone logs
function viewTimezoneLogs() {
    window.open('/admin/timezone', '_blank');
}

// Restart all services
function restartAllServices() {
    if (confirm('Restart all RVM services? This will temporarily disconnect all RVMs.')) {
        alert('Restarting all services...');
        // Implementation for restarting all services
    }
}

// Update all firmware
function updateAllFirmware() {
    if (confirm('Update firmware for all RVMs? This may take several minutes.')) {
        alert('Firmware update initiated for all RVMs');
        // Implementation for updating all firmware
    }
}

// Restart specific RVM
function restartRVM(rvmId) {
    const rvm = rvmData.find(r => r.id === rvmId);
    if (!rvm) return;

    if (confirm(`Restart ${rvm.name}?`)) {
        alert(`Restarting ${rvm.name}...`);
        // Implementation for restarting specific RVM
    }
}

// Update firmware for specific RVM
function updateFirmware(rvmId) {
    const rvm = rvmData.find(r => r.id === rvmId);
    if (!rvm) return;

    if (confirm(`Update firmware for ${rvm.name}?`)) {
        alert(`Updating firmware for ${rvm.name}...`);
        // Implementation for updating firmware
    }
}

// Set maintenance mode
function setMaintenanceMode(rvmId, isMaintenance) {
    const rvm = rvmData.find(r => r.id === rvmId);
    if (!rvm) return;

    const action = isMaintenance ? 'enter' : 'exit';
    if (confirm(`${action.charAt(0).toUpperCase() + action.slice(1)} maintenance mode for ${rvm.name}?`)) {
        alert(`${action.charAt(0).toUpperCase() + action.slice(1)}ing maintenance mode for ${rvm.name}...`);
        // Implementation for setting maintenance mode
    }
}

// Refresh maintenance data
function refreshMaintenanceData() {
    location.reload();
}

// Filter maintenance RVMs
function filterMaintenanceRVMs() {
    const filter = document.getElementById('maintenanceFilter').value;
    const rows = document.querySelectorAll('#maintenanceTableBody tr');
    
    rows.forEach(row => {
        if (!filter) {
            row.style.display = '';
        } else {
            const rvmId = row.dataset.rvmId;
            const rvm = rvmData.find(r => r.id == rvmId);
            
            let show = false;
            switch(filter) {
                case 'maintenance':
                    show = rvm.status === 'maintenance';
                    break;
                case 'error':
                    show = rvm.status === 'error';
                    break;
                case 'timezone_issue':
                    show = !rvm.timezone;
                    break;
                case 'connection_issue':
                    show = !rvm.ip_address;
                    break;
            }
            
            row.style.display = show ? '' : 'none';
        }
    });
}

// Global timezone form submission
document.getElementById('globalTimezoneForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const timezone = formData.get('timezone');
    
    if (confirm(`Set timezone to ${timezone} for ALL RVMs?`)) {
        fetch('/admin/rvm/set-global-timezone', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ timezone: timezone })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Global timezone updated successfully');
                bootstrap.Modal.getInstance(document.getElementById('globalTimezoneModal')).hide();
                refreshMaintenanceData();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
});
</script>
@endsection
