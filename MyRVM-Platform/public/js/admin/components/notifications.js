// Notification System

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.maxNotifications = 5;
    }

    show(message, type = 'info', duration = 3000) {
        const notification = this.createNotification(message, type);
        this.addToDOM(notification);
        this.notifications.push(notification);
        
        // Auto remove after duration
        setTimeout(() => {
            this.remove(notification);
        }, duration);
        
        // Limit number of notifications
        if (this.notifications.length > this.maxNotifications) {
            const oldest = this.notifications.shift();
            this.remove(oldest);
        }
        
        return notification;
    }

    createNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icons = { 
            success: 'fas fa-check-circle', 
            error: 'fas fa-times-circle', 
            warning: 'fas fa-exclamation-triangle', 
            info: 'fas fa-info-circle' 
        };
        const icon = icons[type] || icons.info;
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="${icon} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" onclick="notificationManager.remove(this.closest('.notification'))"></button>
            </div>
        `;
        
        return notification;
    }

    addToDOM(notification) {
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
    }

    remove(notification) {
        if (!notification || !notification.parentNode) return;
        
        notification.classList.remove('show');
        
        notification.addEventListener('transitionend', () => {
            if (notification.parentNode) {
                notification.remove();
            }
            
            // Remove from array
            const index = this.notifications.indexOf(notification);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        });
    }

    clear() {
        this.notifications.forEach(notification => {
            this.remove(notification);
        });
    }
}

// Global notification manager instance
const notificationManager = new NotificationManager();

// Convenience functions
function showNotification(message, type = 'info', duration = 3000) {
    return notificationManager.show(message, type, duration);
}

function showSuccess(message, duration = 3000) {
    return notificationManager.show(message, 'success', duration);
}

function showError(message, duration = 5000) {
    return notificationManager.show(message, 'error', duration);
}

function showWarning(message, duration = 4000) {
    return notificationManager.show(message, 'warning', duration);
}

function showInfo(message, duration = 3000) {
    return notificationManager.show(message, 'info', duration);
}
