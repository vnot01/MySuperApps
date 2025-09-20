// Remote Access JavaScript Functions
// File: public/js/admin/dashboard/remote-access.js

function startRemoteAccess(rvmId) {
    const adminId = getCurrentAdminId(); // Get current admin ID
    
    if (!adminId) {
        alert('❌ Admin ID not found. Please login again.');
        return;
    }
    
    // Show confirmation dialog
    if (!confirm(`Start remote access for RVM ${rvmId}?\n\nThis will change RVM status to "maintenance".`)) {
        return;
    }
    
    // Show loading state
    const button = document.querySelector(`[data-rvm-id="${rvmId}"] .remote-access-btn`);
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
            ip_address: getClientIP(), // Get client IP
            port: 5001
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Remote access started successfully!\n\nSession ID: ${data.data.session_id}\nRVM Status: ${data.data.status}`);
            
            // Update UI
            updateRVMStatus(rvmId, 'maintenance');
            updateRemoteAccessButton(rvmId, 'stop');
            
            // Refresh page after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
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

function stopRemoteAccess(rvmId) {
    const adminId = getCurrentAdminId();
    
    if (!adminId) {
        alert('❌ Admin ID not found. Please login again.');
        return;
    }
    
    // Show confirmation dialog
    if (!confirm(`Stop remote access for RVM ${rvmId}?\n\nThis will change RVM status back to "active".`)) {
        return;
    }
    
    // Show loading state
    const button = document.querySelector(`[data-rvm-id="${rvmId}"] .remote-access-btn`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Stopping...';
    button.disabled = true;
    
    fetch(`/admin/rvm/${rvmId}/remote-access/stop`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            admin_id: adminId,
            reason: 'Manual stop from dashboard'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Remote access stopped successfully!\n\nDuration: ${data.data.duration} seconds\nRVM Status: ${data.data.status}`);
            
            // Update UI
            updateRVMStatus(rvmId, 'active');
            updateRemoteAccessButton(rvmId, 'start');
            
            // Refresh page after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            const errorMessage = data.message || 'Unknown error occurred';
            alert(`❌ Failed to stop remote access:\n${errorMessage}`);
        }
    })
    .catch(error => {
        console.error('Remote access stop error:', error);
        const errorMessage = error.message || error.toString() || 'Unknown network error';
        alert('❌ Network error: ' + errorMessage);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function getRemoteAccessStatus(rvmId) {
    fetch(`/admin/rvm/${rvmId}/remote-access/status`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const status = data.data;
                
                if (status.active_session) {
                    // Show active session info
                    showRemoteAccessModal(status);
                } else {
                    // No active session
                    console.log('No active remote access session');
                }
            }
        })
        .catch(error => {
            console.error('Remote access status error:', error);
        });
}

function updateRemoteAccessButton(rvmId, action) {
    const button = document.querySelector(`[data-rvm-id="${rvmId}"] .remote-access-btn`);
    
    if (action === 'start') {
        button.innerHTML = '<i class="fas fa-desktop"></i> Remote Access';
        button.onclick = () => startRemoteAccess(rvmId);
        button.className = 'btn btn-outline-primary btn-sm remote-access-btn';
    } else if (action === 'stop') {
        button.innerHTML = '<i class="fas fa-stop"></i> Stop Access';
        button.onclick = () => stopRemoteAccess(rvmId);
        button.className = 'btn btn-outline-danger btn-sm remote-access-btn';
    }
}

function getCurrentAdminId() {
    // Get admin ID from meta tag or user data
    const adminIdMeta = document.querySelector('meta[name="admin-id"]');
    return adminIdMeta ? adminIdMeta.getAttribute('content') : null;
}

function getClientIP() {
    // Get client IP (this would need to be implemented based on your setup)
    return '192.168.1.100'; // Placeholder
}

function updateRVMStatus(rvmId, status) {
    // Update RVM status in UI
    const statusElement = document.querySelector(`[data-rvm-id="${rvmId}"] .rvm-status`);
    if (statusElement) {
        const badgeClass = status === 'active' ? 'bg-success' : (status === 'maintenance' ? 'bg-warning' : 'bg-danger');
        statusElement.className = `badge ${badgeClass}`;
        statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    }
}

