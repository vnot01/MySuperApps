// Remote Control Form Functionality

class RemoteControlManager {
    constructor() {
        this.isConnected = false;
        this.selectedRvm = null;
        this.connectionLog = [];
        
        this.initializeElements();
        this.setupEventListeners();
        this.loadRvmDevices();
    }

    initializeElements() {
        this.rvmSelect = document.getElementById('rvmSelect');
        this.connectButton = document.getElementById('connectButton');
        this.disconnectButton = document.getElementById('disconnectButton');
        this.statusIndicator = document.getElementById('statusIndicator');
        this.connectionInfo = document.getElementById('connectionInfo');
        this.logContainer = document.getElementById('logContainer');
        this.controlButtons = document.querySelectorAll('.control-button');
    }

    setupEventListeners() {
        this.connectButton?.addEventListener('click', () => this.connectToRvm());
        this.disconnectButton?.addEventListener('click', () => this.disconnectFromRvm());
        this.rvmSelect?.addEventListener('change', (e) => this.onRvmChange(e.target.value));
        
        this.controlButtons.forEach(button => {
            button.addEventListener('click', (e) => this.handleControlAction(e.currentTarget));
        });
    }

    async loadRvmDevices() {
        try {
            showLoading('Loading RVM devices...');
            
            const response = await fetch('/api/v2/rvm');
            const rvms = await response.json();
            
            this.rvmSelect.innerHTML = '<option value="">Select RVM...</option>';
            
            rvms.forEach(rvm => {
                const option = document.createElement('option');
                option.value = rvm.id;
                option.textContent = `${rvm.name} - ${rvm.location}`;
                this.rvmSelect.appendChild(option);
            });
            
        } catch (error) {
            console.error('Error loading RVM devices:', error);
            showError('Failed to load RVM devices');
        } finally {
            hideLoading();
        }
    }

    onRvmChange(rvmId) {
        this.selectedRvm = rvmId;
        if (this.isConnected) {
            this.disconnectFromRvm();
        }
    }

    async connectToRvm() {
        if (!this.selectedRvm) {
            showWarning('Please select an RVM first');
            return;
        }

        try {
            showLoading('Connecting to RVM...');
            
            // Simulate connection process
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            this.isConnected = true;
            this.updateConnectionStatus();
            this.addLogEntry('Connected to RVM successfully', 'info');
            showSuccess('Connected to RVM successfully');
            
        } catch (error) {
            console.error('Error connecting to RVM:', error);
            this.addLogEntry('Failed to connect to RVM', 'error');
            showError('Failed to connect to RVM');
        } finally {
            hideLoading();
        }
    }

    disconnectFromRvm() {
        this.isConnected = false;
        this.updateConnectionStatus();
        this.addLogEntry('Disconnected from RVM', 'warning');
        showInfo('Disconnected from RVM');
    }

    updateConnectionStatus() {
        if (this.isConnected) {
            this.statusIndicator.className = 'status-indicator connected';
            this.statusIndicator.innerHTML = '<i class="fas fa-circle me-1"></i>Connected';
            this.connectButton.disabled = true;
            this.disconnectButton.disabled = false;
            this.rvmSelect.disabled = true;
            
            // Update connection info
            const selectedOption = this.rvmSelect.options[this.rvmSelect.selectedIndex];
            if (selectedOption) {
                this.connectionInfo.innerHTML = `
                    <div class="info-item">
                        <span class="info-label">RVM Name:</span>
                        <span class="info-value">${selectedOption.textContent}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Connection Time:</span>
                        <span class="info-value">${new Date().toLocaleTimeString()}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value">Active</span>
                    </div>
                `;
            }
        } else {
            this.statusIndicator.className = 'status-indicator disconnected';
            this.statusIndicator.innerHTML = '<i class="fas fa-circle me-1"></i>Disconnected';
            this.connectButton.disabled = false;
            this.disconnectButton.disabled = true;
            this.rvmSelect.disabled = false;
            this.connectionInfo.innerHTML = '<p class="text-muted text-center">Not connected</p>';
        }
    }

    handleControlAction(button) {
        if (!this.isConnected) {
            showWarning('Please connect to an RVM first');
            return;
        }

        const action = button.dataset.action;
        const actionName = button.querySelector('span').textContent;
        
        // Add visual feedback
        button.classList.add('active');
        setTimeout(() => button.classList.remove('active'), 200);
        
        this.addLogEntry(`Executing: ${actionName}`, 'info');
        
        // Simulate action execution
        setTimeout(() => {
            this.addLogEntry(`${actionName} completed successfully`, 'info');
            showSuccess(`${actionName} executed successfully`);
        }, 1000);
    }

    addLogEntry(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry ${type}`;
        logEntry.innerHTML = `
            <span class="log-timestamp">[${timestamp}]</span>
            <span>${message}</span>
        `;
        
        this.logContainer.appendChild(logEntry);
        this.logContainer.scrollTop = this.logContainer.scrollHeight;
        
        // Keep only last 50 entries
        const entries = this.logContainer.querySelectorAll('.log-entry');
        if (entries.length > 50) {
            entries[0].remove();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new RemoteControlManager();
});
