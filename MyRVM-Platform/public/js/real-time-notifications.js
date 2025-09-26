/**
 * Real-time Notification Handler
 * Integrates with existing notification system and handles broadcasting events
 */

class RealTimeNotificationHandler {
    constructor() {
        this.echo = null;
        this.channels = [];
        this.notificationQueue = [];
        this.isProcessing = false;
        this.config = {
            maxNotifications: 5,
            autoHideDelay: 5000,
            retryAttempts: 3,
            retryDelay: 2000,
        };
        
        this.init();
    }

    /**
     * Initialize the notification handler
     */
    init() {
        this.setupEcho();
        this.setupChannels();
        this.setupEventHandlers();
        
        console.log('Real-time notification handler initialized');
    }

    /**
     * Setup Laravel Echo connection
     */
    setupEcho() {
        if (typeof window.Echo !== 'undefined') {
            this.echo = window.Echo;
            console.log('Laravel Echo connected for notifications');
        } else {
            console.warn('Laravel Echo not available, using fallback');
            this.setupFallback();
        }
    }

    /**
     * Setup notification channels
     */
    setupChannels() {
        if (!this.echo) return;

        try {
            // System notifications channel
            this.channels.push(
                this.echo.channel('system-notifications')
                    .listen('.system.notification.broadcast', (data) => {
                        this.handleSystemNotification(data);
                    })
            );

            // User action notifications channel
            this.channels.push(
                this.echo.channel('user-activity')
                    .listen('.user.action.notification', (data) => {
                        this.handleUserActionNotification(data);
                    })
            );

            // RVM status updates channel
            this.channels.push(
                this.echo.channel('rvm-status')
                    .listen('.rvm.status.updated', (data) => {
                        this.handleRvmStatusNotification(data);
                    })
            );

            // Admin dashboard channel
            this.channels.push(
                this.echo.channel('admin-dashboard')
                    .listen('.system.notification.broadcast', (data) => {
                        this.handleAdminNotification(data);
                    })
                    .listen('.user.action.notification', (data) => {
                        this.handleAdminNotification(data);
                    })
                    .listen('.rvm.status.updated', (data) => {
                        this.handleAdminNotification(data);
                    })
            );

            // Private user channel (if user is authenticated)
            if (window.userId) {
                this.channels.push(
                    this.echo.private(`user.${window.userId}`)
                        .listen('.user.action.notification', (data) => {
                            this.handlePrivateNotification(data);
                        })
                );
            }

            console.log('Notification channels setup complete');
        } catch (error) {
            console.error('Failed to setup notification channels:', error);
        }
    }

