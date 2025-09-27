@extends('components.admin-layout')

@section('title', 'Notifications - MyRVM Platform')
@section('description', 'Manage your notifications and alerts')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-bell me-2"></i>Notifications
    </li>
@endsection

@section('content')
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div>
                <h1 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-bell me-2"></i>Notifications
                </h1>
                <p class="text-muted mb-0">Stay updated with system alerts and important messages</p>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-pills nav-fill bg-light rounded p-1">
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab === 'all' ? 'active' : '' }}" 
                       href="{{ route('admin.notifications', ['tab' => 'all']) }}"
                       data-tab="all">
                        <i class="fas fa-list me-2"></i>All
                        <span class="badge bg-secondary ms-2">{{ $allCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab === 'unread' ? 'active' : '' }}" 
                       href="{{ route('admin.notifications', ['tab' => 'unread']) }}"
                       data-tab="unread">
                        <i class="fas fa-envelope me-2"></i>Unread
                        <span class="badge bg-primary ms-2">{{ $unreadCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab === 'read' ? 'active' : '' }}" 
                       href="{{ route('admin.notifications', ['tab' => 'read']) }}"
                       data-tab="read">
                        <i class="fas fa-envelope-open me-2"></i>Read
                        <span class="badge bg-success ms-2">{{ $readCount }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="mb-0">
                                @if($currentTab === 'unread')
                                    Unread Notifications
                                @elseif($currentTab === 'read')
                                    Read Notifications
                                @else
                                    All Notifications
                                @endif
                            </h5>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <!-- Auto Refresh Pulse Indicator -->
                            <div class="auto-refresh-pulse d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Auto Refresh Active">
                                <div class="pulse-dot bg-success rounded-circle pulse-green"></div>
                            </div>
                            <span class="badge bg-primary fs-6 px-3 py-2">{{ $unreadCount }} Unread</span>
                            @if($unreadCount > 0)
                                <button class="btn btn-sm btn-outline-primary px-3" id="markAllAsRead">
                                    <i class="fas fa-check-double me-2"></i>Mark All Read
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 notifications-container">
                    @forelse($notifications as $notification)
                        <div class="notification-item d-flex align-items-start p-4 border-bottom {{ !$notification->isRead() ? 'bg-light' : '' }}" data-notification-id="{{ $notification->notification_id }}">
                            <div class="notification-icon me-3">
                                @if($notification->type == 'success')
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-success">
                                            <i class="fas fa-check text-white"></i>
                                        </span>
                                    </div>
                                @elseif($notification->type == 'warning')
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-warning">
                                            <i class="fas fa-exclamation text-white"></i>
                                        </span>
                                    </div>
                                @elseif($notification->type == 'info')
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-info">
                                            <i class="fas fa-info text-white"></i>
                                        </span>
                                    </div>
                                @elseif($notification->type == 'error')
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-danger">
                                            <i class="fas fa-times text-white"></i>
                                        </span>
                                    </div>
                                @else
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-secondary">
                                            <i class="fas fa-bell text-white"></i>
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="notification-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 {{ !$notification->isRead() ? 'fw-bold' : '' }}">
                                            {{ $notification->title }}
                                        </h6>
                                        <p class="text-muted mb-1">{{ $notification->message }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                        @if($notification->category)
                                            <span class="badge bg-secondary ms-2">{{ ucfirst(str_replace('_', ' ', $notification->category)) }}</span>
                                        @endif
                                    </div>
                                    <div class="notification-actions">
                                        @if(!$notification->isRead())
                                            <span class="ms-2 notification-dot" style="display: inline-block !important; width: 8px !important; height: 8px !important; background-color: #696cff !important; border-radius: 50% !important; position: absolute !important; right: 8px !important; top: 8px !important; z-index: 999 !important;"></span>
                                        @endif
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-text-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if(!$notification->isRead())
                                                    <li><a class="dropdown-item mark-as-read" href="#" data-notification-id="{{ $notification->notification_id }}"><i class="fas fa-check me-2"></i>Mark as Read</a></li>
                                                @else
                                                    <li><span class="dropdown-item text-muted"><i class="fas fa-check-circle me-2"></i>Already Read</span></li>
                                                @endif
                                                @if($notification->data)
                                                    <li><a class="dropdown-item" href="#" onclick="showNotificationDetails('{{ $notification->notification_id }}')"><i class="fas fa-info-circle me-2"></i>View Details</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-5">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                    <i class="fas fa-bell-slash fs-2"></i>
                                </span>
                            </div>
                            <h5>No Notifications</h5>
                            <p class="text-muted">You're all caught up! No new notifications at the moment.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Pagination Info -->
                    <div class="text-muted">
                        <small>
                            Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} 
                            of {{ $notifications->total() }} results
                        </small>
                    </div>
                    
                    <!-- Pagination Links -->
                    <nav aria-label="Notifications pagination">
                        <ul class="pagination pagination-sm mb-0">
                            {{-- Previous Page Link --}}
                            @if ($notifications->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifications->appends(request()->query())->previousPageUrl() }}">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($notifications->appends(request()->query())->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                                @if ($page == $notifications->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($notifications->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifications->appends(request()->query())->nextPageUrl() }}">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    @endif

    <!-- Notification Settings -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Notification Preferences</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label" for="emailNotifications">
                                    <strong>Email Notifications</strong>
                                    <br><small class="text-muted">Receive notifications via email</small>
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="pushNotifications" checked>
                                <label class="form-check-label" for="pushNotifications">
                                    <strong>Push Notifications</strong>
                                    <br><small class="text-muted">Receive browser push notifications</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="rvmAlerts" checked>
                                <label class="form-check-label" for="rvmAlerts">
                                    <strong>RVM Status Alerts</strong>
                                    <br><small class="text-muted">Get notified about RVM status changes</small>
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="systemUpdates">
                                <label class="form-check-label" for="systemUpdates">
                                    <strong>System Updates</strong>
                                    <br><small class="text-muted">Receive system maintenance notifications</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Preferences
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page-js')
<script src="{{ asset('js/admin/notifications-auto-refresh.js') }}"></script>
<script>
// Initialize auto-refresh for notifications
document.addEventListener('DOMContentLoaded', function() {
    const autoRefresh = new NotificationAutoRefresh({
        refreshInterval: 30000, // 30 seconds
        refreshUrl: '{{ route("admin.notifications") }}'
    });
    
    autoRefresh.start();
    
    // Handle tab switching
    const tabLinks = document.querySelectorAll('.nav-pills .nav-link');
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const tab = this.getAttribute('data-tab');
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('tab', tab);
            currentUrl.searchParams.delete('page'); // Reset pagination when switching tabs
            
            // Update URL without page reload
            window.history.pushState({}, '', currentUrl.toString());
            
            // Load new tab content
            loadTabContent(tab);
        });
    });
    
    // Mark single notification as read
    document.querySelectorAll('.mark-as-read').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.getAttribute('data-notification-id');
            markNotificationAsRead(notificationId);
        });
    });

    // Mark all notifications as read
    const markAllButton = document.getElementById('markAllAsRead');
    if (markAllButton) {
        markAllButton.addEventListener('click', function(e) {
            e.preventDefault();
            markAllNotificationsAsRead();
        });
    }
});

