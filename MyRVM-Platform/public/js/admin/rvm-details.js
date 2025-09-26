// RVM Details Auto-refresh functionality
let refreshInterval;

function refreshRvmData() {
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    }
    
    fetch(window.location.pathname, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateRvmStatus(data.rvm);
            updateLastUpdated();
            showNotification('RVM data refreshed successfully', 'success');
        }
    })
    .catch(error => {
        console.error('Error refreshing RVM data:', error);
        showNotification('Failed to refresh RVM data', 'error');
    })
    .finally(() => {
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Refresh';
        }
    });
}

function updateRvmStatus(rvm) {
    // Update RVM status icon
    const statusIcon = document.querySelector('.status-icon-' + rvm.status_data.class);
    if (statusIcon) {
        statusIcon.title = 'RVM Status: ' + rvm.status_data.label;
    }
    
    // Update connection status icon
    const connectionIcon = document.querySelector('.connection-status-' + rvm.connection_status);
    if (connectionIcon) {
        connectionIcon.title = 'Connection: ' + rvm.connection_status.charAt(0).toUpperCase() + rvm.connection_status.slice(1);
        
        // Update icon based on connection status
        const iconElement = connectionIcon.querySelector('i');
        if (iconElement) {
            iconElement.className = rvm.connection_status === 'connected' ? 'fas fa-wifi' : 
                                   (rvm.connection_status === 'local' ? 'fas fa-home' : 'fas fa-wifi-slash');
        }
    }
    
    // Update status pulse animation
    const statusPulseIcon = document.querySelector('.status-pulse-icon.status-icon-' + rvm.status_data.class);
    if (statusPulseIcon) {
        statusPulseIcon.className = 'status-pulse-icon status-icon-' + rvm.status_data.class;
    }
    
    const connectionPulseIcon = document.querySelector('.status-pulse-icon.connection-status-' + rvm.connection_status);
    if (connectionPulseIcon) {
        connectionPulseIcon.className = 'status-pulse-icon connection-status-' + rvm.connection_status;
    }
}

function updateLastUpdated() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    console.log('RVM data refreshed at:', timeString);
}

function startAutoRefresh() {
    // Refresh every 30 seconds
    refreshInterval = setInterval(refreshRvmData, 30000);
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

function showNotification(message, type) {
    // Simple notification function
    console.log(`[${type.toUpperCase()}] ${message}`);
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
});

// Stop auto-refresh when page unloads
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});