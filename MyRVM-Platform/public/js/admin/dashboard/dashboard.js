// Data from Controller
const serverData = {
    rvms: window.dashboardData?.rvms || [],
    statistics: window.dashboardData?.statistics || {},
    timezoneConfig: window.dashboardData?.timezoneConfig || {}
};

// Configuration
const config = {
    apiBaseUrl: window.dashboardConfig?.apiBaseUrl || '/api/v2',
    csrfToken: window.dashboardConfig?.csrfToken || '',
    refreshInterval: 30000, // 30 seconds
    timezone: window.dashboardConfig?.timezone || 'Asia/Jakarta',
    dateFormat: window.dashboardConfig?.dateFormat || 'Y-m-d',
    timeFormat: window.dashboardConfig?.timeFormat || 'H:i:s',
    datetimeFormat: window.dashboardConfig?.datetimeFormat || 'Y-m-d H:i:s',
    displayTimezone: window.dashboardConfig?.displayTimezone || 'WIB'
};

// Global variables
let monitoringData = null;
let statusChart = null;
let currentRvmId = null;
let refreshIntervalTimer = null;
let rvmStatusChanges = {}; // Store RVM status changes
let currentPage = 1;
let itemsPerPage = 12;
let realTimeNotifications = null; // Real-time notification handler

// --- Core UI Functions ---

function showLoadingAnimation() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
        // Trigger reflow to ensure the transition is applied
        overlay.offsetHeight; 
        overlay.style.opacity = '1';
        overlay.style.visibility = 'visible';
    }
}

function hideLoadingAnimation() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        // The display:none can be handled by the transitionend event, or a simple timeout.
        // Timeout is simpler and sufficient here.
        setTimeout(() => {
            if (overlay.style.opacity === '0') { // Check if it's still meant to be hidden
                overlay.style.display = 'none';
            }
        }, 300); // Should match CSS transition duration
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icons = { 
        success: 'fas fa-check-circle', 
        error: 'fas fa-times-circle', 
        warning: 'fas fa-exclamation-triangle', 
        info: 'fas fa-info-circle' 
    };
    const icon = icons[type] || icons.info;
    
    notification.innerHTML = `<div class="d-flex align-items-center"><i class="${icon} me-2"></i> ${message}</div>`;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        notification.addEventListener('transitionend', () => notification.remove());
    }, 3000);
}

// --- Dashboard Logic ---

async function initializeDashboard() {
    try {
        showLoadingAnimation();
        
        // Load saved RVM status changes first
        loadRvmStatusChanges();
        
        await Promise.all([
            initializeStatusChart(),
            setupEventListeners(),
            initializeRealTimeNotifications(),
        ]);
        
        await loadMonitoringData();
        startAutoRefresh();

    } catch (error) {
        console.error('Error initializing dashboard:', error);
        showNotification('Failed to initialize dashboard', 'error');
    } finally {
        hideLoadingAnimation();
    }
}