function showRemoteAccessModal(status) {
    // Show modal with remote access status
    const modal = new bootstrap.Modal(document.getElementById('remoteAccessStatusModal'));
    const content = document.getElementById('remoteAccessStatusContent');
    
    const session = status.active_session;
    const duration = formatDuration(session.duration);
    
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Active Session</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Session ID:</strong></td>
                        <td>${session.session_id}</td>
                    </tr>
                    <tr>
                        <td><strong>Admin:</strong></td>
                        <td>${session.admin_name}</td>
                    </tr>
                    <tr>
                        <td><strong>Start Time:</strong></td>
                        <td>${formatDateTime(session.start_time)}</td>
                    </tr>
                    <tr>
                        <td><strong>Duration:</strong></td>
                        <td>${duration}</td>
                    </tr>
                    <tr>
                        <td><strong>IP Address:</strong></td>
                        <td>${session.ip_address || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Port:</strong></td>
                        <td>${session.port || 'N/A'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>RVM Status</h6>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Maintenance Mode:</strong> RVM is currently in maintenance mode due to active remote access session.
                </div>
            </div>
        </div>
    `;
    
    modal.show();
}

// Helper functions
function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    if (hours > 0) {
        return `${hours}h ${minutes}m ${secs}s`;
    } else if (minutes > 0) {
        return `${minutes}m ${secs}s`;
    } else {
        return `${secs}s`;
    }
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

// ===== REMOTE ACCESS MODAL FUNCTIONS =====

function showRemoteAccessModal(rvmId) {
    const rvm = rvmData.find(r => r.id === rvmId);
    if (!rvm) {
        alert('❌ RVM not found');
        return;
    }
    
    // Populate modal content
    const content = document.getElementById('remoteAccessContent');
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">RVM Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>RVM ID:</strong></td>
                                <td>${rvm.id}</td>
                            </tr>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>${rvm.name}</td>
                            </tr>
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td>${rvm.location || 'Not Set'}</td>
                            </tr>
                            <tr>
                                <td><strong>IP Address:</strong></td>
                                <td>${rvm.ip_address || 'Not Set'}</td>
                            </tr>
                            <tr>
                                <td><strong>Port:</strong></td>
                                <td>${rvm.port || 8000}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge bg-${getStatusClass(rvm.status)}">${rvm.status}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Remote Access Options</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Access Type</label>
                            <select class="form-select" id="accessType">
                                <option value="camera">Camera Access (Port 5000)</option>
                                <option value="gui">GUI Access (Port 5001)</option>
                                <option value="both">Both Camera & GUI</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Session Duration (minutes)</label>
                            <select class="form-select" id="sessionDuration">
                                <option value="30">30 minutes</option>
                                <option value="60" selected>1 hour</option>
                                <option value="120">2 hours</option>
                                <option value="240">4 hours</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Access</label>
                            <textarea class="form-control" id="accessReason" rows="3" placeholder="Enter reason for remote access..."></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Starting remote access will change RVM status to "maintenance" mode.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Update modal title and button
    document.getElementById('remoteAccessModalLabel').innerHTML = `<i class="fas fa-desktop me-2"></i>Remote Access - ${rvm.name}`;
    document.getElementById('remoteAccessActionBtn').innerHTML = '<i class="fas fa-desktop me-1"></i> Start Remote Access';
    document.getElementById('remoteAccessActionBtn').onclick = () => startRemoteAccessFromModal(rvmId);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('remoteAccessModal'));
    modal.show();
}

function startRemoteAccessFromModal(rvmId) {
    const accessType = document.getElementById('accessType').value;
    const sessionDuration = document.getElementById('sessionDuration').value;
    const accessReason = document.getElementById('accessReason').value;
    const adminId = getCurrentAdminId();
    
    if (!adminId) {
        alert('❌ Admin ID not found. Please login again.');
        return;
    }
    
    if (!accessReason.trim()) {
        alert('❌ Please enter a reason for remote access.');
        return;
    }
    
    // Show loading state
    const button = document.getElementById('remoteAccessActionBtn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Starting...';
    button.disabled = true;
    
    // Determine port based on access type
    let port = 5001; // Default to GUI access
    if (accessType === 'camera') {
        port = 5000;
    } else if (accessType === 'both') {
        port = 5001; // GUI access for both
    }
    
    fetch(`/admin/rvm/${rvmId}/remote-access/start`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            admin_id: adminId,
            ip_address: getClientIP(),
            port: port,
            access_type: accessType,
            session_duration: parseInt(sessionDuration),
            reason: accessReason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const session = data.data;
            alert(`✅ Remote access started successfully!\n\nSession ID: ${session.session_id}\nAccess Type: ${accessType}\nDuration: ${sessionDuration} minutes\nRVM Status: ${session.status}`);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('remoteAccessModal'));
            modal.hide();
            
            // Update UI
            updateRVMStatus(rvmId, 'maintenance');
            updateRemoteAccessStatus(rvmId, 'active', session.session_id);
            
            // Refresh page after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
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

function updateRemoteAccessStatus(rvmId, status, sessionId = null) {
    const statusElement = document.querySelector(`[data-rvm-id="${rvmId}"] .remote-access-status`);
    
    if (!statusElement) {
        console.error(`Status element not found for RVM ${rvmId}`);
        return;
    }
    
    if (status === 'active') {
        statusElement.innerHTML = `
            <span class="badge bg-success">
                <i class="fas fa-circle"></i> Active
            </span>
            <small class="d-block text-muted">Session: ${sessionId}</small>
        `;
    } else {
        statusElement.innerHTML = `
            <span class="badge bg-secondary">
                <i class="fas fa-circle"></i> Inactive
            </span>
        `;
    }
}

function getStatusClass(status) {
    const statusClasses = {
        'active': 'success',
        'inactive': 'secondary',
        'maintenance': 'warning',
        'error': 'danger',
        'full': 'info'
    };
    return statusClasses[status] || 'secondary';
}

// ===== REMOTE ACCESS STATUS MODAL =====

function showRemoteAccessStatusModal(rvmId) {
    fetch(`/admin/rvm/${rvmId}/remote-access/status`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const status = data.data;
                const content = document.getElementById('remoteAccessStatusContent');
                
                if (status.active_session) {
                    // Show active session
                    const session = status.active_session;
                    const duration = formatDuration(session.duration);
                    
                    content.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Active Session</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Session ID:</strong></td>
                                                <td>${session.session_id}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Admin:</strong></td>
                                                <td>${session.admin_name || 'Unknown'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Start Time:</strong></td>
                                                <td>${formatDateTime(session.start_time)}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Duration:</strong></td>
                                                <td>${duration}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>IP Address:</strong></td>
                                                <td>${session.ip_address || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Port:</strong></td>
                                                <td>${session.port || 'N/A'}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">RVM Status</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Maintenance Mode:</strong> RVM is currently in maintenance mode due to active remote access session.
                                        </div>
                                        <div class="d-grid">
                                            <button class="btn btn-danger" onclick="stopRemoteAccessFromStatusModal(${rvmId})">
                                                <i class="fas fa-stop me-1"></i> Stop Remote Access
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Show stop button
                    document.getElementById('stopRemoteAccessBtn').style.display = 'block';
                    document.getElementById('stopRemoteAccessBtn').onclick = () => stopRemoteAccessFromStatusModal(rvmId);
                } else {
                    // No active session
                    content.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-desktop fa-3x text-muted mb-3"></i>
                            <h5>No Active Remote Access Session</h5>
                            <p class="text-muted">This RVM is not currently being accessed remotely.</p>
                            <button class="btn btn-primary" onclick="showRemoteAccessModal(${rvmId})">
                                <i class="fas fa-desktop me-1"></i> Start Remote Access
                            </button>
                        </div>
                    `;
                    
                    // Hide stop button
                    document.getElementById('stopRemoteAccessBtn').style.display = 'none';
                }
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('remoteAccessStatusModal'));
                modal.show();
            } else {
                alert('❌ Error loading remote access status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Remote access status error:', error);
            const errorMessage = error.message || error.toString() || 'Unknown network error';
        alert('❌ Network error: ' + errorMessage);
        });
}

