/**
 * Auto Refresh Notifications System
 * Automatically refreshes notifications every 30 seconds
 */

class NotificationAutoRefresh {
    constructor(options = {}) {
        this.refreshInterval = options.refreshInterval || 30000; // 30 seconds default
        this.refreshUrl = options.refreshUrl || '/admin/notifications/refresh';
        this.isActive = false;
        this.intervalId = null;
        this.lastRefreshTime = null;
        this.retryCount = 0;
        this.maxRetries = 3;
        this.currentTab = this.getCurrentTab(); // Track current tab
        
        // Bind methods
        this.refresh = this.refresh.bind(this);
        this.handleVisibilityChange = this.handleVisibilityChange.bind(this);
        
        // Initialize
        this.init();
    }

    init() {
        // Start auto refresh when page loads
        this.start();
        
        // Pause/resume based on page visibility
        document.addEventListener('visibilitychange', this.handleVisibilityChange);
        
        // Show auto refresh status
        this.showAutoRefreshStatus();
        
        // Initialize tooltips
        this.initializeTooltips();
    }

    start() {
        if (this.isActive) return;
        
        this.isActive = true;
        this.intervalId = setInterval(this.refresh, this.refreshInterval);
        this.updateStatusIndicator('active');
        console.log('🔄 Auto refresh notifications started');
    }

    stop() {
        if (!this.isActive) return;
        
        this.isActive = false;
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        this.updateStatusIndicator('inactive');
        console.log('⏸️ Auto refresh notifications stopped');
    }