// Load tab content via AJAX
function loadTabContent(tab) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('tab', tab);
    currentUrl.searchParams.delete('page'); // Reset pagination
    
    fetch(currentUrl.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Update notifications container
        const newNotifications = doc.querySelector('.notifications-container');
        const currentContainer = document.querySelector('.notifications-container');
        
        if (newNotifications && currentContainer) {
            currentContainer.innerHTML = newNotifications.innerHTML;
        }
        
        // Update tab navigation active state
        document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-tab') === tab) {
                link.classList.add('active');
            }
        });
        
        // Update tab badges
        const newTabBadges = doc.querySelectorAll('.nav-pills .badge');
        const currentTabBadges = document.querySelectorAll('.nav-pills .badge');
        
        newTabBadges.forEach((newBadge, index) => {
            if (currentTabBadges[index]) {
                currentTabBadges[index].textContent = newBadge.textContent;
            }
        });
        
        // Update section title
        const newTitle = doc.querySelector('.card-header h5');
        const currentTitle = document.querySelector('.card-header h5');
        
        if (newTitle && currentTitle) {
            currentTitle.innerHTML = newTitle.innerHTML;
        }
    })
    .catch(error => {
        console.error('Error loading tab content:', error);
    });
}

function markNotificationAsRead(notificationId) {
    fetch(`/admin/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('bg-light');
                const badge = notificationElement.querySelector('.badge.bg-primary');
                if (badge) badge.remove();
                
                const title = notificationElement.querySelector('h6');
                if (title) title.classList.remove('fw-bold');
                
                // Update dropdown
                const markAsReadLink = notificationElement.querySelector('.mark-as-read');
                if (markAsReadLink) {
                    markAsReadLink.parentElement.innerHTML = '<span class="dropdown-item text-muted"><i class="fas fa-check-circle me-2"></i>Already Read</span>';
                }
            }
            
            // Update unread count
            updateUnreadCount();
            
            // Show success message
            showNotification('Notification marked as read', 'success');
        } else {
            showNotification('Failed to mark notification as read', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function markAllNotificationsAsRead() {
    fetch('/admin/notifications/read-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to show updated state
            location.reload();
        } else {
            showNotification('Failed to mark all notifications as read', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function updateUnreadCount() {
    const unreadElements = document.querySelectorAll('.notification-item.bg-light');
    const unreadCount = unreadElements.length;
    const badge = document.querySelector('.badge.bg-primary');
    if (badge) {
        badge.textContent = `${unreadCount} Unread`;
    }
}

function showNotificationDetails(notificationId) {
    // This can be expanded to show a modal with notification details
    console.log('Show details for notification:', notificationId);
}

function showNotification(message, type = 'info') {
    // Simple notification system
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 3000);
}
</script>

<style>
/* Simple Pulse Animation for Auto Refresh Indicator */
.pulse-dot {
    width: 8px;
    height: 8px;
    cursor: help;
}

/* Pulse Animations */
@keyframes pulse-green {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
}
.pulse-green {
    animation: pulse-green 2s infinite;
}

@keyframes pulse-yellow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
}
.pulse-yellow {
    animation: pulse-yellow 2s infinite;
}

@keyframes pulse-red {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
}
.pulse-red {
    animation: pulse-red 2s infinite;
}

@keyframes pulse-blue {
    0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
}
.pulse-blue {
    animation: pulse-blue 2s infinite;
}

/* Auto Refresh Pulse Container */
.auto-refresh-pulse {
    cursor: help;
}

/* Improved badge styling */
.badge.fs-6 {
    font-size: 0.875rem !important;
    font-weight: 500;
}

/* Better spacing for buttons */
.btn.px-3 {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
}
</style>
@endsection