@extends('components.admin-layout')

@section('title', 'Edge Vision Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti tabler-robot me-2"></i>
                        Edge Vision Dashboard
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" onclick="refreshStatistics()">
                            <i class="ti tabler-refresh me-1"></i>
                            Refresh
                        </button>
                        <button class="btn btn-success" onclick="triggerGlobalProcessing()">
                            <i class="ti tabler-play me-1"></i>
                            Start Processing
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        Monitor and control YOLO11 + SAM2 computer vision processing across all RVM devices.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Total RVMs</p>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2" id="total-rvms">-</h4>
                                <small class="text-success fw-semibold">
                                    <i class="ti tabler-trending-up me-1"></i>
                                    <span id="total-rvms-change">0%</span>
                                </small>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="ti tabler-device-desktop ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Active RVMs</p>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2" id="active-rvms">-</h4>
                                <small class="text-success fw-semibold">
                                    <i class="ti tabler-trending-up me-1"></i>
                                    <span id="active-rvms-change">0%</span>
                                </small>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-2">
                                <i class="ti tabler-check ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">CV Processing Today</p>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2" id="cv-processing-today">-</h4>
                                <small class="text-success fw-semibold">
                                    <i class="ti tabler-trending-up me-1"></i>
                                    <span id="cv-processing-change">0%</span>
                                </small>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded p-2">
                                <i class="ti tabler-brain ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Success Rate</p>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2" id="cv-success-rate">-</h4>
                                <small class="text-success fw-semibold">
                                    <i class="ti tabler-trending-up me-1"></i>
                                    <span id="cv-success-change">0%</span>
                                </small>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="ti tabler-target ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RVM Status Grid -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti tabler-device-desktop me-2"></i>
                        RVM Edge Vision Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row" id="rvm-status-grid">
                        <!-- RVM status cards will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Model Status -->
    <div class="row mb-4">
        <div class="col-lg-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti tabler-brain me-2"></i>
                        YOLO11 Model Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">YOLO11 Nano</h6>
                            <small class="text-muted">Object Detection Model</small>
                        </div>
                        <span class="badge bg-label-success">Loaded</span>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Version</small>
                            <p class="mb-0 fw-semibold">11.0.0</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Size</small>
                            <p class="mb-0 fw-semibold">6.2MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti tabler-scissors me-2"></i>
                        SAM2 Model Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">SAM2 Base</h6>
                            <small class="text-muted">Segmentation Model</small>
                        </div>
                        <span class="badge bg-label-success">Loaded</span>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Version</small>
                            <p class="mb-0 fw-semibold">2.1.0</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Size</small>
                            <p class="mb-0 fw-semibold">375MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Processing History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti tabler-history me-2"></i>
                        Recent Processing History
                    </h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="history-rvm-select" style="width: auto;">
                            <option value="">All RVMs</option>
                        </select>
                        <button class="btn btn-outline-primary btn-sm" onclick="loadProcessingHistory()">
                            <i class="ti tabler-refresh me-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>RVM</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Started</th>
                                    <th>Duration</th>
                                    <th>Objects</th>
                                    <th>Confidence</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="processing-history-table">
                                <!-- Processing history will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CV Processing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Select RVM</label>
                        <select class="form-select" id="processing-rvm-select">
                            <option value="">Choose RVM...</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Processing Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="processing-type" id="type-yolo" value="yolo" checked>
                            <label class="form-check-label" for="type-yolo">
                                YOLO11 Only
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="processing-type" id="type-sam2" value="sam2">
                            <label class="form-check-label" for="type-sam2">
                                SAM2 Only
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="processing-type" id="type-both" value="both">
                            <label class="form-check-label" for="type-both">
                                Both (YOLO11 + SAM2)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="startProcessing()">Start Processing</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let statisticsData = {};
let rvmData = [];

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadRvmStatus();
    loadProcessingHistory();
    
    // Auto refresh every 30 seconds
    setInterval(() => {
        loadStatistics();
        loadRvmStatus();
    }, 30000);
});

