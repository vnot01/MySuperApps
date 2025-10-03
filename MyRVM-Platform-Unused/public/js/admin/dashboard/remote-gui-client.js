/**
 * Remote GUI Client - Display LED Jetson Screen Content
 * This script handles the remote GUI client that displays what's shown on the LED Jetson screen
 */

class RemoteGUIClient {
    constructor(rvmId, rvmIp) {
        this.rvmId = rvmId;
        this.rvmIp = rvmIp;
        this.guiUrl = `http://${rvmIp}:5001`;
        this.isConnected = false;
        this.refreshInterval = null;
        this.iframe = null;
    }

    /**
     * Initialize Remote GUI Client
     */
    init() {
        this.createGUIContainer();
        this.startConnection();
        this.setupEventListeners();
    }

    /**
     * Create GUI Container
     */
    createGUIContainer() {
        const container = document.getElementById('remoteGUIContainer');
        if (!container) return;

        container.innerHTML = `
            <div class="remote-gui-wrapper">
                <div class="remote-gui-header">
                    <div class="gui-info">
                        <h5><i class="fas fa-desktop"></i> Remote GUI Client - ${this.rvmId}</h5>
                        <div class="connection-status">
                            <span class="badge bg-secondary" id="guiConnectionStatus">Connecting...</span>
                        </div>
                    </div>
                    <div class="gui-controls">
                        <button class="btn btn-sm btn-outline-primary" id="refreshGUI">
                            <i class="fas fa-refresh"></i> Refresh
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="fullscreenGUI">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="disconnectGUI">
                            <i class="fas fa-times"></i> Disconnect
                        </button>
                    </div>
                </div>
                
                <div class="remote-gui-content">
                    <div class="gui-loading" id="guiLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Connecting to RVM GUI Client...</p>
                    </div>
                    
                    <div class="gui-error" id="guiError" style="display: none;">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Connection Failed</strong>
                            <p>Unable to connect to RVM GUI Client at ${this.guiUrl}</p>
                            <button class="btn btn-sm btn-outline-danger" onclick="remoteGUIClient.retryConnection()">
                                <i class="fas fa-redo"></i> Retry Connection
                            </button>
                        </div>
                    </div>
                    
                    <div class="gui-iframe-container" id="guiIframeContainer" style="display: none;">
                        <iframe 
                            id="remoteGUIIframe" 
                            src="${this.guiUrl}" 
                            frameborder="0"
                            allowfullscreen
                            sandbox="allow-same-origin allow-scripts allow-forms allow-popups">
                        </iframe>
                    </div>
                </div>
                
                <div class="remote-gui-footer">
                    <div class="gui-stats">
                        <span class="stat-item">
                            <i class="fas fa-clock"></i>
                            <span id="connectionTime">00:00:00</span>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-wifi"></i>
                            <span id="connectionQuality">Good</span>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-desktop"></i>
                            <span id="screenResolution">1920x1080</span>
                        </span>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Start Connection to RVM GUI
     */
    startConnection() {
        this.updateConnectionStatus('Connecting...', 'warning');
        this.showLoading();

        // Test connection first
        this.testConnection()
            .then(() => {
                this.loadGUI();
            })
            .catch((error) => {
                console.error('Connection failed:', error);
                this.showError();
            });
    }

    /**
     * Test connection to RVM GUI
     */
    async testConnection() {
        try {
            const response = await fetch(`${this.guiUrl}/api/status`, {
                method: 'GET',
                mode: 'cors',
                timeout: 5000
            });
            
            if (response.ok) {
                this.isConnected = true;
                this.updateConnectionStatus('Connected', 'success');
                return true;
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            this.isConnected = false;
            this.updateConnectionStatus('Failed', 'danger');
            throw error;
        }
    }

    /**
     * Load GUI in iframe
     */
    loadGUI() {
        const iframe = document.getElementById('remoteGUIIframe');
        const container = document.getElementById('guiIframeContainer');
        const loading = document.getElementById('guiLoading');
        const error = document.getElementById('guiError');

        if (iframe && container && loading && error) {
            // Hide loading and error
            loading.style.display = 'none';
            error.style.display = 'none';
            
            // Show iframe
            container.style.display = 'block';
            
            // Setup iframe event listeners
            iframe.onload = () => {
                console.log('GUI iframe loaded successfully');
                this.startConnectionTimer();
            };
            
            iframe.onerror = () => {
                console.error('GUI iframe failed to load');
                this.showError();
            };
        }
    }

    /**
     * Show loading state
     */
    showLoading() {
        const loading = document.getElementById('guiLoading');
        const error = document.getElementById('guiError');
        const container = document.getElementById('guiIframeContainer');
        
        if (loading) loading.style.display = 'block';
        if (error) error.style.display = 'none';
        if (container) container.style.display = 'none';
    }

    /**
     * Show error state
     */
    showError() {
        const loading = document.getElementById('guiLoading');
        const error = document.getElementById('guiError');
        const container = document.getElementById('guiIframeContainer');
        
        if (loading) loading.style.display = 'none';
        if (error) error.style.display = 'block';
        if (container) container.style.display = 'none';
        
        this.updateConnectionStatus('Failed', 'danger');
    }

    /**
     * Update connection status
     */
    updateConnectionStatus(text, type) {
        const statusElement = document.getElementById('guiConnectionStatus');
        if (statusElement) {
            statusElement.textContent = text;
            statusElement.className = `badge bg-${type}`;
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.getElementById('refreshGUI');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshGUI();
            });
        }

        // Fullscreen button
        const fullscreenBtn = document.getElementById('fullscreenGUI');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                this.toggleFullscreen();
            });
        }

        // Disconnect button
        const disconnectBtn = document.getElementById('disconnectGUI');
        if (disconnectBtn) {
            disconnectBtn.addEventListener('click', () => {
                this.disconnect();
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                this.refreshGUI();
            }
            if (e.key === 'F11') {
                e.preventDefault();
                this.toggleFullscreen();
            }
            if (e.key === 'Escape') {
                this.exitFullscreen();
            }
        });
    }

    /**
     * Refresh GUI
     */
    refreshGUI() {
        const iframe = document.getElementById('remoteGUIIframe');
        if (iframe) {
            iframe.src = iframe.src;
        }
    }

    /**
     * Toggle fullscreen
     */
    toggleFullscreen() {
        const container = document.getElementById('remoteGUIContainer');
        if (container) {
            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(err => {
                    console.error('Error attempting to enable fullscreen:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }
    }

    /**
     * Exit fullscreen
     */
    exitFullscreen() {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        }
    }

    /**
     * Start connection timer
     */
    startConnectionTimer() {
        const startTime = Date.now();
        this.refreshInterval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const hours = Math.floor(elapsed / 3600000);
            const minutes = Math.floor((elapsed % 3600000) / 60000);
            const seconds = Math.floor((elapsed % 60000) / 1000);
            
            const timeElement = document.getElementById('connectionTime');
            if (timeElement) {
                timeElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }, 1000);
    }

    /**
     * Retry connection
     */
    retryConnection() {
        this.startConnection();
    }

    /**
     * Disconnect
     */
    disconnect() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
        
        this.isConnected = false;
        this.updateConnectionStatus('Disconnected', 'secondary');
        
        // Close modal and cleanup
        const modal = bootstrap.Modal.getInstance(document.getElementById('remoteGUIModal'));
        if (modal) {
            modal.hide();
        }
        
        // Cleanup iframe
        const iframe = document.getElementById('remoteGUIIframe');
        if (iframe) {
            iframe.src = '';
        }
        
        // Hide GUI container
        const container = document.getElementById('guiIframeContainer');
        if (container) {
            container.style.display = 'none';
        }
        
        // Show loading state
        this.showLoading();
    }
}

// Global instance
let remoteGUIClient = null;

/**
 * Initialize Remote GUI Client
 */
function initRemoteGUIClient(rvmId, rvmIp) {
    remoteGUIClient = new RemoteGUIClient(rvmId, rvmIp);
    remoteGUIClient.init();
}

/**
 * Start Remote GUI Access
 */
function startRemoteGUIAccess(rvmId, rvmIp) {
    // Create modal for remote GUI
    const modalHtml = `
        <div class="modal fade" id="remoteGUIModal" tabindex="-1" aria-labelledby="remoteGUIModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="remoteGUIModalLabel">
                            <i class="fas fa-desktop"></i> Remote GUI Access - ${rvmId}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="remoteGUIContainer" style="height: 70vh;">
                            <!-- Remote GUI content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('remoteGUIModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('remoteGUIModal'));
    modal.show();
    
    // Initialize remote GUI client
    initRemoteGUIClient(rvmId, rvmIp);
    
    // Clean up on modal close
    document.getElementById('remoteGUIModal').addEventListener('hidden.bs.modal', () => {
        if (remoteGUIClient) {
            remoteGUIClient.disconnect();
        }
        document.getElementById('remoteGUIModal').remove();
    });
}