    /**
     * Setup event handlers
     */
    setupEventHandlers() {
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.processNotificationQueue();
            }
        });

        // Handle connection events
        if (this.echo) {
            this.echo.connector.socket.on('connect', () => {
                console.log('WebSocket connected for notifications');
            });

            this.echo.connector.socket.on('disconnect', () => {
                console.log('WebSocket disconnected');
                this.setupReconnection();
            });
        }
    }

    /**
     * Handle system notifications
     */
    handleSystemNotification(data) {
        console.log('System notification received:', data);
        
        const notification = {
            id: data.notification_id,
            title: data.title,
            message: data.message,
            type: data.type,
            category: 'system',
            timestamp: data.timestamp,
            data: data.data || {},
            priority: data.data?.priority || 'medium'
        };

        this.displayNotification(notification);
        this.playNotificationSound(notification.type);
    }

    /**
     * Handle user action notifications
     */
    handleUserActionNotification(data) {
        console.log('User action notification received:', data);
        
        // Only show user action notifications to admins
        if (!this.isAdmin()) {
            return;
        }

        const notification = {
            id: data.notification_id,
            title: data.title,
            message: data.message,
            type: data.type,
            category: 'user_action',
            timestamp: data.timestamp,
            data: {
                user: data.user,
                action: data.action,
                resource: data.resource,
                details: data.details
            }
        };

        this.displayNotification(notification);
    }

    /**
     * Handle RVM status notifications
     */
    handleRvmStatusNotification(data) {
        console.log('RVM status notification received:', data);
        
        const notification = {
            id: `rvm_status_${data.rvm_id}_${Date.now()}`,
            title: `RVM Status Update`,
            message: `${data.rvm_name} status changed to ${data.status}`,
            type: this.getRvmStatusType(data.status),
            category: 'rvm_status',
            timestamp: new Date().toISOString(),
            data: {
                rvm_id: data.rvm_id,
                rvm_name: data.rvm_name,
                status: data.status,
                previous_status: data.previous_status
            }
        };

        this.displayNotification(notification);
        this.updateRvmStatusInUI(data);
    }

    /**
     * Handle admin notifications
     */
    handleAdminNotification(data) {
        if (!this.isAdmin()) {
            return;
        }

        // Add admin badge to notification
        const notification = {
            ...data,
            isAdmin: true,
            category: data.category || 'admin'
        };

        this.displayNotification(notification);
    }

    /**
     * Handle private notifications
     */
    handlePrivateNotification(data) {
        console.log('Private notification received:', data);
        
        const notification = {
            id: data.notification_id,
            title: data.title,
            message: data.message,
            type: data.type,
            category: 'private',
            timestamp: data.timestamp,
            data: data.data || {}
        };

        this.displayNotification(notification);
    }

    /**
     * Display notification using existing notification system
     */
    displayNotification(notification) {
        if (document.hidden) {
            this.notificationQueue.push(notification);
            return;
        }

        try {
            // Use existing showNotification function if available
            if (typeof showNotification === 'function') {
                showNotification(
                    notification.message,
                    notification.type,
                    notification.title
                );
            } else if (typeof window.NotificationManager !== 'undefined') {
                // Use NotificationManager if available
                window.NotificationManager.add(
                    notification.message,
                    notification.type,
                    notification.title
                );
            } else {
                // Fallback to browser notification
                this.showBrowserNotification(notification);
            }

            // Update notification counter if exists
            this.updateNotificationCounter();
            
        } catch (error) {
            console.error('Failed to display notification:', error);
        }
    }

    /**
     * Show browser notification as fallback
     */
    showBrowserNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/images/notification-icon.png',
                tag: notification.id
            });
        }
    }

    /**
     * Process queued notifications
     */
    processNotificationQueue() {
        if (this.isProcessing || this.notificationQueue.length === 0) {
            return;
        }

        this.isProcessing = true;
        
        while (this.notificationQueue.length > 0) {
            const notification = this.notificationQueue.shift();
            this.displayNotification(notification);
            
            // Small delay between notifications
            if (this.notificationQueue.length > 0) {
                setTimeout(() => {}, 500);
            }
        }
        
        this.isProcessing = false;
    }

    /**
     * Update RVM status in UI
     */
    updateRvmStatusInUI(data) {
        // Update RVM cards if they exist
        const rvmCard = document.querySelector(`[data-rvm-id="${data.rvm_id}"]`);
        if (rvmCard) {
            const statusElement = rvmCard.querySelector('.rvm-status');
            if (statusElement) {
                statusElement.textContent = data.status;
                statusElement.className = `rvm-status status-${data.status}`;
            }
        }

        // Update RVM details page if open
        if (window.location.pathname.includes(`/rvms/${data.rvm_id}`)) {
            if (typeof updateRvmStatus === 'function') {
                updateRvmStatus(data.status);
            }
        }

        // Trigger refresh of RVM data if function exists
        if (typeof refreshRvmData === 'function') {
            refreshRvmData();
        }
    }

    /**
     * Update notification counter
     */
    updateNotificationCounter() {
        const counter = document.querySelector('.notification-counter');
        if (counter) {
            const currentCount = parseInt(counter.textContent) || 0;
            counter.textContent = currentCount + 1;
            counter.style.display = 'inline';
        }
    }

    /**
     * Play notification sound
     */
    playNotificationSound(type) {
        try {
            const audio = new Audio(`/sounds/notification-${type}.mp3`);
            audio.volume = 0.3;
            audio.play().catch(() => {
                // Ignore audio play errors
            });
        } catch (error) {
            // Ignore audio errors
        }
    }

    /**
     * Get RVM status notification type
     */
    getRvmStatusType(status) {
        switch (status) {
            case 'active':
                return 'success';
            case 'inactive':
                return 'warning';
            case 'maintenance':
                return 'info';
            case 'error':
                return 'error';
            case 'full':
                return 'warning';
            default:
                return 'info';
        }
    }

    /**
     * Check if current user is admin
     */
    isAdmin() {
        return window.userRole === 'admin' || window.isAdmin === true;
    }

    /**
     * Setup fallback for when Echo is not available
     */
    setupFallback() {
        // Implement polling fallback
        setInterval(() => {
            this.pollForNotifications();
        }, 30000); // Poll every 30 seconds
    }

    /**
     * Poll for notifications (fallback)
     */
    async pollForNotifications() {
        try {
            const response = await fetch('/api/notifications/recent');
            const notifications = await response.json();
            
            notifications.forEach(notification => {
                this.displayNotification(notification);
            });
        } catch (error) {
            console.error('Failed to poll notifications:', error);
        }
    }

    /**
     * Setup reconnection logic
     */
    setupReconnection() {
        let attempts = 0;
        const maxAttempts = this.config.retryAttempts;
        
        const reconnect = () => {
            if (attempts < maxAttempts) {
                attempts++;
                console.log(`Attempting to reconnect... (${attempts}/${maxAttempts})`);
                
                setTimeout(() => {
                    this.setupChannels();
                }, this.config.retryDelay * attempts);
            }
        };
        
        reconnect();
    }

    /**
     * Cleanup channels and connections
     */
    cleanup() {
        this.channels.forEach(channel => {
            if (channel && typeof channel.stopListening === 'function') {
                channel.stopListening();
            }
        });
        this.channels = [];
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Wait for Echo to be available
    const initNotifications = () => {
        if (typeof window.Echo !== 'undefined' || document.readyState === 'complete') {
            window.realTimeNotifications = new RealTimeNotificationHandler();
        } else {
            setTimeout(initNotifications, 1000);
        }
    };
    
    initNotifications();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.realTimeNotifications) {
        window.realTimeNotifications.cleanup();
    }
});