// Load statistics
async function loadStatistics() {
    try {
        const response = await fetch('/admin/edge-vision/statistics');
        const result = await response.json();
        
        if (result.success) {
            statisticsData = result.data;
            updateStatisticsDisplay();
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

// Update statistics display
function updateStatisticsDisplay() {
    document.getElementById('total-rvms').textContent = statisticsData.total_rvms || 0;
    document.getElementById('active-rvms').textContent = statisticsData.active_rvms || 0;
    document.getElementById('cv-processing-today').textContent = statisticsData.cv_processing_today || 0;
    document.getElementById('cv-success-rate').textContent = (statisticsData.cv_success_rate || 0) + '%';
}

// Load RVM status
async function loadRvmStatus() {
    try {
        const response = await fetch('/admin/edge-vision/rvm-status');
        const result = await response.json();
        
        if (result.success) {
            rvmData = result.data;
            updateRvmStatusGrid();
        }
    } catch (error) {
        console.error('Error loading RVM status:', error);
    }
}

// Update RVM status grid
function updateRvmStatusGrid() {
    const grid = document.getElementById('rvm-status-grid');
    grid.innerHTML = '';
    
    rvmData.forEach(rvm => {
        const card = createRvmStatusCard(rvm);
        grid.appendChild(card);
    });
}

// Create RVM status card
function createRvmStatusCard(rvm) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6 col-12 mb-3';
    
    const statusClass = rvm.cv_enabled ? 'success' : 'secondary';
    const statusText = rvm.cv_enabled ? 'Active' : 'Inactive';
    
    col.innerHTML = `
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="card-title mb-1">${rvm.rvm_name}</h6>
                        <small class="text-muted">RVM-${rvm.rvm_id.toString().padStart(3, '0')}</small>
                    </div>
                    <span class="badge bg-label-${statusClass}">${statusText}</span>
                </div>
                
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted">Processing Count</small>
                        <p class="mb-0 fw-semibold">${rvm.cv_processing_count || 0}</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Success Rate</small>
                        <p class="mb-0 fw-semibold">${(rvm.cv_success_rate || 0).toFixed(1)}%</p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Pending Uploads</small>
                        <p class="mb-0 fw-semibold">${rvm.pending_uploads || 0}</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Last Processing</small>
                        <p class="mb-0 fw-semibold">${formatTimeAgo(rvm.last_cv_processing)}</p>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="triggerRvmProcessing(${rvm.rvm_id})">
                        <i class="ti tabler-play me-1"></i>
                        Process
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="viewRvmHistory(${rvm.rvm_id})">
                        <i class="ti tabler-history me-1"></i>
                        History
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

// Load processing history
async function loadProcessingHistory() {
    try {
        const rvmId = document.getElementById('history-rvm-select').value;
        const response = await fetch(`/admin/edge-vision/processing-history?rvm_id=${rvmId}&limit=10`);
        const result = await response.json();
        
        if (result.success) {
            updateProcessingHistoryTable(result.data);
        }
    } catch (error) {
        console.error('Error loading processing history:', error);
    }
}

// Update processing history table
function updateProcessingHistoryTable(history) {
    const tbody = document.getElementById('processing-history-table');
    tbody.innerHTML = '';
    
    history.forEach(item => {
        const row = document.createElement('tr');
        
        const statusClass = item.status === 'completed' ? 'success' : 
                           item.status === 'processing' ? 'warning' : 'danger';
        
        row.innerHTML = `
            <td>RVM-${item.rvm_id.toString().padStart(3, '0')}</td>
            <td>
                <span class="badge bg-label-${item.type === 'yolo' ? 'primary' : item.type === 'sam2' ? 'info' : 'secondary'}">
                    ${item.type.toUpperCase()}
                </span>
            </td>
            <td>
                <span class="badge bg-label-${statusClass}">
                    ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                </span>
            </td>
            <td>${formatDateTime(item.started_at)}</td>
            <td>${item.processing_time}s</td>
            <td>${item.objects_detected}</td>
            <td>${item.confidence.toFixed(1)}%</td>
            <td>
                <button class="btn btn-outline-primary btn-sm" onclick="viewProcessingDetails('${item.id}')">
                    <i class="ti tabler-eye me-1"></i>
                    View
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Trigger RVM processing
async function triggerRvmProcessing(rvmId) {
    try {
        const response = await fetch('/admin/edge-vision/trigger-processing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                rvm_id: rvmId,
                type: 'yolo'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', 'CV processing triggered successfully');
            loadRvmStatus();
        } else {
            showAlert('error', result.message);
        }
    } catch (error) {
        console.error('Error triggering processing:', error);
        showAlert('error', 'Failed to trigger processing');
    }
}

// Trigger global processing
function triggerGlobalProcessing() {
    const modal = new bootstrap.Modal(document.getElementById('processingModal'));
    modal.show();
}

// Start processing from modal
async function startProcessing() {
    const rvmId = document.getElementById('processing-rvm-select').value;
    const type = document.querySelector('input[name="processing-type"]:checked').value;
    
    if (!rvmId) {
        showAlert('error', 'Please select an RVM');
        return;
    }
    
    try {
        const response = await fetch('/admin/edge-vision/trigger-processing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                rvm_id: rvmId,
                type: type
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', 'CV processing started successfully');
            bootstrap.Modal.getInstance(document.getElementById('processingModal')).hide();
            loadRvmStatus();
        } else {
            showAlert('error', result.message);
        }
    } catch (error) {
        console.error('Error starting processing:', error);
        showAlert('error', 'Failed to start processing');
    }
}

// Refresh statistics
function refreshStatistics() {
    loadStatistics();
    loadRvmStatus();
    loadProcessingHistory();
    showAlert('info', 'Data refreshed');
}

// Utility functions
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString();
}

function formatTimeAgo(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
    return `${Math.floor(diffMins / 1440)}d ago`;
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

// View RVM history
function viewRvmHistory(rvmId) {
    document.getElementById('history-rvm-select').value = rvmId;
    loadProcessingHistory();
}

// View processing details
function viewProcessingDetails(processingId) {
    showAlert('info', `Viewing details for processing: ${processingId}`);
}
</script>
@endsection
