/**
 * Navbar Notifications Auto Refresh
 * Updates the notification dropdown in the navbar
 */

class NavbarNotifications {
    constructor() {
        this.refreshInterval = 60000; // 1 minute for navbar
        this.refreshUrl = '/admin/notifications/refresh';
        this.isActive = false;
        this.intervalId = null;
        
        this.init();
    }

    init() {
        // Start auto refresh
        this.start();
        
        // Pause/resume based on page visibility
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stop();
            } else {
                this.start();
            }
        });

        // Refresh when dropdown is opened
        const notificationDropdown = document.querySelector('.dropdown-notifications');
        if (notificationDropdown) {
            notificationDropdown.addEventListener('show.bs.dropdown', () => {
                this.refresh();
            });
        }
    }

    start() {
        if (this.isActive) return;
        
        this.isActive = true;
        this.intervalId = setInterval(() => this.refresh(), this.refreshInterval);
    }

    stop() {
        if (!this.isActive) return;
        
        this.isActive = false;
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    async refresh() {
        try {
            const response = await fetch(this.refreshUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            
            if (data.success) {
                this.updateNavbarNotifications(data);
            }

        } catch (error) {
            console.error('Navbar notifications refresh error:', error);
        }
    }

    updateNavbarNotifications(data) {
        // Update badge count
        this.updateBadgeCount(data.unread_count);
        
        // Update dropdown content
        this.updateDropdownContent(data.notifications.slice(0, 5)); // Show only 5 latest
    }

    updateBadgeCount(unreadCount) {
        const badge = document.querySelector('.badge-notifications');
        if (badge) {
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'inline' : 'none';
        }
    }

    updateDropdownContent(notifications) {
        const dropdownList = document.querySelector('.dropdown-notifications-list .list-group');
        if (!dropdownList) return;

        if (notifications.length === 0) {
            dropdownList.innerHTML = `
                <li class="list-group-item text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-bell-slash fs-4 mb-2"></i>
                        <p class="mb-0">No new notifications</p>
                    </div>
                </li>
            `;
            return;
        }

        dropdownList.innerHTML = notifications.map(notification => {
            const iconClass = this.getNotificationIcon(notification.type);
            const bgClass = notification.type === 'error' ? 'danger' : notification.type;
            
            return `
                <li class="list-group-item list-group-item-action dropdown-notifications-item ${!notification.is_read ? 'bg-light' : ''}" style="position: relative;">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                                <span class="avatar-initial rounded-circle bg-label-${bgClass}">
                                    <i class="${iconClass}"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 ${!notification.is_read ? 'fw-bold' : ''}">${notification.title}</h6>
                            <p class="mb-0 text-muted small">${this.truncateText(notification.message, 60)}</p>
                            <small class="text-muted">${notification.created_at}</small>
                        </div>
                        ${!notification.is_read ? '<span class="ms-2 notification-dot" style="display: inline-block !important; width: 8px !important; height: 8px !important; background-color: #696cff !important; border-radius: 50% !important; position: absolute !important; right: 8px !important; top: 8px !important; z-index: 999 !important;"></span>' : ''}
                    </div>
                </li>
            `;
        }).join('');

        // Update "View all notifications" link
        const viewAllLink = document.querySelector('.dropdown-menu-footer a');
        if (viewAllLink) {
            viewAllLink.href = '/admin/notifications';
        }
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || 'fas fa-bell';
    }

    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
}

// Initialize navbar notifications when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if user is authenticated and navbar exists
    if (document.querySelector('.badge-notifications')) {
        window.navbarNotifications = new NavbarNotifications();
    }
});