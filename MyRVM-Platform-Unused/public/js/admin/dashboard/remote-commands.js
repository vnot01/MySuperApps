class RemoteCommandsManager {
    constructor(rvmId) {
        this.rvmId = rvmId;
        this.availableCommands = null;
        this.commandHistory = [];
        this.refreshInterval = null;
    }
    
    async init() {
        await this.loadAvailableCommands();
        await this.loadCommandHistory();
        this.startStatusRefresh();
    }
    
    async loadAvailableCommands() {
        try {
            const response = await fetch(`/admin/rvm/${this.rvmId}/available-commands`);
            const data = await response.json();
            
            if (data.success) {
                this.availableCommands = data.data.available_commands;
                this.renderCommandButtons();
            }
        } catch (error) {
            console.error('Failed to load available commands:', error);
        }
    }
    
    async loadCommandHistory() {
        try {
            const response = await fetch(`/admin/rvm/${this.rvmId}/remote-commands`);
            const data = await response.json();
            
            if (data.success) {
                this.commandHistory = data.data.commands;
                this.renderCommandHistory();
            }
        } catch (error) {
            console.error('Failed to load command history:', error);
        }
    }
    
    renderCommandButtons() {
        const container = document.getElementById('remote-commands-container');
        if (!container || !this.availableCommands) return;
        
        let html = '';
        
        Object.entries(this.availableCommands).forEach(([category, commands]) => {
            html += `
                <div class="command-category mb-4">
                    <h6 class="text-muted mb-3">${this.getCategoryDisplayName(category)}</h6>
                    <div class="row">
            `;
            
            commands.forEach(command => {
                html += `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card command-card">
                            <div class="card-body text-center">
                                <i class="${command.icon} fa-2x text-${command.color} mb-2"></i>
                                <h6 class="card-title">${command.display_name}</h6>
                                <p class="card-text small text-muted">${command.description}</p>
                                <button class="btn btn-${command.color} btn-sm" 
                                        onclick="executeRemoteCommand('${category}', '${command.name}', ${command.requires_confirmation})">
                                    <i class="fas fa-play"></i> Execute
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    renderCommandHistory() {
        const container = document.getElementById('command-history-container');
        if (!container) return;
        
        let html = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Command</th>
                            <th>Status</th>
                            <th>Executed By</th>
                            <th>Executed At</th>
                            <th>Completed At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        this.commandHistory.forEach(command => {
            html += `
                <tr>
                    <td>
                        <strong>${command.command_name}</strong><br>
                        <small class="text-muted">${command.command_type}</small>
                    </td>
                    <td>
                        <span class="badge badge-${this.getStatusColor(command.status)}">
                            ${command.status}
                        </span>
                    </td>
                    <td>${command.executed_by || 'System'}</td>
                    <td>${this.formatDateTime(command.executed_at)}</td>
                    <td>${this.formatDateTime(command.completed_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" 
                                onclick="viewCommandDetails(${command.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        container.innerHTML = html;
    }
    
    async executeCommand(commandType, commandName, requiresConfirmation = false) {
        if (requiresConfirmation) {
            const confirmed = confirm(`Are you sure you want to execute "${commandName}"?`);
            if (!confirmed) return;
        }
        
        try {
            const response = await fetch(`/admin/rvm/${this.rvmId}/execute-command`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    command_type: commandType,
                    command_name: commandName,
                    command_payload: {}
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Command sent successfully', 'success');
                await this.loadCommandHistory();
            } else {
                this.showNotification('Failed to send command: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Failed to execute command:', error);
            this.showNotification('Network error: ' + error.message, 'error');
        }
    }
    
    startStatusRefresh() {
        this.refreshInterval = setInterval(() => {
            this.loadCommandHistory();
        }, 5000); // Refresh every 5 seconds
    }
    
    stopStatusRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
    
    getCategoryDisplayName(category) {
        const names = {
            'HARDWARE_CONTROL': 'Hardware Control',
            'PROCESS_MANAGEMENT': 'Process Management',
            'SYSTEM_CONTROL': 'System Control',
            'DIAGNOSTICS': 'Diagnostics'
        };
        return names[category] || category;
    }
    
    getStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'executing': 'info',
            'completed': 'success',
            'failed': 'danger'
        };
        return colors[status] || 'secondary';
    }
    
    formatDateTime(dateTime) {
        if (!dateTime) return '-';
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
function executeRemoteCommand(commandType, commandName, requiresConfirmation) {
    if (window.remoteCommandsManager) {
        window.remoteCommandsManager.executeCommand(commandType, commandName, requiresConfirmation);
    }
}

function viewCommandDetails(commandId) {
    // Implement command details modal
    console.log('View command details:', commandId);
    
    // For now, just show an alert
    alert(`Command details for ID: ${commandId}\n\nThis would open a detailed modal showing command execution details, results, and logs.`);
}

// Initialize remote commands manager
let remoteCommandsManager = null;

function initRemoteCommands(rvmId) {
    if (remoteCommandsManager) {
        remoteCommandsManager.stopStatusRefresh();
    }
    remoteCommandsManager = new RemoteCommandsManager(rvmId);
    window.remoteCommandsManager = remoteCommandsManager;
    remoteCommandsManager.init();
}

function stopRemoteCommands() {
    if (remoteCommandsManager) {
        remoteCommandsManager.stopStatusRefresh();
        remoteCommandsManager = null;
    }
}
