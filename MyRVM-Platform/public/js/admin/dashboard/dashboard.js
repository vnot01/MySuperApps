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
        
        // Use server data instead of mock data
        const serverMonitoringData = {
            rvms: serverData.rvms,
            statistics: serverData.statistics
        };
        
        console.log('Server data loaded:', serverMonitoringData);
        
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