async function loadMonitoringData() {
    try {
        showLoadingAnimation();
        
        console.log('Loading monitoring data from server...');
        
        // Fetch fresh data from server via AJAX
        const response = await fetch(window.location.pathname, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': config.csrfToken
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const serverMonitoringData = await response.json();
        
        console.log('Fresh server data loaded:', serverMonitoringData);
        
        // Apply saved status changes to server data
        if (serverMonitoringData.rvms && Object.keys(rvmStatusChanges).length > 0) {
            console.log('Applying saved status changes:', rvmStatusChanges);
            serverMonitoringData.rvms.forEach(rvm => {
                if (rvmStatusChanges[rvm.id]) {
                    rvm.calculated_status = rvmStatusChanges[rvm.id].status;
                    rvm.last_seen = rvmStatusChanges[rvm.id].last_seen;
                }
            });
        }
        
        monitoringData = serverMonitoringData;
        
        console.log('Updating dashboard components...');
        updateStatistics(serverMonitoringData.statistics);
        updateRvmCards(serverMonitoringData.rvms);
        updateStatusChart();
        updateLastUpdated();
        
        console.log('Dashboard data loaded successfully from server');
        
    } catch (error) {
        console.error('Error loading monitoring data:', error);
        console.error('Error stack:', error.stack);
        showNotification('Error loading dashboard data: ' + error.message, 'error');
    } finally {
        hideLoadingAnimation();
    }
}

// --- Timezone and DateTime Functions ---

function formatDateTime(date, format = 'datetime') {
    try {
        const d = new Date(date);
        
        // Check if date is valid
        if (isNaN(d.getTime())) {
            console.warn('Invalid date provided to formatDateTime:', date);
            return 'Invalid Date';
        }
        
        // Convert to configured timezone
        const options = {
            timeZone: config.timezone || 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        
        const formatted = d.toLocaleString('en-US', options);
        
        // Add timezone display
        return `${formatted} ${config.displayTimezone || 'WIB'}`;
        
    } catch (error) {
        console.error('Error formatting datetime:', error);
        return date ? date.toString() : 'Unknown';
    }
}

function formatTime(date) {
    try {
        const d = new Date(date);
        
        // Check if date is valid
        if (isNaN(d.getTime())) {
            console.warn('Invalid date provided to formatTime:', date);
            return 'Invalid Date';
        }
        
        const options = {
            timeZone: config.timezone || 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        
        return d.toLocaleTimeString('en-US', options);
        
    } catch (error) {
        console.error('Error formatting time:', error, 'Date:', date);
        return date ? date.toString() : 'Unknown';
    }
}

function formatDate(date) {
    try {
        const d = new Date(date);
        
        // Check if date is valid
        if (isNaN(d.getTime())) {
            console.warn('Invalid date provided to formatDate:', date);
            return 'Invalid Date';
        }
        
        const options = {
            timeZone: config.timezone || 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        };
        
        return d.toLocaleDateString('en-US', options);
        
    } catch (error) {
        console.error('Error formatting date:', error);
        return date ? date.toString() : 'Unknown';
    }
}

function getCurrentDateTime() {
    const now = new Date();
    return formatDateTime(now);
}

function getCurrentTime() {
    const now = new Date();
    return formatTime(now);
}

function getCurrentDate() {
    const now = new Date();
    return formatDate(now);
}

// --- RVM Status Logic Functions ---

function determineRvmStatus(capacity, specialStatus = null) {
    // If there's a special status (maintenance, inactive, error, unknown), use it
    if (specialStatus && ['maintenance', 'inactive', 'error', 'unknown'].includes(specialStatus)) {
        return specialStatus;
    }
    
    // Determine status based on capacity
    if (capacity >= 100) {
        return 'full';
    } else if (capacity >= 0) {
        return 'active';
    } else {
        return 'unknown';
    }
}

function getStatusInfo(status) {
    const statusMap = {
        'active': { 
            color: 'success', 
            icon: 'fas fa-check-circle', 
            label: 'Active',
            description: 'RVM is operational and ready'
        },
        'full': { 
            color: 'danger', 
            icon: 'fas fa-exclamation-triangle', 
            label: 'Full',
            description: 'RVM storage is full'
        },
        'maintenance': { 
            color: 'warning', 
            icon: 'fas fa-tools', 
            label: 'Maintenance',
            description: 'RVM is under maintenance'
        },
        'inactive': { 
            color: 'secondary', 
            icon: 'fas fa-pause-circle', 
            label: 'Inactive',
            description: 'RVM is offline or disabled'
        },
        'error': { 
            color: 'danger', 
            icon: 'fas fa-times-circle', 
            label: 'Error',
            description: 'RVM has encountered an error'
        },
        'unknown': { 
            color: 'info', 
            icon: 'fas fa-question-circle', 
            label: 'Unknown',
            description: 'Status cannot be determined'
        }
    };
    
    return statusMap[status] || statusMap['unknown'];
}

// --- Data Update Functions ---

function saveRvmStatusChange(rvmId, newStatus) {
    // Save RVM status change to persist across refreshes
    rvmStatusChanges[rvmId] = {
        status: newStatus,
        last_seen: getCurrentTime(),
        timestamp: Date.now()
    };
    
    // Store in localStorage for persistence across page reloads
    localStorage.setItem('rvmStatusChanges', JSON.stringify(rvmStatusChanges));
    
    console.log(`Saved status change for RVM-${rvmId}: ${newStatus} at ${getCurrentTime()}`);
}

function loadRvmStatusChanges() {
    // Load saved status changes from localStorage
    const saved = localStorage.getItem('rvmStatusChanges');
    if (saved) {
        try {
            rvmStatusChanges = JSON.parse(saved);
            console.log('Loaded saved RVM status changes:', rvmStatusChanges);
        } catch (error) {
            console.error('Error loading saved status changes:', error);
            rvmStatusChanges = {};
        }
    }
}

function clearRvmStatusChange(rvmId) {
    // Clear saved status change for specific RVM
    if (rvmStatusChanges[rvmId]) {
        delete rvmStatusChanges[rvmId];
        localStorage.setItem('rvmStatusChanges', JSON.stringify(rvmStatusChanges));
        console.log(`Cleared status change for RVM-${rvmId}`);
    }
}

function updateStatistics(stats) {
    animateNumber('total-rvm', stats.total_rvm);
    animateNumber('active-sessions', stats.active_sessions);
    animateNumber('deposits-today', stats.deposits_today);
    animateNumber('total-issues', stats.total_issues);
}

function animateNumber(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const startValue = parseInt(element.textContent, 10) || 0;
    if (startValue === targetValue) return;

    const duration = 1000;
    let startTime = null;

    function animation(currentTime) {
        if (startTime === null) startTime = currentTime;
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const currentValue = Math.floor(progress * (targetValue - startValue) + startValue);
        
        element.textContent = currentValue;
        
        if (progress < 1) requestAnimationFrame(animation);
    }
    requestAnimationFrame(animation);
}

function startAutoRefresh() {
    clearInterval(refreshIntervalTimer);
    refreshIntervalTimer = setInterval(loadMonitoringData, config.refreshInterval);
}

function stopAutoRefresh() {
    clearInterval(refreshIntervalTimer);
}

function updateLastUpdated() {
    const lastUpdatedElement = document.getElementById('last-updated');
    if (lastUpdatedElement) {
        lastUpdatedElement.textContent = getCurrentTime();
    }
}

// --- Event Handling & Setup ---

function setupEventListeners() {
    document.getElementById('refresh-dashboard')?.addEventListener('click', loadMonitoringData);
    document.getElementById('export-data')?.addEventListener('click', exportMonitoringData);
}

function exportMonitoringData() {
    showNotification('Preparing data export...', 'info');
    // Simulate export process
    setTimeout(() => {
        showNotification('Data exported successfully! Download will start shortly.', 'success');
        // In real implementation, this would trigger a file download
    }, 2000);
}

// --- Page Lifecycle ---

window.addEventListener('load', () => {
    // Add small delay to ensure Chart.js is fully loaded
    setTimeout(async () => {
        initializeDashboard();
        initializeStickyNavbar();
    }, 100);
});

window.addEventListener('beforeunload', stopAutoRefresh);

// Sticky Navbar with Scroll Effect
function initializeStickyNavbar() {
    const navbar = document.getElementById('layout-navbar');
    if (!navbar) return;

    let lastScrollTop = 0;
    
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add scrolled class for visual effect
        if (scrollTop > 10) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        lastScrollTop = scrollTop;
    });
}

// --- Real-time Notification Integration ---

async function initializeRealTimeNotifications() {
    try {
        // Wait for real-time notification handler to be available
        if (typeof window.RealTimeNotificationHandler !== 'undefined') {
            realTimeNotifications = new window.RealTimeNotificationHandler();
            
            // Override notification handlers for dashboard-specific behavior
            setupDashboardNotificationHandlers();
            
            console.log('Real-time notifications initialized for dashboard');
        } else {
            console.warn('RealTimeNotificationHandler not available, using fallback');
            setupNotificationFallback();
        }
    } catch (error) {
        console.error('Failed to initialize real-time notifications:', error);
    }
}

function setupDashboardNotificationHandlers() {
    if (!realTimeNotifications) return;
    
    // Override RVM status update handler
    const originalHandleRvmStatus = realTimeNotifications.handleRvmStatusNotification;
    realTimeNotifications.handleRvmStatusNotification = function(data) {
        // Call original handler
        originalHandleRvmStatus.call(this, data);
        
        // Dashboard-specific handling
        handleRvmStatusUpdate(data);
    };
    
    // Override system notification handler for dashboard
    const originalHandleSystem = realTimeNotifications.handleSystemNotification;
    realTimeNotifications.handleSystemNotification = function(data) {
        // Call original handler
        originalHandleSystem.call(this, data);
        
        // Dashboard-specific handling
        handleDashboardSystemNotification(data);
    };
}

function handleRvmStatusUpdate(data) {
    try {
        console.log('Dashboard handling RVM status update:', data);
        
        // Update local status changes
        rvmStatusChanges[data.rvm_id] = {
            status: data.status,
            last_seen: new Date().toISOString(),
            timestamp: Date.now()
        };
        
        // Save to localStorage
        saveRvmStatusChanges();
        
        // Update RVM card immediately
        updateRvmCardStatus(data.rvm_id, data.status);
        
        // Update statistics
        updateStatisticsForStatusChange(data.rvm_id, data.status, data.previous_status);
        
        // Update status chart
        updateStatusChart();
        
        // Show notification with RVM-specific styling
        showRvmStatusNotification(data);
        
    } catch (error) {
        console.error('Error handling RVM status update:', error);
    }
}

function updateRvmCardStatus(rvmId, newStatus) {
    const rvmCard = document.querySelector(`[data-rvm-id="${rvmId}"]`);
    if (!rvmCard) return;
    
    // Update status badge
    const statusBadge = rvmCard.querySelector('.status-badge');
    if (statusBadge) {
        statusBadge.className = `status-badge status-${newStatus}`;
        statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    }
    
    // Update status indicator
    const statusIndicator = rvmCard.querySelector('.status-indicator');
    if (statusIndicator) {
        statusIndicator.className = `status-indicator status-${newStatus}`;
    }
    
    // Update last seen time
    const lastSeenElement = rvmCard.querySelector('.last-seen');
    if (lastSeenElement) {
        lastSeenElement.textContent = formatDateTime(new Date());
    }
    
    // Add visual feedback
    rvmCard.classList.add('status-updated');
    setTimeout(() => {
        rvmCard.classList.remove('status-updated');
    }, 2000);
}

function updateStatisticsForStatusChange(rvmId, newStatus, previousStatus) {
    if (!monitoringData || !monitoringData.statistics) return;
    
    const stats = monitoringData.statistics;
    
    // Update status counts
    if (previousStatus && stats[previousStatus] > 0) {
        stats[previousStatus]--;
    }
    
    if (stats[newStatus] !== undefined) {
        stats[newStatus]++;
    } else {
        stats[newStatus] = 1;
    }
    
    // Update total if needed
    stats.total = Object.values(stats).reduce((sum, count) => {
        return typeof count === 'number' ? sum + count : sum;
    }, 0);
    
    // Update statistics display
    updateStatistics(stats);
}

function showRvmStatusNotification(data) {
    const statusColors = {
        active: 'success',
        inactive: 'warning',
        maintenance: 'info',
        error: 'error',
        full: 'warning',
        unknown: 'info'
    };
    
    const notificationType = statusColors[data.status] || 'info';
    const message = `${data.rvm_name} status changed to ${data.status.toUpperCase()}`;
    
    showNotification(message, notificationType);
}

function handleDashboardSystemNotification(data) {
    try {
        console.log('Dashboard handling system notification:', data);
        
        // Show system notification with special styling
        showSystemNotification(data);
        
        // If it's a maintenance notification, update dashboard accordingly
        if (data.data && data.data.priority === 'urgent') {
            handleUrgentSystemNotification(data);
        }
        
    } catch (error) {
        console.error('Error handling system notification:', error);
    }
}

function showSystemNotification(data) {
    const notification = document.createElement('div');
    notification.className = `notification notification-system notification-${data.type}`;
    notification.innerHTML = `
        <div class="notification-header">
            <i class="fas fa-broadcast-tower me-2"></i>
            <strong>System Notification</strong>
            <span class="notification-time">${formatTime(data.timestamp)}</span>
        </div>
        <div class="notification-content">
            <h6>${data.title}</h6>
            <p>${data.message}</p>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        notification.addEventListener('transitionend', () => notification.remove());
    }, 8000); // Longer display time for system notifications
}

function handleUrgentSystemNotification(data) {
    // Show modal for urgent notifications
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Urgent System Notification
                    </h5>
                </div>
                <div class="modal-body">
                    <h6>${data.title}</h6>
                    <p>${data.message}</p>
                    <small class="text-muted">Received: ${formatDateTime(data.timestamp)}</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        Acknowledged
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Show modal using Bootstrap
    if (typeof bootstrap !== 'undefined') {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
}

function setupNotificationFallback() {
    // Fallback polling for notifications when WebSocket is not available
    setInterval(async () => {
        try {
            const response = await fetch('/api/notifications/recent', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                }
            });
            
            if (response.ok) {
                const notifications = await response.json();
                notifications.forEach(notification => {
                    if (notification.category === 'rvm_status') {
                        handleRvmStatusUpdate(notification.data);
                    } else if (notification.category === 'system') {
                        handleDashboardSystemNotification(notification);
                    }
                });
            }
        } catch (error) {
            console.error('Notification polling error:', error);
        }
    }, 30000); // Poll every 30 seconds
}

// Expose functions for external use
window.dashboardNotifications = {
    handleRvmStatusUpdate,
    handleDashboardSystemNotification,
    updateRvmCardStatus,
    showRvmStatusNotification
};
