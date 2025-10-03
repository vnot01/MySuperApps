class OTAManagementManager {
    constructor(rvmId) {
        this.rvmId = rvmId;
        this.updateProgress = {};
        this.refreshInterval = null;
    }
    
    async init() {
        await this.loadOTAInfo();
        await this.checkForUpdates();
        this.startProgressRefresh();
    }
    
    async loadOTAInfo() {
        try {
            const response = await fetch(`/admin/rvm/${this.rvmId}/ota-info`);
            const data = await response.json();
            
            if (data.success) {
                this.renderOTAInfo(data.data);
            }
        } catch (error) {
            console.error('Failed to load OTA info:', error);
        }
    }
    
    async checkForUpdates() {
        try {
            const response = await fetch(`/admin/rvm/${this.rvmId}/check-updates`);
            const data = await response.json();
            
            if (data.success) {
                this.renderAvailableUpdates(data.data);
            }
        } catch (error) {
            console.error('Failed to check for updates:', error);
        }
    }
    
    renderOTAInfo(otaInfo) {
        const container = document.getElementById('ota-info-container');
        if (!container) return;
        
        let html = `
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Current Software</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Version:</strong> ${otaInfo.current_software?.target_version || 'Unknown'}</p>
                            <p class="mb-0"><strong>Last Updated:</strong> ${this.formatDateTime(otaInfo.current_software?.completed_at)}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Current AI Model</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Model:</strong> ${otaInfo.current_model?.model_name || 'Unknown'}</p>
                            <p class="mb-1"><strong>Version:</strong> ${otaInfo.current_model?.model_version || 'Unknown'}</p>
                            <p class="mb-0"><strong>Deployed:</strong> ${this.formatDateTime(otaInfo.current_model?.deployed_at)}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Recent Updates</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
        `;
        
        otaInfo.recent_updates.slice(0, 3).forEach(update => {
            html += `
                <div class="list-group-item px-0 py-2">
                    <div class="d-flex justify-content-between">
                        <span class="small">${update.update_type} ${update.target_version}</span>
                        <span class="badge badge-${this.getStatusColor(update.status)}">${update.status}</span>
                    </div>
                </div>
            `;
        });
        
        html += `
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    }
    
    renderAvailableUpdates(updates) {
        const container = document.getElementById('available-updates-container');
        if (!container) return;
        
        let html = `
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Software Updates</h6>
                        </div>
                        <div class="card-body">
        `;
        
        if (updates.software_updates && updates.software_updates.length > 0) {
            updates.software_updates.slice(0, 3).forEach(release => {
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>${release.tag_name}</strong><br>
                            <small class="text-muted">${release.published_at}</small>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="startSoftwareUpdate('${release.tag_name}', '${release.html_url}')">
                            Update
                        </button>
                    </div>
                `;
            });
        } else {
            html += '<p class="text-muted mb-0">No software updates available</p>';
        }
        
        html += `
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Model Updates</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0">No model updates available</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Configuration Updates</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0">No configuration updates available</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    }
    
    async startSoftwareUpdate(targetVersion, updateSource) {
        try {
            const response = await fetch(`/admin/rvm/${this.rvmId}/start-software-update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    target_version: targetVersion,
                    update_source: updateSource,
                    update_type: 'software'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Software update started successfully', 'success');
                this.updateProgress[data.data.update_id] = data.data;
                this.renderUpdateProgress();
            } else {
                this.showNotification('Failed to start software update: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Failed to start software update:', error);
            this.showNotification('Network error: ' + error.message, 'error');
        }
    }
    
    renderUpdateProgress() {
        const container = document.getElementById('update-progress-container');
        if (!container) return;
        
        let html = '<h6>Update Progress</h6>';
        
        Object.values(this.updateProgress).forEach(update => {
            html += `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">${update.update_type} ${update.target_version}</h6>
                            <span class="badge badge-${this.getStatusColor(update.status)}">${update.status}</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" style="width: ${update.progress}%">
                                ${update.progress}%
                            </div>
                        </div>
                        <p class="small text-muted mb-0">${update.progress_message || 'Processing...'}</p>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    startProgressRefresh() {
        this.refreshInterval = setInterval(() => {
            this.refreshUpdateProgress();
        }, 2000); // Refresh every 2 seconds
    }
    
    stopProgressRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
    
    async refreshUpdateProgress() {
        for (const updateId in this.updateProgress) {
            try {
                const response = await fetch(`/admin/rvm/${this.rvmId}/update/${updateId}/progress`);
                const data = await response.json();
                
                if (data.success) {
                    this.updateProgress[updateId] = data.data;
                }
            } catch (error) {
                console.error('Failed to refresh update progress:', error);
            }
        }
        
        this.renderUpdateProgress();
    }
    
    getStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'downloading': 'info',
            'installing': 'info',
            'completed': 'success',
            'failed': 'danger',
            'rolled_back': 'secondary'
        };
        return colors[status] || 'secondary';
    }
    
    formatDateTime(dateTime) {
        if (!dateTime) return 'Never';
        return new Date(dateTime).toLocaleString();
    }
    
    showNotification(message, type) {
        // Implement notification system
        console.log(`${type.toUpperCase()}: ${message}`);
        
        // Create a simple notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
}

// Global functions for button clicks
function startSoftwareUpdate(targetVersion, updateSource) {
    if (window.otaManagementManager) {
        window.otaManagementManager.startSoftwareUpdate(targetVersion, updateSource);
    }
}

// Initialize OTA management manager
let otaManagementManager = null;

function initOTAManagement(rvmId) {
    if (otaManagementManager) {
        otaManagementManager.stopProgressRefresh();
    }
    otaManagementManager = new OTAManagementManager(rvmId);
    window.otaManagementManager = otaManagementManager;
    otaManagementManager.init();
}

function stopOTAManagement() {
    if (otaManagementManager) {
        otaManagementManager.stopProgressRefresh();
        otaManagementManager = null;
    }
}
