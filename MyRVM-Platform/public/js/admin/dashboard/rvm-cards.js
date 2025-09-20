// RVM Cards Management

function updateRvmCards(rvms) {
    const container = document.getElementById('rvm-cards-container');
    if (!container) return;
    
    container.innerHTML = '';
    
    // Calculate pagination
    const totalItems = rvms.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
    const currentPageItems = rvms.slice(startIndex, endIndex);
    
    // Update pagination info
    updatePaginationInfo(startIndex + 1, endIndex, totalItems);
    updatePaginationControls(currentPage, totalPages);
    
    // Render current page items
    currentPageItems.forEach((rvm, index) => {
        const card = createRvmCard(rvm);
        card.style.animation = `fadeInUp 0.5s ${index * 0.05}s ease-out forwards`;
        container.appendChild(card);
    });
}

function createRvmCard(rvm) {
    const col = document.createElement('div');
    col.className = 'col-md-6 col-lg-4';
    col.style.opacity = 0; // for animation
    
    const statusInfo = {
        active: { text: 'text-success', icon: 'fas fa-check-circle' },
        inactive: { text: 'text-secondary', icon: 'fas fa-pause-circle' },
        maintenance: { text: 'text-warning', icon: 'fas fa-tools' },
        full: { text: 'text-danger', icon: 'fas fa-exclamation-triangle' },
        error: { text: 'text-danger', icon: 'fas fa-times-circle' },
        unknown: { text: 'text-muted', icon: 'fas fa-question-circle' }
    }[rvm.calculated_status] || { text: 'text-muted', icon: 'fas fa-question-circle' };
    
    col.innerHTML = `
        <div class="card rvm-card ${rvm.calculated_status} border-0 shadow-sm h-100" data-rvm-id="${rvm.id}">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="status-dot ${rvm.calculated_status} me-3"></div>
                        <div>
                            <h6 class="card-title mb-1 fw-bold">${rvm.name}</h6>
                            <small class="text-muted">${rvm.location}</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-text-secondary btn-icon rounded-pill" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="openRemoteAccess(${rvm.id}, '${rvm.name}')"><i class="fas fa-desktop me-2"></i>Remote Access</a></li>
                            <li><a class="dropdown-item" href="#" onclick="openStatusModal(${rvm.id}, '${rvm.name}')"><i class="fas fa-edit me-2"></i>Update Status</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize ${statusInfo.text}"><i class="${statusInfo.icon} me-1"></i>${rvm.calculated_status}</span>
                        <span class="fw-bold">${rvm.capacity}% Full</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar ${rvm.capacity > 80 ? 'bg-danger' : rvm.capacity > 60 ? 'bg-warning' : 'bg-success'}" style="width: ${rvm.capacity}%"></div>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="fas fa-clock me-1"></i>${rvm.last_seen}</small>
                </div>
            </div>
        </div>`;
    return col;
}

// --- Pagination Functions ---

function updatePaginationInfo(start, end, total) {
    const infoElement = document.getElementById('pagination-info');
    if (infoElement) {
        infoElement.textContent = `Showing ${start}-${end} of ${total} RVMs`;
    }
}

function updatePaginationControls(currentPage, totalPages) {
    // Update page buttons
    for (let i = 1; i <= totalPages; i++) {
        const pageElement = document.getElementById(`page-${i}`);
        if (pageElement) {
            if (i === currentPage) {
                pageElement.classList.add('active');
            } else {
                pageElement.classList.remove('active');
            }
        }
    }
    
    // Update prev/next buttons
    const prevElement = document.getElementById('prev-page');
    const nextElement = document.getElementById('next-page');
    
    if (prevElement) {
        if (currentPage === 1) {
            prevElement.classList.add('disabled');
        } else {
            prevElement.classList.remove('disabled');
        }
    }
    
    if (nextElement) {
        if (currentPage === totalPages) {
            nextElement.classList.add('disabled');
        } else {
            nextElement.classList.remove('disabled');
        }
    }
}

function goToPage(page) {
    if (page < 1) return;
    
    const totalItems = monitoringData?.rvms?.length || 0;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    if (page > totalPages) return;
    
    currentPage = page;
    
    // Re-render cards with new page
    if (monitoringData?.rvms) {
        updateRvmCards(monitoringData.rvms);
    }
}

function changePage(direction) {
    const totalItems = monitoringData?.rvms?.length || 0;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const newPage = currentPage + direction;
    
    if (newPage >= 1 && newPage <= totalPages) {
        goToPage(newPage);
    }
}

// --- RVM Action Functions ---

function openRemoteAccess(rvmId, rvmName) {
    // Get RVM data from dashboard data
    const rvm = window.dashboardData.rvms.find(r => r.id === rvmId);
    if (!rvm) {
        alert('❌ RVM not found');
        return;
    }
    
    // Check current remote access status
    fetch(`/admin/rvm/${rvmId}/remote-access/status`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const status = data.data;
                
                if (status.active_session) {
                    // Show active session info
                    showActiveRemoteAccessModal(status);
                } else {
                    // Show start remote access modal
                    showStartRemoteAccessModal(rvm);
                }
            } else {
                const errorMessage = data.message || 'Unknown error occurred';
                alert('❌ Failed to get remote access status: ' + errorMessage);
            }
        })
        .catch(error => {
            console.error('Remote access status error:', error);
            // Don't show alert for network errors, just log them
            if (error.name !== 'TypeError' || !error.message.includes('Load failed')) {
                const errorMessage = error.message || error.toString() || 'Unknown network error';
                alert('❌ Network error: ' + errorMessage);
            } else {
                console.warn('Network connectivity issue - this is normal for unreachable hosts');
            }
        });
}