    getCurrentTab() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('tab') || 'all';
    }

    async refresh() {
        if (!this.isActive) return;
        
        this.updateStatusIndicator('refreshing');
        
        try {
            // Build refresh URL with current tab parameter
            const currentTab = this.getCurrentTab();
            const refreshUrl = new URL(window.location.href);
            refreshUrl.searchParams.set('tab', currentTab);
            
            const response = await fetch(refreshUrl.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const html = await response.text();
            
            // Update the notifications container and tab badges
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update notifications list
            const newNotifications = doc.querySelector('.notifications-container');
            const currentContainer = document.querySelector('.notifications-container');
            
            if (newNotifications && currentContainer) {
                currentContainer.innerHTML = newNotifications.innerHTML;
                
                // Re-attach event listeners for new elements
                this.attachEventListeners();
            }
            
            // Update tab badges with new counts
            const newTabBadges = doc.querySelectorAll('.nav-pills .badge');
            const currentTabBadges = document.querySelectorAll('.nav-pills .badge');
            
            newTabBadges.forEach((newBadge, index) => {
                if (currentTabBadges[index]) {
                    currentTabBadges[index].textContent = newBadge.textContent;
                }
            });
            
            // Update unread count in header
            const newUnreadBadge = doc.querySelector('.card-header .badge');
            const currentUnreadBadge = document.querySelector('.card-header .badge');
            
            if (newUnreadBadge && currentUnreadBadge) {
                currentUnreadBadge.textContent = newUnreadBadge.textContent;
            }
            
            this.lastRefreshTime = new Date();
            this.retryCount = 0;
            this.updateStatusIndicator('active');
            
            console.log('✅ Notifications refreshed successfully');
            
        } catch (error) {
            console.error('❌ Failed to refresh notifications:', error);
            this.retryCount++;
            
            if (this.retryCount >= this.maxRetries) {
                this.updateStatusIndicator('error');
                console.error('🚫 Max retries reached. Auto refresh paused.');
                this.stop();
            } else {
                this.updateStatusIndicator('active');
                console.log(`🔄 Retry ${this.retryCount}/${this.maxRetries} in next refresh cycle`);
            }
        }
    }

    updateNotifications(data) {
        // Update unread count
        this.updateUnreadCount(data.unread_count);
        
        // Update notifications list
        this.updateNotificationsList(data.notifications);
        
        // Show new notifications indicator if there are new ones
        this.checkForNewNotifications(data.notifications);
        
        console.log(`✅ Notifications refreshed: ${data.total_count} total, ${data.unread_count} unread`);
    }

    updateUnreadCount(unreadCount) {
        // Update badge in header
        const badges = document.querySelectorAll('.badge-notifications, .badge.bg-primary');
        badges.forEach(badge => {
            if (badge.classList.contains('badge-notifications')) {
                badge.textContent = unreadCount;
                badge.style.display = unreadCount > 0 ? 'inline' : 'none';
            } else if (badge.textContent.includes('Unread')) {
                badge.textContent = `${unreadCount} Unread`;
            }
        });

        // Update page title with unread count
        const originalTitle = document.title.replace(/^\(\d+\)\s*/, '');
        document.title = unreadCount > 0 ? `(${unreadCount}) ${originalTitle}` : originalTitle;
    }

    updateNotificationsList(notifications) {
        const notificationsContainer = document.querySelector('.card-body');
        if (!notificationsContainer) return;

        // Store current scroll position
        const scrollTop = notificationsContainer.scrollTop;

        // Generate new notifications HTML
        const notificationsHtml = this.generateNotificationsHtml(notifications);
        
        // Update the container
        notificationsContainer.innerHTML = notificationsHtml;

        // Restore scroll position
        notificationsContainer.scrollTop = scrollTop;

        // Re-attach event listeners
        this.attachNotificationEventListeners();
    }

    generateNotificationsHtml(notifications) {
        if (notifications.length === 0) {
            return `
                <div class="text-center p-5">
                    <div class="avatar avatar-xl mx-auto mb-3">
                        <span class="avatar-initial rounded-circle bg-label-secondary">
                            <i class="fas fa-bell-slash fs-2"></i>
                        </span>
                    </div>
                    <h5>No Notifications</h5>
                    <p class="text-muted">You're all caught up! No new notifications at the moment.</p>
                </div>
            `;
        }

        return notifications.map(notification => {
            const iconClass = this.getNotificationIcon(notification.type);
            const bgClass = notification.is_read ? '' : 'bg-light';
            const titleClass = notification.is_read ? '' : 'fw-bold';
            const categoryBadge = notification.category ? 
                `<span class="badge bg-secondary ms-2">${notification.category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>` : '';

            return `
                <div class="notification-item d-flex align-items-start p-4 border-bottom ${bgClass}" data-notification-id="${notification.notification_id}">
                    <div class="notification-icon me-3">
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-circle bg-${notification.type === 'error' ? 'danger' : notification.type}">
                                <i class="${iconClass} text-white"></i>
                            </span>
                        </div>
                    </div>
                    <div class="notification-content flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 ${titleClass}">
                                    ${notification.title}
                                </h6>
                                <p class="text-muted mb-1">${notification.message}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    ${notification.created_at}
                                </small>
                                ${categoryBadge}
                            </div>
                            <div class="notification-actions">
                                ${!notification.is_read ? '<span class="ms-2 notification-dot" style="display: inline-block !important; width: 8px !important; height: 8px !important; background-color: #696cff !important; border-radius: 50% !important; position: absolute !important; right: 8px !important; top: 8px !important; z-index: 999 !important;"></span>' : ''}
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-text-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        ${!notification.is_read ? 
                                            `<li><a class="dropdown-item mark-as-read" href="#" data-notification-id="${notification.notification_id}"><i class="fas fa-check me-2"></i>Mark as Read</a></li>` :
                                            '<li><span class="dropdown-item text-muted"><i class="fas fa-check-circle me-2"></i>Already Read</span></li>'
                                        }
                                        ${notification.data ? 
                                            `<li><a class="dropdown-item" href="#" onclick="showNotificationDetails('${notification.notification_id}')"><i class="fas fa-info-circle me-2"></i>View Details</a></li>` : ''
                                        }
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'fas fa-check',
            error: 'fas fa-times',
            warning: 'fas fa-exclamation',
            info: 'fas fa-info'
        };
        return icons[type] || 'fas fa-bell';
    }

    attachNotificationEventListeners() {
        // Re-attach mark as read listeners
        document.querySelectorAll('.mark-as-read').forEach(element => {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.getAttribute('data-notification-id');
                if (window.markNotificationAsRead) {
                    window.markNotificationAsRead(notificationId);
                }
            });
        });
    }

    checkForNewNotifications(notifications) {
        if (!this.lastRefreshTime) return;

        const newNotifications = notifications.filter(notification => {
            const notificationTime = new Date(notification.created_at_iso);
            return notificationTime > this.lastRefreshTime && !notification.is_read;
        });

        if (newNotifications.length > 0) {
            this.showNewNotificationsAlert(newNotifications.length);
        }
    }

    showNewNotificationsAlert(count) {
        // Show a subtle notification about new notifications
        if (window.showNotification) {
            window.showNotification(`${count} new notification${count > 1 ? 's' : ''} received`, 'info', 2000);
        }
    }

    handleRefreshError(error) {
        this.retryCount++;
        this.updateStatusIndicator('error');

        if (this.retryCount >= this.maxRetries) {
            this.stop();
            console.error(`❌ Auto refresh stopped after ${this.maxRetries} failed attempts`);
            
            if (window.showNotification) {
                window.showNotification('Auto refresh temporarily disabled due to connection issues', 'warning', 5000);
            }
        } else {
            console.warn(`⚠️ Auto refresh failed (attempt ${this.retryCount}/${this.maxRetries}), retrying...`);
        }
    }

    handleVisibilityChange() {
        if (document.hidden) {
            this.stop();
        } else {
            this.start();
            // Refresh immediately when page becomes visible
            setTimeout(this.refresh, 1000);
        }
    }

    initializeTooltips() {
        // Initialize Bootstrap tooltips for auto refresh indicator
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    showAutoRefreshStatus() {
        // The auto refresh pulse indicator is already in the HTML template
        // We just need to ensure it's visible and properly styled
        const indicator = document.querySelector('.auto-refresh-pulse');
        if (indicator) {
            indicator.style.display = 'flex';
        }
    }

    updateStatusIndicator(status) {
        const indicator = document.querySelector('.auto-refresh-pulse');
        const pulseDot = document.querySelector('.pulse-dot');
        
        if (!indicator || !pulseDot) return;

        // Update tooltip text based on status
        let tooltipText = 'Auto Refresh';
        let pulseClass = 'pulse-green';
        let bgClass = 'bg-success';
        
        switch (status) {
            case 'active':
                tooltipText = 'Auto Refresh Active';
                pulseClass = 'pulse-green';
                bgClass = 'bg-success';
                break;
            case 'inactive':
                tooltipText = 'Auto Refresh Paused';
                pulseClass = '';
                bgClass = 'bg-secondary';
                break;
            case 'refreshing':
                tooltipText = 'Auto Refresh Updating...';
                pulseClass = 'pulse-blue';
                bgClass = 'bg-primary';
                break;
            case 'error':
                tooltipText = 'Auto Refresh Error';
                pulseClass = 'pulse-red';
                bgClass = 'bg-danger';
                break;
        }
        
        // Update tooltip
        indicator.setAttribute('data-bs-original-title', tooltipText);
        
        // Update pulse animation and color
        pulseDot.className = `pulse-dot rounded-circle ${bgClass} ${pulseClass}`;
    }

    // Public methods for manual control
    toggle() {
        if (this.isActive) {
            this.stop();
        } else {
            this.start();
        }
    }

    setInterval(interval) {
        this.refreshInterval = interval;
        if (this.isActive) {
            this.stop();
            this.start();
        }
    }
}

// Initialize auto refresh when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on notifications page
    if (window.location.pathname.includes('/notifications')) {
        window.notificationAutoRefresh = new NotificationAutoRefresh({
            refreshInterval: 30000, // 30 seconds
            refreshUrl: '/admin/notifications/refresh'
        });
    }
});

// Add CSS for status indicator
const style = document.createElement('style');
style.textContent = `
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .auto-refresh-status {
        font-size: 0.75rem;
    }
`;
document.head.appendChild(style);