function stopRemoteAccessFromStatusModal(rvmId) {
    const adminId = getCurrentAdminId();
    
    if (!adminId) {
        alert('❌ Admin ID not found. Please login again.');
        return;
    }
    
    if (!confirm(`Stop remote access for RVM ${rvmId}?\n\nThis will change RVM status back to "active".`)) {
        return;
    }
    
    // Show loading state
    const button = document.getElementById('stopRemoteAccessBtn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Stopping...';
    button.disabled = true;
    
    fetch(`/admin/rvm/${rvmId}/remote-access/stop`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            admin_id: adminId,
            reason: 'Manual stop from status modal'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Remote access stopped successfully!\n\nDuration: ${data.data.duration} seconds\nRVM Status: ${data.data.status}`);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('remoteAccessStatusModal'));
            modal.hide();
            
            // Update UI
            updateRVMStatus(rvmId, 'active');
            updateRemoteAccessStatus(rvmId, 'inactive');
            
            // Refresh page after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            const errorMessage = data.message || 'Unknown error occurred';
            alert(`❌ Failed to stop remote access:\n${errorMessage}`);
        }
    })
    .catch(error => {
        console.error('Remote access stop error:', error);
        const errorMessage = error.message || error.toString() || 'Unknown network error';
        alert('❌ Network error: ' + errorMessage);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// ===== INITIALIZATION =====

document.addEventListener('DOMContentLoaded', function() {
    // Check for active remote access sessions on page load
    if (typeof rvmData !== 'undefined') {
        rvmData.forEach(rvm => {
            getRemoteAccessStatus(rvm.id);
        });
    }
    
    // Auto-refresh remote access status every 30 seconds
    setInterval(() => {
        if (typeof rvmData !== 'undefined') {
            rvmData.forEach(rvm => {
                getRemoteAccessStatus(rvm.id);
            });
        }
    }, 30000);
});
