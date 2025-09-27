// Engine Configuration Form Functionality

class EngineConfigManager {
    constructor() {
        this.engines = [];
        this.selectedEngine = null;
        
        this.initializeElements();
        this.setupEventListeners();
        this.loadEngines();
    }

    initializeElements() {
        this.enginesContainer = document.getElementById('enginesContainer');
        this.addEngineButton = document.getElementById('addEngineButton');
    }

    setupEventListeners() {
        this.addEngineButton?.addEventListener('click', () => this.showAddEngineModal());
    }

    async loadEngines() {
        try {
            showLoading('Loading processing engines...');
            
            const response = await fetch('/api/v2/processing-engines');
            const engines = await response.json();
            
            this.engines = engines;
            this.renderEngines();
            
        } catch (error) {
            console.error('Error loading engines:', error);
            showError('Failed to load processing engines');
        } finally {
            hideLoading();
        }
    }

    renderEngines() {
        if (!this.enginesContainer) return;
        
        this.enginesContainer.innerHTML = this.engines.map(engine => this.createEngineCard(engine)).join('');
    }

    createEngineCard(engine) {
        const statusClass = engine.is_online ? 'online' : 'offline';
        const statusText = engine.is_online ? 'Online' : 'Offline';
        
        return `
            <div class="engine-config-card" data-engine-id="${engine.id}">
                <div class="engine-header">
                    <h5 class="engine-title">${engine.name}</h5>
                    <span class="engine-status ${statusClass}">
                        <i class="fas fa-circle me-1"></i>${statusText}
                    </span>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="config-section">
                            <h6>Basic Configuration</h6>
                            <div class="mb-2">
                                <strong>Type:</strong> 
                                <span class="badge bg-${engine.type === 'nvidia_cuda' ? 'primary' : 'success'}">
                                    ${engine.type === 'nvidia_cuda' ? 'NVIDIA CUDA' : 'Jetson Edge'}
                                </span>
                            </div>
                            <div class="mb-2">
                                <strong>Server:</strong> ${engine.server_address}:${engine.port}
                            </div>
                            <div class="mb-2">
                                <strong>GPU Memory:</strong> ${engine.gpu_memory_limit || 'N/A'}
                            </div>
                            <div class="mb-2">
                                <strong>Timeout:</strong> ${engine.processing_timeout}s
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="config-section">
                            <h6>Health Metrics</h6>
                            <div class="health-metrics">
                                <div class="metric-card">
                                    <div class="metric-value">${engine.ping_response_time || 'N/A'}</div>
                                    <div class="metric-label">Ping (ms)</div>
                                </div>
                                <div class="metric-card">
                                    <div class="metric-value">${engine.is_active ? 'Yes' : 'No'}</div>
                                    <div class="metric-label">Active</div>
                                </div>
                                <div class="metric-card">
                                    <div class="metric-value">${engine.auto_failover ? 'Yes' : 'No'}</div>
                                    <div class="metric-label">Failover</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="config-section">
                    <h6>Actions</h6>
                    <div class="action-buttons">
                        <button class="btn btn-outline-primary btn-action" onclick="engineConfigManager.editEngine(${engine.id})">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                        <button class="btn btn-outline-success btn-action" onclick="engineConfigManager.pingEngine(${engine.id})">
                            <i class="fas fa-satellite-dish me-1"></i>Ping
                        </button>
                        <button class="btn btn-outline-${engine.is_active ? 'warning' : 'success'} btn-action" onclick="engineConfigManager.toggleEngine(${engine.id})">
                            <i class="fas fa-${engine.is_active ? 'pause' : 'play'} me-1"></i>
                            ${engine.is_active ? 'Deactivate' : 'Activate'}
                        </button>
                        <button class="btn btn-outline-danger btn-action" onclick="engineConfigManager.deleteEngine(${engine.id})">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    async pingEngine(engineId) {
        try {
            showLoading('Pinging engine...');
            
            const response = await fetch(`/api/v2/processing-engines/${engineId}/ping`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccess(`Engine pinged successfully. Response time: ${result.response_time}ms`);
                this.loadEngines(); // Refresh to show updated status
            } else {
                showError('Engine ping failed');
            }
            
        } catch (error) {
            console.error('Error pinging engine:', error);
            showError('Failed to ping engine');
        } finally {
            hideLoading();
        }
    }

    async toggleEngine(engineId) {
        try {
            const engine = this.engines.find(e => e.id === engineId);
            const newStatus = !engine.is_active;
            
            showLoading(`${newStatus ? 'Activating' : 'Deactivating'} engine...`);
            
            const response = await fetch(`/api/v2/processing-engines/${engineId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ is_active: newStatus })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccess(`Engine ${newStatus ? 'activated' : 'deactivated'} successfully`);
                this.loadEngines(); // Refresh to show updated status
            } else {
                showError(`Failed to ${newStatus ? 'activate' : 'deactivate'} engine`);
            }
            
        } catch (error) {
            console.error('Error toggling engine:', error);
            showError('Failed to toggle engine status');
        } finally {
            hideLoading();
        }
    }

    async deleteEngine(engineId) {
        if (!confirm('Are you sure you want to delete this engine? This action cannot be undone.')) {
            return;
        }
        
        try {
            showLoading('Deleting engine...');
            
            const response = await fetch(`/api/v2/processing-engines/${engineId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccess('Engine deleted successfully');
                this.loadEngines(); // Refresh to remove deleted engine
            } else {
                showError('Failed to delete engine');
            }
            
        } catch (error) {
            console.error('Error deleting engine:', error);
            showError('Failed to delete engine');
        } finally {
            hideLoading();
        }
    }

    editEngine(engineId) {
        // In a real implementation, this would open an edit modal
        showInfo('Edit functionality will be implemented in the next version');
    }

    showAddEngineModal() {
        // In a real implementation, this would open an add engine modal
        showInfo('Add engine functionality will be implemented in the next version');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.engineConfigManager = new EngineConfigManager();
});