function showStartRemoteAccessModal(rvm) {
    const modal = new bootstrap.Modal(document.getElementById('remoteAccessModal'));
    const content = document.getElementById('remoteAccessContent');
    const actionBtn = document.getElementById('remoteAccessActionBtn');
    
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>RVM Information</h6>
                <table class="table table-sm">
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
                        <td><strong>Current Status:</strong></td>
                        <td><span class="badge bg-${rvm.calculated_status === 'active' ? 'success' : 'warning'}">${rvm.calculated_status}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Remote Access Information</h6>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Starting remote access will change RVM status to "maintenance" and disable normal operations.
                </div>
                <div class="form-group">
                    <label>Access Type:</label>
                    <div class="input-group">
                        <select class="form-select" id="accessType">
                            <option value="camera">Camera Access (Port 5000)</option>
                            <option value="gui">GUI Access (Port 5001)</option>
                            <option value="both">Both Camera & GUI</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" id="checkPortBtn" onclick="checkPortFromModal(${rvm.id})">
                            <i class="fas fa-search"></i> Check Port
                        </button>
                    </div>
                    <div id="portStatus" class="mt-2" style="display: none;">
                        <!-- Port status will be displayed here -->
                    </div>
                </div>
                <div class="form-group">
                    <label>Session Duration (minutes):</label>
                    <select class="form-select" id="sessionDuration">
                        <option value="30">30 minutes</option>
                        <option value="60" selected>1 hour</option>
                        <option value="120">2 hours</option>
                        <option value="240">4 hours</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reason for Access:</label>
                    <textarea class="form-control" id="accessReason" rows="3" placeholder="Enter reason for remote access..."></textarea>
                </div>
            </div>
        </div>
    `;
    
    actionBtn.innerHTML = '<i class="fas fa-desktop"></i> Start Remote Access';
    actionBtn.className = 'btn btn-primary';
    actionBtn.onclick = () => startRemoteAccessFromModal(rvm.id);
    
    modal.show();
}

function showActiveRemoteAccessModal(status) {
    const modal = new bootstrap.Modal(document.getElementById('remoteAccessStatusModal'));
    const content = document.getElementById('remoteAccessStatusContent');
    const stopBtn = document.getElementById('stopRemoteAccessBtn');
    
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
            <div class="col-md-6">
                <h6>RVM Status</h6>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Maintenance Mode:</strong> RVM is currently in maintenance mode due to active remote access session.
                </div>
                <div class="form-group">
                    <label>Stop Reason:</label>
                    <textarea class="form-control" id="stopReason" rows="3" placeholder="Optional reason for stopping remote access..."></textarea>
                </div>
            </div>
        </div>
    `;
    
    stopBtn.style.display = 'block';
    stopBtn.onclick = () => stopRemoteAccessFromStatusModal(status.rvm_id);
    
    modal.show();
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
            reason: reason || 'Remote access session started from dashboard'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Remote access started successfully!\n\nSession ID: ${data.data.session_id}\nRVM Status: ${data.data.status}`);
            
            // Close modal and refresh dashboard
            bootstrap.Modal.getInstance(document.getElementById('remoteAccessModal')).hide();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert(`❌ Failed to start remote access:\n${data.message}`);
        }
    })
    .catch(error => {
        console.error('Remote access start error:', error);
        alert('❌ Network error: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function stopRemoteAccessFromStatusModal(rvmId) {
    const adminId = getCurrentAdminId();
    
    if (!adminId) {
        alert('❌ Admin ID not found. Please login again.');
        return;
    }
    
    const reason = document.getElementById('stopReason').value;
    
    // Show loading state
    const button = document.getElementById('stopRemoteAccessBtn');
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
            reason: reason || 'Remote access session stopped from dashboard'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Remote access stopped successfully!\n\nDuration: ${data.data.duration} seconds\nRVM Status: ${data.data.status}`);
            
            // Close modal and refresh dashboard
            bootstrap.Modal.getInstance(document.getElementById('remoteAccessStatusModal')).hide();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert(`❌ Failed to stop remote access:\n${data.message}`);
        }
    })
    .catch(error => {
        console.error('Remote access stop error:', error);
        alert('❌ Network error: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
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

function openStatusModal(rvmId, rvmName) {
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
                                <option value="active">
                                    <i class="fas fa-check-circle text-success"></i> Active
                                </option>
                                <option value="inactive">
                                    <i class="fas fa-pause-circle text-secondary"></i> Inactive
                                </option>
                                <option value="maintenance">
                                    <i class="fas fa-tools text-warning"></i> Maintenance
                                </option>
                                <option value="error">
                                    <i class="fas fa-exclamation-triangle text-danger"></i> Error
                                </option>
                                <option value="full">
                                    <i class="fas fa-exclamation-circle text-danger"></i> Full
                                </option>
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
                
                // Refresh the dashboard
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
